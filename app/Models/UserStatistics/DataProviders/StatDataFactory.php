<?php

namespace App\Models\UserStatistics\DataProviders;

use App\Models\UserStatistics\StatisticsType;

class StatDataFactory
{
    public static function getDataProvider(StatisticsType $type): StatDataProvider
    {
        return match ($type) {
            StatisticsType::WinBetsInCrashGames => new StatCrashBetWinsData(),
            StatisticsType::MadeBets => new StatTotalBetData(),
            StatisticsType::MaxBetMultiplier => new StatMaxBetMultiplierData(),
            default => throw new \Exception("No statistics data provider")
        };
    }
}
