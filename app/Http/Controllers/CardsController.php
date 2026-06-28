<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Illuminate\Routing\Controller as BaseController;

class CardsController extends BaseController
{
    /**
     * Старт игры: создаем сессию
     */
    public function start(Request $request)
    {
        $gameId = (string) Str::uuid();

        // Инициализируем состояние
        $gameState = [
            'status' => 'active',
            'cards_dealt' => false
        ];

        Cache::put('cards_' . $gameId, $gameState, 3600);

        return response()->json([
            'status' => 'success',
            'game_id' => $gameId,
            'message' => 'Игра начата'
        ]);
    }

    /**
     * Игра: 50/50 выбор (Win/Lose)
     */
    public function play(Request $request)
    {
        $request->validate(['game_id' => 'required|string']);

        $gameId = $request->game_id;
        $lockKey = 'cards_lock_' . $gameId;

        // Блокировка от двойных кликов
        return Cache::lock($lockKey, 5)->get(function () use ($gameId) {
            $gameState = Cache::get('cards_' . $gameId);

            if (!$gameState || $gameState['status'] !== 'active') {
                return response()->json(['status' => 'error', 'message' => 'Игра не найдена или завершена'], 400);
            }

            // Математика 50/50
            $isWin = (rand(0, 1) === 1);
            $gameState['status'] = 'finished';

            Cache::put('cards_' . $gameId, $gameState, 3600);

            return response()->json([
                'status' => 'success',
                'result' => $isWin ? 'win' : 'lose',
                'multiplier' => $isWin ? 1.95 : 0
            ]);
        }) ?? response()->json(['status' => 'error', 'message' => 'Сервер занят'], 429);
    }
}