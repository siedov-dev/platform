<?php

namespace App\Models\UserStatistics;

enum StatisticsType: string
{
    case WinBetsInCrashGames = "crash_win_bets";
    case MadeBets = "total_bets";
    case MaxBetMultiplier = "max_bet_multiplier";

    public function getDataType(): string
    {
        return match ($this) {
            StatisticsType::WinBetsInCrashGames => "int",
            StatisticsType::MadeBets => "int",
            StatisticsType::MaxBetMultiplier => "float",
            default => throw new \Exception("Unknown StatisticsType data type"),
        };
    }
}
