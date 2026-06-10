<?php

namespace App\Models\Bonuses;

use App\GamesProviders\IGameWallet;
use App\Helpers\UI;
use App\MUser;

class NonDepBonus extends KUserBonus
{
    public function getName(): string
    {
        return __('bonus.bonus_54_name');
    }

    public function getDescriptionName(): ?string
    {
        return 'non-dep';
    }

    public function getDescription(): string
    {
        return __('bonus.bonus_54_descr');
    }

    /**
     * @return bool
     */
    public function activate(): bool
    {
        $user = MUser::getAuth();
        if (!$user->isEmailVerified()) {
            $this->_error = __('bonus.bonus_26_email_required');
            return false;
        }

       return parent::activate();
    }

    public function getProgressPercents(): int
    {
        $p = 0;
        if ($this->wager_to_complete > 0) {
            $p = $this->total_bet / $this->wager_to_complete * 100;
        }
        if ($p > 100) $p = 100;
        return $p;
    }

    public function getProgressHint(): string
    {
        if ($this->isProgressAvailable()) {
            return __('bonus.bonus_26_played') . floor($this->total_bet) . " / " . floor($this->wager_to_complete);
        }
        return '';
    }

    public function getStatusString(IGameWallet $game_wallet = null): string
    {
        if ($this->status != static::STATUS_ACTIVE) return '';
        return UI::bonusPad(__('bonus.status_active'), $this->getProgressHint());
    }

}
