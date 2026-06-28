<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class PyramidController extends Controller
{
    // Важно: если здесь был конструктор с $this->middleware('auth'), УДАЛИ ЕГО.
    // Конструктор должен быть либо пустым, либо отсутствовать.

    private function getFiguresPool()
    {
        return [
            1  => ['name' => 'Фигурка 1',  'multiplier' => 1.0],
            2  => ['name' => 'Фигурка 2',  'multiplier' => 1.2],
            3  => ['name' => 'Фигурка 3',  'multiplier' => 1.5],
            4  => ['name' => 'Фигурка 4',  'multiplier' => 2.0],
            5  => ['name' => 'Фигурка 5',  'multiplier' => 2.5],
            6  => ['name' => 'Фигурка 6',  'multiplier' => 3.0],
            7  => ['name' => 'Фигурка 7',  'multiplier' => 3.5],
            8  => ['name' => 'Фигурка 8',  'multiplier' => 4.0],
            9  => ['name' => 'Фигурка 9',  'multiplier' => 4.5],
            10 => ['name' => 'Фигурка 10', 'multiplier' => 5.0],
            11 => ['name' => 'Фигурка 11', 'multiplier' => 6.0],
            12 => ['name' => 'Фигурка 12', 'multiplier' => 7.0],
            13 => ['name' => 'Фигурка 13', 'multiplier' => 8.0],
            14 => ['name' => 'Фигурка 14', 'multiplier' => 10.0],
            15 => ['name' => 'Фигурка 15', 'multiplier' => 12.0],
            16 => ['name' => 'Фигурка 16', 'multiplier' => 15.0],
            17 => ['name' => 'Фигурка 17', 'multiplier' => 20.0],
            18 => ['name' => 'Фигурка 18', 'multiplier' => 25.0],
            19 => ['name' => 'Фигурка 19', 'multiplier' => 50.0],
            20 => ['name' => 'Фигурка 20', 'multiplier' => 100.0],
        ];
    }

    public function start(Request $request)
    {
        $pool = $this->getFiguresPool();
        $drawnId = array_rand($pool);
        $currentFigure = $pool[$drawnId];
        unset($pool[$drawnId]);

        $gameId = (string) Str::uuid();
        
        $gameState = [
            'pool' => $pool,
            'current_figure' => $currentFigure,
            'rerolls_left' => 3,
            'status' => 'active'
        ];
        
        Cache::put('pyramid_' . $gameId, $gameState, 3600);

        return response()->json([
            'status' => 'success',
            'data' => [
                'game_id' => $gameId,
                'figure_data' => $currentFigure,
                'rerolls_left' => 3
            ]
        ]);
    }

    public function action(Request $request)
    {
        $request->validate([
            'game_id' => 'required|string',
            'action' => 'required|in:reroll,claim',
        ]);

        $gameId = $request->game_id;
        $lockKey = 'pyramid_lock_' . $gameId;

        // Блокировка (Race condition protection)
        return Cache::lock($lockKey, 5)->get(function () use ($gameId, $request) {
            $gameState = Cache::get('pyramid_' . $gameId);

            if (!$gameState) {
                return response()->json(['status' => 'error', 'message' => 'Игра не найдена'], 400);
            }

            if ($gameState['status'] !== 'active') {
                return response()->json(['status' => 'error', 'message' => 'Игра завершена'], 400);
            }

            if ($request->action === 'claim') {
                $gameState['status'] = 'completed';
                Cache::put('pyramid_' . $gameId, $gameState, 3600);

                return response()->json([
                    'status' => 'claimed',
                    'final_figure' => $gameState['current_figure'],
                    'message' => 'Игра завершена.'
                ]);
            }

            if ($gameState['rerolls_left'] <= 0) {
                return response()->json(['status' => 'error', 'message' => 'Попытки закончились'], 400);
            }

            $pool = $gameState['pool'];
            if (empty($pool)) {
                return response()->json(['status' => 'error', 'message' => 'Пул пуст'], 400);
            }

            $drawnId = array_rand($pool);
            $newFigure = $pool[$drawnId];
            unset($pool[$drawnId]);

            $gameState['current_figure'] = $newFigure;
            $gameState['pool'] = $pool;
            $gameState['rerolls_left']--;

            Cache::put('pyramid_' . $gameId, $gameState, 3600);

            return response()->json([
                'status' => 'rerolled',
                'figure_data' => $newFigure,
                'rerolls_left' => $gameState['rerolls_left']
            ]);
        }) ?? response()->json(['status' => 'error', 'message' => 'Сервер занят, попробуйте снова'], 429);
    }
}