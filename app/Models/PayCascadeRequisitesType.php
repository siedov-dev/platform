<?php

namespace App\Models;

enum PayCascadeRequisitesType: string
{
    case None = '';
    case Email = 'email';
    case Phone = 'phone';
    case EmailPhone = 'email_phone';
    case Card = 'card';

    public function crmName(): string
    {
        return match ($this) {
            self::None => '',
            self::Email => 'email',
            self::Phone => 'Телефон',
            self::EmailPhone => 'email и телефон',
            self::Card => 'Банковская карта',
        };
    }
}
