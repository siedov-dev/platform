<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Illuminate\Routing\Controller as BaseController;

class SafeController extends BaseController
{
    public function start(Request $request)
    {
        $gameId = (string) Str::uuid();

        // 29 сейфов из 640 ячеек
        $safeTypes = array_merge(
            array_fill(0, 4, 'jackpot'),
            array_fill(0, 22, 'minus'),
            array_fill(0, 3, 'normal')
        );

        $map = array_fill(0, 640, 'empty');
        $positions = array_rand(range(0, 639), 29);
        
        foreach ($positions as $index => $pos) {
            $map[$pos] = $safeTypes[$index];
        }

        $gameState = [
            'map' => $map,
            'opened_cells' => [],
            'pool_balance' => 2985,
            'status' => 'active'
        ];

        Cache::put('safe_' . $gameId, $gameState, 3600);

        return response()->json([
            'status' => 'success',
            'data' => [
                'game_id' => $gameId,
                'pool_balance' => 2985
            ]
        ]);
    }

    public function open(Request $request)
    {
        $request->validate([
            'game_id' => 'required|string', 
            'cell_index' => 'required|integer|between:0,639'
        ]);
        
        $cacheKey = 'safe_' . $request->game_id;

        return Cache::lock($cacheKey . '_lock', 5)->get(function () use ($request, $cacheKey) {
            $gameState = Cache::get($cacheKey);

            if (!$gameState) {
                return response()->json(['status' => 'error', 'message' => 'Игра не найдена'], 404);
            }
            
            if (in_array($request->cell_index, $gameState['opened_cells'])) {
                return response()->json(['status' => 'error', 'message' => 'Ячейка уже открыта']);
            }

            $type = $gameState['map'][$request->cell_index];
            $gameState['opened_cells'][] = $request->cell_index;

            switch ($type) {
                case 'jackpot': $gameState['pool_balance'] += 500; break;
                case 'minus':   $gameState['pool_balance'] -= 50;  break;
                case 'normal':  $gameState['pool_balance'] += 100; break;
                default:        $gameState['pool_balance'] -= 5;   
            }

            Cache::put($cacheKey, $gameState, 3600);

            return response()->json([
                'status' => 'success',
                'type' => $type,
                'pool_balance' => $gameState['pool_balance']
            ]);
        }) ?? response()->json(['status' => 'error', 'message' => 'Сервер занят'], 429);
    }
}