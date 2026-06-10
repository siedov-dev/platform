<?php

namespace App\Models\Bonuses;

use App\Helpers\UI;
use App\GamesProviders\IGameWallet;

class PSFreeSpins extends KUserBonus
{

    public function getName(): string
    {
        return __('bonus.bonus_30_name');
    }

    public function getDescription(): string
    {
        switch ($this->status) {
            case self::STATUS_ACTIVE:
                return __('bonus.bonus_30_descr1');

            case self::STATUS_WAITING_FOR_PAYMENT:
                return __('bonus.bonus_30_descr2', ['amount' => $this->money_to_activate, 'curr' => $this->curr_name]);
        }

        return __('bonus.bonus_30_descr3');
    }

    public function getDescriptionName(): ?string
    {
        return 'ps-free-spins';
    }

    public function getStatusString(IGameWallet $game_wallet = null): string
    {
        if ($this->status != static::STATUS_ACTIVE) return '';
        if ($this->spins_available > 0) return '';
        return UI::bonusPad(__('bonus.status_active'), $this->getProgressHint());
    }

}
