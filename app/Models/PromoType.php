<?php

namespace App\Models;

/**
 * Типы промокодов.
 */
enum PromoType: int
{
    case Bonus = 0;
    case SlcItem = 1;

    public function crmName(): string
    {
        return match ($this) {
            self::Bonus => 'Бонус',
            self::SlcItem => 'Slc Итем',
        };
    }
}
