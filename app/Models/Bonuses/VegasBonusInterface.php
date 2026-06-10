<?php


namespace App\Models\Bonuses;


use App\GamesProviders\IGameWallet;

interface VegasBonusInterface
{

    /**
     * get current bonus balance
     * @param IGameWallet|null $game_wallet
     * @return int
     */
    public function getBalanceCents(IGameWallet $game_wallet = null): int;

    /**
     * return status sting of the bonus
     * @param IGameWallet|null $game_wallet
     * @return string
     */
    public function getStatusString(IGameWallet $game_wallet = null): string;

}
