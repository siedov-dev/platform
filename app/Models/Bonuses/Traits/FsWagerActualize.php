<?php

namespace App\Models\Bonuses\Traits;

use App\GamesProviders\IGameWallet;
use App\Jobs\ActualizeBonusJob;
use App\Models\Bonuses\UserBonus;
use App\Models\BonusStatistic;
use App\Models\BonusWin;
use App\MUser;

trait FsWagerActualize
{
    public function actualize()
    {
        /** @var UserBonus $this */
        if ($this->isExpired()) return;

        // Do nothing for inactive bonus.
        if ($this->status != UserBonus::STATUS_ACTIVE) return;

        if ($this->spins_available == 0) {
            $this->spins_available = -1;
            $this->wager_to_complete = (float)bcmul($this->money_available, $this->wager_to_complete);
            BonusStatistic::saveStat($this, $this->user_id, BonusStatistic::ACTION_FS_DONE,
                $this->money_available, $this->status, $this->status, $this->pattern_id,
                [
                    'wager' => $this->wager_to_complete,
                    'money' => $this->money_available,
                ]);
        }

        // Complete bonus and transfer money if user won necessary amount of money.
        if ($this->spins_available < 0 && $this->total_bet >= $this->wager_to_complete) {
            if ($this->money_available > $this->max_win) {
                $this->money_available = $this->max_win;
            }
            $this->spins_available = -1;
            $this->money_paid_out = $this->money_available;
            $this->writeTransaction($this->money_available);

            /** @var MUser $user */
            $user = MUser::find($this->user_id);
            $user?->getWallet($this->currency)->addMoney($this->money_paid_out)->save();

            BonusStatistic::saveStat($this, $this->user_id, BonusStatistic::ACTION_WIN, $this->money_paid_out,
                UserBonus::STATUS_ACTIVE, UserBonus::STATUS_DONE);

            $this->money_available = 0;
            $this->status = UserBonus::STATUS_DONE;
            return;
        }

        if ($this->money_available <= 0 && $this->spins_available < 0) {
            $this->money_available = 0;
            $this->status = UserBonus::STATUS_DONE;
            BonusStatistic::saveStat($this, $this->user_id, BonusStatistic::ACTION_FAIL, $this->money_available,
                UserBonus::STATUS_ACTIVE, UserBonus::STATUS_DONE);
        }
    }

    public function actualizeVegas(string $game_id, IGameWallet $game_wallet, string $system = 'vegas')
    {
        /** @var UserBonus $this */
        // Mark bonus as expired if user don't activated bonus until the specified time.
        if ($this->status == UserBonus::STATUS_ACTIVE && strtotime($this->active_to) < time()) {
            $this->status = UserBonus::STATUS_EXPIRED;
            BonusStatistic::saveStat($this, $this->user_id, BonusStatistic::ACTION_EXPIRED,
                $this->money_available, $this->status, UserBonus::STATUS_EXPIRED);
            return;
        }

        // Do nothing for inactive bonus.
        if ($this->status != UserBonus::STATUS_ACTIVE) return;

        if ($this->spins_available == 0) {
            $this->spins_available = -1;
            $this->wager_to_complete = (float)bcmul($this->money_available, $this->wager_to_complete);
            BonusStatistic::saveStat($this, $this->user_id, BonusStatistic::ACTION_FS_DONE,
                $this->money_available, $this->status, $this->status, $this->pattern_id,
                [
                    'wager' => $this->wager_to_complete,
                    'money' => $this->money_available,
                ]);
        }

        // Complete bonus and transfer money if user won necessary amount of money.
        if ($this->spins_available < 0 && $this->total_bet >= $this->wager_to_complete) {
            if ($this->max_win > 0 && $this->money_available > $this->max_win) {
                $this->money_available = $this->max_win;
            }
            $this->spins_available = -1;
            $this->money_paid_out = $this->money_available;
            $this->writeTransaction($this->money_available);

            $game_wallet->addMoney($this->money_available);

            /** @var MUser $user */
            $user = MUser::find($this->user_id);
            BonusWin::userWin($user, $this->money_paid_out, $game_id, $this->currency, $system);
            BonusStatistic::saveStat($this, $this->user_id, BonusStatistic::ACTION_WIN, $this->money_paid_out,
                UserBonus::STATUS_ACTIVE, UserBonus::STATUS_DONE);

            $this->money_available = 0;
            $this->status = UserBonus::STATUS_DONE;
            return;
        }

        // wait win for 5+seconds or complete
        if ($this->money_available <= 0) {
            $this->money_available = 0;
            // call actualize in 5+ seconds
            ActualizeBonusJob::dispatch($this->getId())->delay(now()->addSeconds(5));
        }
    }

}
