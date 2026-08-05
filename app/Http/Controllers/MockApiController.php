<?php

namespace App\Http\Controllers;

class MockApiController extends Controller
{
    // Данные для баланса (база papdb)
    public function getUserBalance() {
        return response()->json([
            "status" => "success",
            "data" => [
                "user_id" => 1001,
                "balance" => 1500.50,
                "currency" => "KZT"
            ]
        ]);
    }

    // Данные для статистики (база kraken_statistics)
    public function getStats() {
        return response()->json([
            "status" => "success",
            "data" => [
                "games_count" => 1240,
                "win_rate" => 45.2
            ]
        ]);
    }

    // Данные для SMS (база sms)
    public function getSms() {
        return response()->json([
            "status" => "success",
            "data" => [
                ["id" => 101, "message" => "Ваш код: 7788", "time" => "2026-06-22 14:20"]
            ]
        ]);
    }
}