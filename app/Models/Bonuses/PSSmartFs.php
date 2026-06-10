<?php

namespace App\Models\Bonuses;

use App\GamesProviders\IGameWallet;
use App\Helpers\Hlog;
use App\Helpers\UI;
use App\Models\BonusPatterns;
use App\Services\KBonus\KBonus;

class PSSmartFs extends KUserBonus
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


//
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
