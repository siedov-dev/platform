<?php

namespace App\GamesProviders\TVBet;

use App\GamesProviders\IGameWallet;
use App\MUser;
use App\Wallet;

/**
 * TvBet Wallet delegate to UserWallet
 */
class TvBetWallet implements IGameWallet
{
    private Wallet $_wallet;

    private function __construct(MUser $user, ?int $currency = null)
    {
        $this->_wallet = $user->getWallet($currency);
    }

    public static function getForUser(MUser $user, ?int $currency = null): IGameWallet
    {
        return new TvBetWallet($user, $currency);
    }

    public function getBalance(): float
    {
        return $this->_wallet->getBalance();
    }

    public function getCurrency(): int
    {
        return $this->_wallet->getCurrencyId();
    }

    public function addMoney(float $amount): IGameWallet
    {
        $this->_wallet->addMoney($amount);
        return $this;
    }

    /**
     * @throws \Exception
     */
    public function remMoney(float $amount): IGameWallet
    {
        $this->_wallet->remMoney($amount);
        return $this;
    }
}
