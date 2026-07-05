<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// ==========================================
// МИНИ-ИГРЫ (Полностью автономные)
// ==========================================
use App\Http\Controllers\PyramidController;
use App\Http\Controllers\CardsController;
use App\Http\Controllers\WheelController;
use App\Http\Controllers\SafeController;
use App\Http\Controllers\PillarController; 

Route::prefix('game')->group(function () {
    Route::post('/pyramid/start', [PyramidController::class, 'start']);
    Route::post('/pyramid/action', [PyramidController::class, 'action']);
    
    Route::post('/cards/start', [CardsController::class, 'start']);
    Route::post('/cards/play', [CardsController::class, 'play']);
    
    Route::post('/wheel/start', [WheelController::class, 'start']);
    Route::post('/wheel/spin', [WheelController::class, 'spin']);

    Route::post('/safe/start', [SafeController::class, 'start']);
    Route::post('/safe/open', [SafeController::class, 'open']);

    // ИГРА: СТОЛБ
    Route::post('/pillar/play', [PillarController::class, 'play']); // Боевой эндпоинт для фронта
    Route::get('/pillar/simulate', [PillarController::class, 'simulate']); // Эндпоинт для отчета о балансе (Stress Test)
});

// ==========================================
// ОСНОВНОЙ API
// ==========================================

Route::get('/health', function () { return response()->json(['status' => 'ok']); });
Route::get('/ping', function () { return response("ok"); });

Route::group(['middleware' => 'forceJson'], function() {
    // ... весь твой огромный список платежек ...
    Route::post('/piastrix_pay_url', 'PayController@piastrix_pay_url');
    // (Оставь здесь все свои Route::any из исходного файла)
    // ...
});

// ... (остальные твои роуты: paycraft, gm, bcknd)