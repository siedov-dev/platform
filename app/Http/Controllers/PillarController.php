<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class PillarController extends Controller
{
    public function play(Request $request)
    {
        // Ожидаем от фронтенда user_id и cluster_id
        $userId = $request->input('user_id', 'test_user_1');
        $clusterId = $request->input('cluster_id', 'global_cluster');

        $response = [
            'status' => 'success',
            'events' => [], // Сюда складываем все события за спин (База -> Карусель -> Колесо)
            'total_coins_won' => 0,
        ];

        // 1. БАЗОВАЯ ИГРА (СТОЛБ)
        $nBase = mt_rand(0, 65536);
        $baseOutcome = $this->resolveBaseOutcome($nBase);
        
        $response['events'][] = [
            'type' => 'base_slot',
            'n' => $nBase,
            'outcome' => $baseOutcome,
            'coins' => $this->getBaseCoins($baseOutcome)
        ];
        $response['total_coins_won'] += $this->getBaseCoins($baseOutcome);

        // 2. ЛОГИКА БОНУСА (ОБЩИЕ ФЛАГИ КЛАСТЕРА)
        if ($baseOutcome === 'BONUS') {
            // Блокируем кластер на 5 секунд, чтобы избежать гонки запросов (race condition)
            $lock = Cache::lock("cluster_lock_{$clusterId}", 5);
            
            if ($lock->get()) {
                $flags = Cache::get("cluster_flags_{$clusterId}", 0);
                $flags++;

                if ($flags >= 4) {
                    // Флагов 4! Сбрасываем общие флаги и запускаем карусель для ТЕКУЩЕГО игрока
                    Cache::put("cluster_flags_{$clusterId}", 0, 86400); // храним сутки
                    $response['events'][] = ['type' => 'flag_trigger', 'message' => '4th flag collected!'];
                    
                    // Запускаем Карусель
                    $this->handleCarousel($userId, $response);
                } else {
                    // Просто обновляем счетчик
                    Cache::put("cluster_flags_{$clusterId}", $flags, 86400);
                    $response['events'][] = ['type' => 'flag_collected', 'current_flags' => $flags];
                }
                
                $lock->release();
            }
        }

        // 3. ДОБАВЛЯЕМ ТЕКУЩИЕ СОСТОЯНИЯ В ОТВЕТ (ДЛЯ ФРОНТЕНДА)
        $response['state'] = [
            'cluster_flags' => Cache::get("cluster_flags_{$clusterId}", 0),
            'user_wagons' => Cache::get("user_wagons_{$userId}", 0)
        ];

        return response()->json($response);
    }

    /**
     * СИМУЛЯЦИЯ ДЛЯ ТЕСТИРОВАНИЯ МАТЕМАТИКИ (STRESS TEST)
     * Прогоняет игру N раз и выдает статистику для отчета.
     */
    public function simulate(Request $request)
    {
        // По умолчанию делаем 100 000 спинов, если не передано другое число
        $spins = $request->input('spins', 100000); 
        
        $stats = [
            'total_spins' => $spins,
            'base_outcomes' => [],
            'bonus_hits' => 0,
            'carousel_triggers' => 0,
            'wheel_triggers' => 0,
            'total_coins_won' => 0,
            'total_wagons_won' => 0,
        ];

        $wagons = 0;
        $flags = 0;

        for ($i = 0; $i < $spins; $i++) {
            // 1. Крутим столб
            $nBase = mt_rand(0, 65536);
            $outcome = $this->resolveBaseOutcome($nBase);

            if (!isset($stats['base_outcomes'][$outcome])) {
                $stats['base_outcomes'][$outcome] = 0;
            }
            $stats['base_outcomes'][$outcome]++;
            $stats['total_coins_won'] += $this->getBaseCoins($outcome);

            // 2. Логика бонусов
            if ($outcome === 'BONUS') {
                $stats['bonus_hits']++;
                $flags++;
                
                // 3. Сработала карусель
                if ($flags >= 4) {
                    $flags = 0; // сброс кластера
                    $stats['carousel_triggers']++;

                    $stage = min($wagons, 5);
                    $nCarousel = mt_rand(0, 65535);
                    $cResult = $this->resolveCarouselOutcome($stage, $nCarousel);

                    $stats['total_coins_won'] += $cResult['coins'];
                    $stats['total_wagons_won'] += $cResult['wagons'];
                    $wagons += $cResult['wagons'];

                    // 4. Сработало колесо
                    if ($wagons >= 6) {
                        $wagons -= 6;
                        $stats['wheel_triggers']++;
                        
                        $nWheel = mt_rand(0, 65535);
                        $stats['total_coins_won'] += $this->resolveWheelOutcome($nWheel);
                    }
                }
            }
        }

        // Сортируем исходы от частых к редким для красоты отчета
        arsort($stats['base_outcomes']);

        return response()->json($stats);
    }

    /**
     * Обработка Карусели и (если нужно) Колеса обозрения
     */
    private function handleCarousel($userId, &$response)
    {
        // Получаем текущие вагоны игрока
        $currentWagons = Cache::get("user_wagons_{$userId}", 0);
        
        // Защита: если вагонов больше 5, используем математику для 5-й стадии
        $stage = min($currentWagons, 5); 
        
        $nCarousel = mt_rand(0, 65535);
        $carouselResult = $this->resolveCarouselOutcome($stage, $nCarousel);

        // Обновляем вагоны и монеты
        $newWagonsCount = $currentWagons + $carouselResult['wagons'];
        $response['total_coins_won'] += $carouselResult['coins'];
        
        $response['events'][] = [
            'type' => 'carousel',
            'stage_played' => $stage,
            'n' => $nCarousel,
            'coins_won' => $carouselResult['coins'],
            'wagons_won' => $carouselResult['wagons'],
            'total_wagons_now' => $newWagonsCount
        ];

        // Проверяем, не пора ли крутить Колесо обозрения
        if ($newWagonsCount >= 6) {
            $newWagonsCount -= 6; // Списываем 6 вагонов
            
            $nWheel = mt_rand(0, 65535);
            $wheelCoins = $this->resolveWheelOutcome($nWheel);
            
            $response['total_coins_won'] += $wheelCoins;
            $response['events'][] = [
                'type' => 'ferris_wheel',
                'n' => $nWheel,
                'coins_won' => $wheelCoins,
                'wagons_left' => $newWagonsCount
            ];
        }

        // Сохраняем итоговое количество вагонов в кэш
        Cache::put("user_wagons_{$userId}", $newWagonsCount, 86400);
    }

    // ================= МАТЕМАТИКА =================

    private function resolveBaseOutcome($n)
    {
        if ($n >= 0 && $n <= 881) return 'BONUS';
        if ($n >= 6060 && $n <= 6064) return 'WIN_200';
        if ($n >= 11243 && $n <= 11276) return 'WIN_100';
        if ($n >= 16455 && $n <= 16523) return 'WIN_50';
        if ($n >= 21702 && $n <= 22189) return 'WIN_15';
        if ($n >= 27368 && $n <= 28143) return 'WIN_10';
        if ($n >= 33322 && $n <= 34061) return 'WIN_5';
        if ($n >= 39240 && $n <= 40739) return 'WIN_3';
        if ($n >= 45917 && $n <= 49177) return 'WIN_2';
        if ($n >= 54356 && $n <= 60355) return 'WIN_1';
        return 'LOSE';
    }

    private function getBaseCoins($outcome)
    {
        $map = [
            'WIN_200' => 200, 'WIN_100' => 100, 'WIN_50' => 50, 
            'WIN_15' => 15, 'WIN_10' => 10, 'WIN_5' => 5, 
            'WIN_3' => 3, 'WIN_2' => 2, 'WIN_1' => 1
        ];
        return $map[$outcome] ?? 0;
    }

    private function resolveCarouselOutcome($stage, $n)
    {
        // Индексы: [шанс <= N, монеты, вагоны]
        $math = [
            0 => [[920, 100, 0], [17116, 50, 1], [65535, 25, 2]],
            1 => [[2864, 100, 0], [29006, 50, 1], [65535, 25, 2]],
            2 => [[5282, 100, 0], [41176, 50, 1], [65535, 25, 2]],
            3 => [[11317, 100, 0], [44965, 50, 1], [65535, 25, 2]],
            4 => [[22154, 100, 0], [50974, 50, 1], [65535, 25, 2]],
            5 => [[31105, 100, 0], [36197, 50, 1], [65535, 25, 2]],
        ];

        foreach ($math[$stage] as $rule) {
            if ($n <= $rule[0]) {
                return ['coins' => $rule[1], 'wagons' => $rule[2]];
            }
        }
        return ['coins' => 25, 'wagons' => 2]; // Фолбэк на всякий случай
    }

    private function resolveWheelOutcome($n)
    {
        if ($n >= 0 && $n <= 30500) return 200;
        if ($n >= 30501 && $n <= 60944) return 100;
        if ($n >= 60945 && $n <= 65535) return 500;
        return 0; // Фолбэк
    }
}