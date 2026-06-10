<?php

namespace App\GamesProviders;

use App\MUser;
use App\Wallet;

/**
 * UserWallet IGameWallet adapter
 */
class WalletGameWalletAdapter implements IGameWallet
{
    private Wallet $wallet;

    public function __construct(Wallet $wallet)
    {
        $this->wallet = $wallet;
    }

    public static function getForUser(MUser $user, ?int $currency = null): IGameWallet
    {
        return new static($user->getWallet($currency));
    }

    public function getBalance(): float
    {
        return $this->wallet->getBalance();
    }

    public function getCurrency(): int
    {
        return $this->wallet->getCurrencyId();
    }

    public function addMoney(float $amount): IGameWallet
    {
        $this->wallet->addMoney($amount)->save();
        return $this;
    }

    public function remMoney(float $amount): IGameWallet
    {
        $this->wallet->remMoney($amount)->save();
        return $this;
    }
}
