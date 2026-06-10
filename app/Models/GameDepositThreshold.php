<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Предоставляет информацию о минимальных значениях баланса пользователя
 * для отображения окна депозита во время игры.
 * @property int $id
 * @property string $currency
 * @property float $threshold
 */
class GameDepositThreshold extends Model
{
    public $timestamps = false;
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

    public function getThreshold(): float
    {
        return $this->threshold;
    }

    public function setThreshold(float $threshold): void
    {
        $this->threshold = $threshold;
    }

}
