<?php

namespace App\GamesProviders;

use App\MUser;

interface IGameWallet
{
    /**
     * get or create game wallet
     * @param MUser $user
     * @param int|null $currency
     * @return IGameWallet
     */
    public static function getForUser(MUser $user, ?int $currency = null): IGameWallet;

    /**
     * wallet balance
     * @return float
     */
    public function getBalance(): float;

    /**
     * wallet currency
     * @return int
     */
    public function getCurrency(): int;

    /**
     * add money to wallet
     * @param float $amount
     * @return IGameWallet
     */
    public function addMoney(float $amount): IGameWallet;

    /**
     * remove money from wallet
     * @param float $amount
     * @return IGameWallet
     */
    public function remMoney(float $amount): IGameWallet;
}
