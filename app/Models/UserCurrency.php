<?php

namespace App\Models;

use App\UserWallet;
use Illuminate\Database\Eloquent\Model;

/**
 * Class UserCurrency
 * Данные о валюте.
 *
 * @property int $id
 * @property string $name
 * @property string $game_currency
 * @property int $hall_id
 * @property string $symbol
 * @property int $code
 * @property boolean $is_crypto
 * @property int $denomination
 * @property int $sport_code
 * @property string $vegas_bank
 * @property string $vegas_bonus_bank
 * @property float $min_payin_sum
 * @property float $min_payout_sum
 * @property float $max_auto_payout
 * @property float $max_payout_sum
 */
class UserCurrency extends Model
{
    private array $digits = [
        UserWallet::CURRENCY_mBTC => 4,
    ];

    public function isCrypto(): bool
    {
        return $this->is_crypto;
    }

    public static function byId(int $id): UserCurrency
    {
        return UserCurrency::findOrFail($id);
    }

    public static function byName(string $name): ?UserCurrency
    {
        return UserCurrency::where('name', $name)->first();
    }

    public function getDigits()
    {
        return $this->digits[$this->id]?? 2;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getGameCurrency(): string
    {
        return $this->game_currency;
    }

    public function getHallId(): int
    {
        return $this->hall_id;
    }

    public function getSymbol(): string
    {
        return $this->symbol;
    }

    public function getCode(): int
    {
        return $this->code;
    }

}
