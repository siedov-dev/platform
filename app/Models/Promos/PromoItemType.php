<?php

namespace App\Models\Promos;

enum PromoItemType: int
{
    case NoTime = 0;
    case SpecificDate = 1;
    case WelcomeBonus = 2;
    case WeekDay = 3;
    case WeekDays = 4;


    public function getCrmName():string
    {
        return match ($this) {
            self::NoTime => 'Бессрочный',
            self::SpecificDate => 'На дату',
            self::WelcomeBonus => 'Welcome bonus',
            self::WeekDay => 'День недели',
            self::WeekDays => 'Дни недели',
            default => 'Unknown',
        };
    }
}
