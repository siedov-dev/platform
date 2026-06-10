<?php

namespace App\Models\Bonuses\Handlers;

use App\GamesProviders\IGameWallet;
use App\Models\Bonuses\Contracts\ExternalFsHandler;
use App\Models\Bonuses\UserBonus;
use App\Models\BonusStatistic;
use Illuminate\Support\Facades\Redis;

class TestFsHandler implements ExternalFsHandler
{
    private UserBonus $_bonus;
    public function __construct(UserBonus &$bonus)
    {
        $this->_bonus = $bonus;
    }

    public function externFreeSpinBet(IGameWallet $game_wallet, string $system, string $game_id, float $amount): float
    {
        $this->_bonus->withdrawCents($amount * 100, $game_wallet, $system, $game_id);
        $this->updateFsData();
        $balance = $this->_bonus->getBalanceCents($game_wallet) / 100;
        Redis::set('bonus-status-' . $this->_bonus->user_id, $this->_bonus->getStatusString());
        $this->save();
        return $balance;
    }

    public function externFreeSpinWin(IGameWallet $game_wallet, string $system, string $game_id, float $amount): float
    {
        $this->_bonus->depositCents($amount * 100, $game_wallet, $system, $game_id);
        $this->updateFsData();
        $balance = $this->_bonus->getBalanceCents($game_wallet) / 100;
        Redis::set('bonus-status-' . $this->_bonus->user_id, $this->_bonus->getStatusString());
        $this->_bonus->save();
        return $balance;
    }

    public function externFreeSpinsCompleted(IGameWallet $game_wallet, string $system, string $game_id, float $amount): float
    {
        $this->_bonus->spins_available = -1;
        $this->_bonus->wager_to_complete = (float)bcmul($this->_bonus->money_available, $this->_bonus->multiplier);
        BonusStatistic::saveStat($this->_bonus, $this->_bonus->user_id, BonusStatistic::ACTION_FS_DONE, $this->_bonus->money_available,
            $this->_bonus::STATUS_ACTIVE, $this->_bonus->status, $this->_bonus->pattern_id, [
                'wager' => $this->_bonus->wager_to_complete,
            ]);
        $this->updateFsData();
        $this->_bonus->save();
        return $this->_bonus->getBalanceCents($game_wallet) / 100;
    }

    public function updateFsData(): void
    {
    }

    public function cancelFs(string $id): void
    {
    }

    public function createFs(string $id): bool
    {
        return true;
    }
}
