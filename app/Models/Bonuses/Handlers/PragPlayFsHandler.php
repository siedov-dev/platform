<?php

namespace App\Models\Bonuses\Handlers;

use App\GamesProviders\PragmaticPlay\PragmaticPlay;
use App\GamesProviders\IGameWallet;
use App\Helpers\Hlog;
use App\Models\Bonuses\Contracts\ExternalFsHandler;
use App\Models\Bonuses\UserBonus;
use App\Models\BonusStatistic;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;

class PragPlayFsHandler implements ExternalFsHandler
{
    private UserBonus $_bonus;
    private PragmaticPlay $_api;
    private string $_id = "";

    public function __construct(UserBonus &$bonus, string $id)
    {
        $this->_bonus = $bonus;
        $this->_api = new PragmaticPlay();
        $this->_id = $id;
    }

    public function externFreeSpinBet(IGameWallet $game_wallet, string $system, string $game_id, float $amount): float
    {
        Log::channel('pragmatik')->info("fb bonus bet id:" . $this->_id);
        $this->_bonus->withdrawCents($amount * 100, $game_wallet, $system, $game_id);
        $this->updateFsData();
        $balance = $this->_bonus->getBalanceCents($game_wallet) / 100;
        Redis::set('bonus-status-' . $this->_bonus->user_id, $this->_bonus->getStatusString());
        $this->_bonus->save();
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
        $this->_bonus->save();
        return $this->_bonus->getBalanceCents($game_wallet) / 100;
    }

    public function updateFsData(): void
    {
        $bonuses = $this->_api->getPlayersFRB($this->_bonus->user_id . $this->_bonus->curr_name)['bonuses'] ?? null;
        if (!empty($bonuses)) {
            foreach ($bonuses as $data) {
                if ($data['bonusCode'] == $this->_id) {
                    $this->_bonus->spins_available = $data['rounds'] - $data['roundsPlayed'];
                    $this->_bonus->spins_total = $data['rounds'];
                    $this->_bonus->save();
                }
            }
        }
    }

    public function cancelFs(string $id): void
    {
        try {
            $this->_api->cancelVFRB($id);
        } catch (\Exception $e) {
            Hlog::logException($e);
        }
        try {
            $this->_api->cancelFRB($id);
        } catch (\Exception $e) {
            Hlog::logException($e);
        }
    }

    public function createFs(string $id): bool
    {
        $all_games = json_decode($this->_bonus->allowed_games, true);
        $games = $all_games['pragplay'] ?? [];
        if ($this->_bonus->getJsonParam('max_bet', 0) > 0) {
            if ($this->_api->createPlayerVFRB(
                    player_id: $this->_bonus->getUserId() . $this->_bonus->curr_name,
                    currency_name: $this->_bonus->curr_name,
                    games_list: implode(',', $games),
                    rounds: $this->_bonus->getSpinsAvailable(),
                    bonus_id: $id,
                    expiration_date: $this->_bonus->getExpirationDate()->timestamp,
                    max_bet: $this->_bonus->getJsonParam('max_bet', 0),
                ) === false) {
                Log::error('Ошибка при активации vfrb бонуса PPlay #' . $this->_bonus->getId());
                return false;
            }
        } elseif ($this->_api->createFRB(
                player_id: $this->_bonus->getUserId() . $this->_bonus->curr_name,
                currency_name: $this->_bonus->curr_name,
                games_list: implode(',', $games),
                rounds: $this->_bonus->getSpinsAvailable(),
                bonus_id: $id,
                expiration_date: $this->_bonus->getExpirationDate()->timestamp
            ) === false) {
            Log::error('Ошибка при активации бонуса PPlay #' . $this->_bonus->getId());
            return false;
        }
        return true;
    }
}
