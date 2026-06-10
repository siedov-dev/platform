<?php

namespace App\Models\Tournaments;

enum TntGameType: int
{
    case Multiplier = 1;
    case BetX = 2;
    case WinX = 3;
    case WinK = 4;

    public function getCrmName(): string
    {
        return match ($this) {
            self::Multiplier => 'Множитель выигрыша',
            self::BetX => 'По сумме ставок',
            self::WinX => 'По сумме выигрышей',
            self::WinK => 'Коэффициент выигрыша',
        };
    }

    public function getSettingsView(): string
    {
        return match ($this) {
            self::Multiplier => 'multiplier',
            self::BetX => 'betx',
            self::WinX => 'winx',
            self::WinK => 'wink',
        };
    }
}
