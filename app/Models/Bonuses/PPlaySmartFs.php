<?php

namespace App\Models\Bonuses;

use App\Helpers\UI;
use App\Models\BonusPatterns;
use App\GamesProviders\IGameWallet;

/**
 * @deprecated use PSSmartFs
 */
class PPlaySmartFs extends KUserBonus
{

    public function getName(): string
    {
        return __('bonus.bonus_31_name');
    }

    public function getDescription(): string
    {
        switch ($this->status) {
            case self::STATUS_ACTIVE:
                return __('bonus.bonus_31_descr1');

            case self::STATUS_WAITING_FOR_PAYMENT:
                return __('bonus.bonus_31_descr2', ['amount' => $this->money_to_activate, 'curr' => $this->curr_name]);
        }

        return __('bonus.bonus_31_descr3');
    }

    public function getDescriptionName(): ?string
    {
        if ($this->pattern_id == BonusPatterns::PTN_FIRST_DEP_BONUS_SW) return 'first-deposit-sw';
        if ($this->pattern_id == BonusPatterns::PTN_SECOND_DEP_BONUS_SW) return 'second-deposit-sw';
        if ($this->pattern_id == BonusPatterns::PTN_THIRD_DEP_BONUS_SW) return 'third-deposit-sw';
        return 'smart-wager';
    }

    public function getStatusString(IGameWallet $game_wallet = null): string
    {
        if ($this->status != static::STATUS_ACTIVE) return '';
        if ($this->spins_available > 0) return '';
        return UI::bonusPad(__('bonus.status_active'), $this->getProgressHint());
    }

}
