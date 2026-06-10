<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Модель хранит параметры для депозитов
 * @property int $id
 * @property string $currency трехбуквенный код валюты
 * @property float $wager_drop_amount сумма баланса, при которой сбрасывается старый вейджер
 * @property int $wager вейджер на основные средства, устанавливаемый при депозите
 */
class DepositParams extends Model
{

    /**
     * Возвращает параметры для валюты
     */
    public static function forCurrency(string $currency): ?DepositParams
    {
        return DepositParams::where('currency', $currency)->first();
    }

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

    public function getWagerDropAmount(): float
    {
        return $this->wager_drop_amount;
    }

    public function setWagerDropAmount(float $wager_drop_amount): void
    {
        $this->wager_drop_amount = $wager_drop_amount;
    }

    public function getWager(): int
    {
        return $this->wager;
    }

    public function setWager(int $wager): void
    {
        $this->wager = $wager;
    }
}
