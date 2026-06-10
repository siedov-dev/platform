<?php

namespace App\Models\Slc;

enum SlcUserItemStatus: int
{
    case New = 0;
    case Activated = 1;
    case Expired = 2;
    case Depleted = 3;

    public function getCrmName(): string
    {
        return match ($this) {
            self::New => 'Новый/активный',
            self::Activated => 'Активирован',
            self::Expired => 'Истёк',
            self::Depleted => 'Израсходован',
        };
    }
}
