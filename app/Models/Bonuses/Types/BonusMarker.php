<?php

namespace App\Models\Bonuses\Types;

enum BonusMarker: int
{
    case NoMarker = 0;
    case TelegramBonus = 1;
    case RegBonus = 2;
}
