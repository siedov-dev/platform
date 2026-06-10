<?php

namespace App\Models\Bonuses;

use App\GamesProviders\IGameWallet;
use App\Helpers\Hlog;
use App\Helpers\UI;
use App\Models\Bonuses\Contracts\BonusRenderer;
use App\Models\Bonuses\Render\WelcomeBonusRenderer;
use App\Models\BonusPatterns;
use App\MUser;
use App\Services\KBonus\KBonus;
use App\Services\Users\UserManager;

class SharedMoneyBonus extends KUserBonus
{

    public function confirmPayment(MUser $user, float $amount, int $currency): float
    {
        $bonus = KBonus::getInstance()->confirmPayment($this->getId(), $amount, $currency);
        if ($bonus !== null) {
            $this->attributes = $bonus->toArray();
            $this->meta = $bonus->getMeta();
            if ($this->isActive()) $user->getWallet($currency)->setBonusId($this->getId());
        }
        // на данный момент ни один бонус не должен требовать реальных денег для активации
        return $amount;
    }

    public function getName(): string
    {
        return __('bonus.bonus_46_name');
    }

    public function getDescription(): string
    {
        switch ($this->status) {
            case self::STATUS_ACTIVE:
                return __('bonuses.swBonusDescActive');

            case self::STATUS_WAITING_FOR_AGREEMENT:
                return __('bonuses.swBonusDescAgreement', [
                    'sum' => $this->money_to_activate,
                    'currency' => $this->curr_name,
                    'bonus' => ceil($this->multiplier_to_payout),
                    'wager' => $this->multiplier,
                ]);

            case self::STATUS_WAITING_FOR_PAYMENT:
                return __('bonuses.swBonusDescPayment', [
                    'sum' => $this->money_to_activate,
                    'currency' => $this->curr_name,
                ]);
        }
        return __('bonuses.swBonusDescAgreement', [
            'sum' => $this->money_to_activate,
            'currency' => $this->curr_name,
            'bonus' => ceil($this->multiplier_to_payout),
            'wager' => $this->multiplier,
        ]);
    }


    public function getDescriptionName(): ?string
    {
        return 'shared-money';
    }

    public function getProgressPercents(): int
    {
        if ($this->wager_to_complete == 0) return 100;
        $p = floor($this->total_bet / $this->wager_to_complete * 100);
        if ($p > 100) $p = 100;
        return $p;
    }

    public function getProgressHint(): string
    {
        if ($this->isProgressAvailable()) {
            return __('bonus.wager_left', [
                'val' => floor($this->wager_to_complete) - floor($this->total_bet),
                'cur' => $this->curr_name,
            ]);
        }
        return '';
    }

    public function getStatusString(IGameWallet $game_wallet = null): string
    {
        return UI::bonusPad('Progress', $this->getProgressHint());
    }

    public function forceToPlay(): bool
    {
        return true;
    }

    public function getRenderer(): BonusRenderer
    {
        // assume that pattern is welcome bonus only
        if ($this->pattern_id !== null && in_array($this->pattern_id, [
            ...BonusPatterns::getRegFsPatternIds($this->curr_name),
            ...BonusPatterns::getRegPatternIds($this->curr_name),
            ])) {
            return new WelcomeBonusRenderer($this);
        }
        return parent::getRenderer();
    }

    public function getShortDescription(): string
    {
        if ($this->pattern_id !== null && in_array($this->pattern_id, [
                ...BonusPatterns::getRegFsPatternIds($this->curr_name),
                ...BonusPatterns::getRegPatternIds($this->curr_name),
            ])) {
            return view('common.bonus-descriptions.wb-dep', ['bonus' => $this])->render();
        }
        if (view()->exists('common.bonus-descriptions.t'.$this->type)) return view('common.bonus-descriptions.t'.$this->type, ['bonus' => $this])->render();
        return 'NO DESCRIPTION '.$this->type;
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

    public function actualize()
    {
        if ($this->isExpired()) return;
        if ($this->isActive()) {
            UserManager::walletSetBonusId($this->user_id, $this->currency, $this->id);
        }
    }

    public function isDeposit(): bool
    {
        return true;
    }
}
