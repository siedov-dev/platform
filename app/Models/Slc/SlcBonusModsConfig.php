<?php

namespace App\Models\Slc;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Параметры модификаторов бонусов
 *
 * @property int $id
 * @property int $bonus_type
 * @property int $wager_mod
 * @property int $deposit_mod
 * @property boolean $enabled
 */
class SlcBonusModsConfig extends Model
{
    use HasFactory;

    /**
     * Получить тип бонуса
     * @return int
     */
    public function getBonusType(): int
    {
        return $this->bonus_type;
    }

    /**
     * Установить тип бонуса
     * @param int $bonus_type
     */
    public function setBonusType(int $bonus_type): void
    {
        $this->bonus_type = $bonus_type;
    }

    /**
     * Получить модификатор wager
     * @return int
     */
    public function getWagerMod(): int
    {
        return $this->wager_mod;
    }

    /**
     * Установить модификатор wager
     * @param int $wager_mod
     */
    public function setWagerMod(int $wager_mod): void
    {
        $this->wager_mod = $wager_mod;
    }

    /**
     * Получить модификатор депозита
     * @return int
     */
    public function getDepositMod(): int
    {
        return $this->deposit_mod;
    }

    /**
     * Установить модификатор депозита
     * @param int $deposit_mod
     */
    public function setDepositMod(int $deposit_mod): void
    {
        $this->deposit_mod = $deposit_mod;
    }

    /**
     * Получить статус активности
     * @return bool
     */
    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    /**
     * Установить статус активности
     * @param bool $enabled
     */
    public function setEnabled(bool $enabled): void
    {
        $this->enabled = $enabled;
    }


}
