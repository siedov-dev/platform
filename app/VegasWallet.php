<?php

namespace App;

use App\GamesProviders\IGameWallet;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

/**
 * Class VegasWallet
 * @package App
 * @property int user_id
 * @property int money
 * @property int currency
 * @property int bonus
 * @property float total_bet
 * @property float total_win
 * @property int total_spins
 * @property string session_id
 * @deprecated
 */
class VegasWallet extends Model implements IGameWallet
{
    //

    public static function getForUser(MUser $user, ?int $currency = null): VegasWallet
    {
        if (!$currency) $currency = $user->currency;
        $wallet = VegasWallet::where('user_id', $user->id)
            ->where('currency', $currency)->LockForUpdate()->first();

        if (!$wallet) {
            $wallet = new VegasWallet();
            $wallet->user_id = $user->id;
            $wallet->currency = $currency;
            $wallet->save();

            $wallet = VegasWallet::where('user_id', $user->id)
                ->where('currency', $currency)->LockForUpdate()->first();
        }

        return $wallet;
    }

    /**
     * add money to wallet
     * @param float $amount
     * @return $this
     */
    public function addMoney(float $amount): VegasWallet
    {
        $this->increment('money', intval($amount * 100));
        return $this;
    }

    /**
     * remove money from wallet
     * @param float $amount
     * @return $this
     */
    public function remMoney(float $amount): VegasWallet
    {
        $this->decrement('money', intval($amount * 100));
        if ($this->money < 0) {
            $this->money = 0;
            $this->save();
            Log::error('vegas wallet balance < 0 on decrement');
        }
        return $this;
    }

    public function getBalance(): float
    {
        return $this->money / 100;
    }

    public function getCurrency(): int
    {
        return $this->currency;
    }
}
