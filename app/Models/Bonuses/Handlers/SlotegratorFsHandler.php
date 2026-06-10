<?php

namespace App\Models\Bonuses\Handlers;

use App\GamesProviders\IGameWallet;
use App\GamesProviders\Slotegrator\SlotegratorApi;
use App\Models\Bonuses\Contracts\ExternalFsHandler;
use App\Models\Bonuses\UserBonus;
use App\Models\BonusStatistic;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;

class SlotegratorFsHandler implements ExternalFsHandler
{
    private UserBonus $_bonus;
    private SlotegratorApi $_api;

    public function __construct(UserBonus &$bonus)
    {
        Log::channel('slotegrator')->info("created sleg handler");

        $this->_bonus = $bonus;
        $this->_api = new SlotegratorApi();
    }

    public function externFreeSpinBet(IGameWallet $game_wallet, string $system, string $game_id, float $amount): float
    {
        $balance = $this->_bonus->getBalanceCents($game_wallet) / 100;
        Redis::set('bonus-status-' . $this->_bonus->user_id, $this->_bonus->getStatusString());
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
        $this->_bonus->setSpinsAvailable(-1);
        $this->_bonus->total_bet = 0;
        $this->_bonus->wager_to_complete = (float)bcmul($this->_bonus->getMoneyAvailable(), $this->_bonus->multiplier);

        BonusStatistic::saveStat($this->_bonus, $this->_bonus->user_id, BonusStatistic::ACTION_FS_DONE, $this->_bonus->getMoneyAvailable(),
            $this->_bonus::STATUS_ACTIVE, $this->_bonus->status, $this->_bonus->pattern_id, [
                'wager' => $this->_bonus->wager_to_complete,
            ]);

        $this->_bonus->save();
        return $this->_bonus->getBalanceCents($game_wallet) / 100;
    }

    public function updateFsData(): void
    {
    }

    public function cancelFs(string $id): void
    {
        $this->_api->freeSpinsCancel($id);
    }

    public function createFs(string $id): bool
    {
        $game_id = $this->_bonus->game_id;
        if (empty($game_id)) {
            $json = json_decode($this->_bonus->allowed_games, true);
            if (!empty($json) && is_array($json)) {
                $game_provider = key($json);
                if (empty($json[$game_provider])) {
                    Log::error('Ошибка при активации бонуса Sleg #' . $this->_bonus->getId() . ' no game for provider ' . $game_provider);
                    return false;
                }
                $game_id = current($json[$game_provider]);
            } else {
                Log::error('Ошибка при активации бонуса Sleg #' . $this->_bonus->getId() . ' Empty game list ');
                return false;
            }
        }
        if ($this->_api->freeSpinsSet(
                id: $id,
                game_id: $game_id,
                player_id: 'bonus' . $this->_bonus->getUserId() . $this->_bonus->curr_name,
                quantity: $this->_bonus->getSpinsAvailable(),
                currency: $this->_bonus->curr_name,
                active_to: $this->_bonus->getExpirationDate()->timestamp,
                max_bet: $this->_bonus->getJsonParam('max_bet', 0),
            )['status'] !== 'Created') {
            Log::error('Ошибка при активации бонуса Sleg #' . $this->_bonus->getId());
            return false;
        }
        return true;
    }
}
