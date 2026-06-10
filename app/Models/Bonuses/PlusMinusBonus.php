<?php

namespace App\Models\Bonuses;


use App\GamesProviders\IGameWallet;
use App\Helpers\UI;

/**
 * @deprecated
 */
class PlusMinusBonus extends KUserBonus
{
    public function getName(): string
    {
        return __('bonus.bonus_5_name');
    }

    public function getDescription(): string
    {
        return __('bonus.bonus_5_desrc');
    }

    public function getDescriptionName(): ?string
    {
        return 'plus-minus';
    }


    public function getProgressPercents(): int
    {
        $moneyAvailable = $this->money_available;
        if ($moneyAvailable > $this->wager_to_complete) {
            $moneyAvailable -= $this->wager_to_complete;
        }
        return $moneyAvailable / $this->wager_to_complete * 100;
    }

    public function getProgressHint(): string
    {
        if ($this->isDone()) {
            return '';
        }
        return __('bonus.bonus_5_win') . number_format($this->money_available, 2) . '/' . $this->wager_to_complete;
    }

    public function getStatusString(IGameWallet $game_wallet = null): string
    {
        if ($this->status != static::STATUS_ACTIVE) return '';
        return UI::bonusPad(__('bonus.status_active'), $this->getProgressHint());
    }

}
