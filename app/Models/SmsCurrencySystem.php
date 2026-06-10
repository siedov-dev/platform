<?php

namespace App\Models;

use App\SMS\SmsServiceType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Настройки соответствия СМС системы валюте.
 * Используется для выбора системы для отправки смс в зависимости от валюты пользователя.
 *
 * @property int $id
 * @property string $currency
 * @property SmsServiceType $sms_system
 */
class SmsCurrencySystem extends Model
{

    protected $casts = [
        'sms_system' => SmsServiceType::class,
    ];

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function getCurrency(): string
    {
        return $this->currency;
    }

    public function setCurrency(string $currency): void
    {
        $this->currency = $currency;
    }

    public function getSmsSystem(): SmsServiceType
    {
        return $this->sms_system;
    }

    public function setSmsSystem(SmsServiceType $sms_system): void
    {
        $this->sms_system = $sms_system;
    }

    public static function getForCurrency(string $currency): ?SmsServiceType
    {
        return SmsCurrencySystem::where('currency', $currency)->first()?->getSmsSystem();
    }
}
