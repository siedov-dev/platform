<?php

namespace App\Models;

/**
 * Группы платежных систем.
 */
enum PaymentSystemGroup: int
{
    case NoGroup = 0;
    case VisaMaster = 1;
    case P2p = 2;
    case Bank = 3;
    case Crypto = 4;
    case MobileCommerce = 5;

    public function crmName(): string
    {
        return match ($this) {
            self::NoGroup => 'Нет',
            self::VisaMaster => 'Visa/Mater',
            self::P2p => 'P2P',
            self::Bank => 'Банк',
            self::Crypto => 'Крипта',
            self::MobileCommerce => 'Моб Коммерция',
        };
    }
}
