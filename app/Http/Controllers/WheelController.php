<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Illuminate\Routing\Controller as BaseController;

class WheelController extends BaseController
{
    public function start(Request $request)
    {
        $sectors = [
            ['id' => 1, 'multiplier' => 0.0],
            ['id' => 2, 'multiplier' => 1.2],
            ['id' => 3, 'multiplier' => 1.5],
            ['id' => 4, 'multiplier' => 0.0],
            ['id' => 5, 'multiplier' => 2.0],
            ['id' => 6, 'multiplier' => 0.0],
            ['id' => 7, 'multiplier' => 5.0],
            ['id' => 8, 'multiplier' => 10.0],
        ];

        $gameId = (string) Str::uuid();

        $gameState = [
            'sectors' => $sectors,
            'status' => 'active'
        ];

        Cache::put('wheel_' . $gameId, $gameState, 3600);

        return response()->json([
            'status' => 'success',
            'data' => [
                'game_id' => $gameId,
                'total_spins' => 8
            ]
        ]);
    }

    public function spin(Request $request)
    {
        $request->validate(['game_id' => 'required|string']);
        
        $gameId = $request->game_id;
        $cacheKey = 'wheel_' . $gameId;

        return Cache::lock($cacheKey . '_lock', 5)->get(function () use ($gameId, $cacheKey) {
            $gameState = Cache::get($cacheKey);

            if (!$gameState || empty($gameState['sectors'])) {
                return response()->json(['status' => 'error', 'message' => 'Все сектора выпали или сессия не найдена'], 400);
            }

            $sectors = $gameState['sectors'];
            $drawnKey = array_rand($sectors);
            $winningSector = $sectors[$drawnKey];

            unset($sectors[$drawnKey]);
            $gameState['sectors'] = array_values($sectors);
            Cache::put($cacheKey, $gameState, 3600);

            return response()->json([
                'status' => 'success',
                'sector' => $winningSector,
                'spins_left' => count($gameState['sectors'])
            ]);
        }) ?? response()->json(['status' => 'error', 'message' => 'Сервер занят'], 429);
    }
}