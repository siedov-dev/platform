<?php

namespace App\Models\Bonuses;

use App\Helpers\Hlog;
use App\Helpers\UI;
use App\GamesProviders\IGameWallet;
use App\Services\KBonus\KBonus;

/**
 * @deprecated use PSSmartFs
 */
class SlegSmartFs extends KUserBonus
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
        return 'sleg-smart-wager';
    }

    public function getStatusString(IGameWallet $game_wallet = null): string
    {
        if ($this->status != static::STATUS_ACTIVE) return '';
        if ($this->spins_available > 0) return '';
        return UI::bonusPad(__('bonus.status_active'), $this->getProgressHint());
    }

    public function afterCreated(): void
    {
        if (empty($this->id)) {
            Hlog::logException(new \Exception('Trying attach bonus with no id to segment'));
            return;
        }
        parent::afterCreated();

//        BonusSegment::attachOrCreateByName($this, $this->multiplier_to_payout."% к депозиту");
        KBonus::getInstance()->addBonusToSegmentByName($this->getId(), $this->multiplier_to_payout."% к депозиту");
    }

}
