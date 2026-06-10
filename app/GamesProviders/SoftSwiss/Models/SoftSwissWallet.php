<?php

namespace App\GamesProviders\SoftSwiss\Models;

use App\GamesProviders\IGameWallet;
use App\GamesProviders\WalletInCents;
use App\Models\UserCurrency;
use App\MUser;
use App\UserWallet;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

/**
 * @property int id
 * @property int user_id
 * @property int amount
 * @property int digits
 * @property int currency
 * @property string game_currency
 * @property string currency_name
 * @property int bonus_id
 * @property string token
 * @property string session_id
 * @property Carbon created_at
 * @property Carbon updated_at
 * */
class SoftSwissWallet extends Model implements IGameWallet, WalletInCents
{
    use HasFactory;

    protected $dates = [
        'created_at',
        'updated_at',
    ];

    public static function getForUser(MUser $user, ?int $currency = null): self
    {
        if (!$currency) $currency = $user->currency;

        $wallet = self::where('user_id', $user->id)
            ->where('currency', $currency)->LockForUpdate()->first();

        if (!$wallet) {
            $wallet = new self();
            $wallet->user_id = $user->id;
            $wallet->currency = $currency;
            $wallet->currency_name = UserWallet::currencyName($currency);
            $wallet->digits = UserCurrency::byId($currency)->getDigits();
            $wallet->save();

            $wallet = self::where('user_id', $user->id)
                ->where('currency', $currency)->LockForUpdate()->first();
        }

        return $wallet;
    }

    public static function getBySession(MUser $user, string $session): ?self
    {
        return self::where('user_id', $user->id)
            ->where('session_id', $session)->LockForUpdate()->first();
    }

    public function getBalance(): float
    {
        return $this->digits != 0 ? $this->amount / pow(10, $this->digits) : $this->amount;
    }

    public function getBalanceCents(): int
    {
        return $this->amount;
    }

    public function getCurrency(): int
    {
        return $this->currency;
    }

    public function getCurrencyName(): string
    {
        return $this->currency_name;
    }

    public function getGameCurrencyName(): string
    {
        return $this->game_currency?? $this->currency_name;
    }

    public function addMoney(float $amount): self
    {
        if ($this->digits != 0) $amount = $amount * pow(10, $this->digits);
        $this->increment('amount', intval($amount));
        return $this;
    }

    public function remMoney(float $amount): self
    {
        if ($this->digits != 0) $amount = $amount * pow(10, $this->digits);
        $this->decrement('amount', intval($amount));
        if ($this->amount < 0) {
            $this->amount = 0;
            $this->save();
            Log::error(__CLASS__ . ' wallet balance < 0 on decrement');
        }
        return $this;
    }

    public function addCents(int $amount): self
    {
        $this->increment('amount', $amount);
        return $this;
    }

    public function remCents(int $amount): self
    {
        $this->decrement('amount', $amount);
        if ($this->amount < 0) {
            $this->amount = 0;
            $this->save();
            Log::error(__CLASS__ . ' wallet balance < 0 on decrement');
        }
        return $this;
    }

    public function getDigits(): int
    {
        return $this->digits;
    }

    public function toFloatValue(?int $value = null): float
    {
        if ($value === null) $value = $this->amount;
        return $this->digits != 0 ?$value / pow(10, $this->digits) :$value;
    }
}
