<?php

namespace App\Models\Bonuses;

use App\GamesProviders\IGameWallet;
use App\GamesProviders\Game;
use App\GamesProviders\GamesManager;
use App\Helpers\Hlog;
use App\Helpers\UI;
use App\MUser;
use App\Services\KBonus\KBonus;
use App\Services\Users\UserManager;

class SharedMoneyFsBonus extends KUserBonus
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

    /**
     * bonus doesn't have money to payout
     * @return float
     */
    public function getMoneyToPayout(): float
    {
        return 0;
    }

    public function getName(): string
    {
        return __('bonus.bonus_47_name');
    }

    public function isGameAllowed(Game $game): bool
    {
        // only one game in freespins and all games for wager
        if ($this->getSpinsAvailable() > 0) return $this->game_id === $game->getId();
        return parent::isGameAllowed($game);
    }

    public function isGameAllowedByProvider(string $providerId, string $gameId): bool
    {
        // only one game in freespins and all games for wager
        if ($this->getSpinsAvailable() > 0) return $this->game_id === $gameId && $this->game_provider === $providerId;

        $game = GamesManager::getProvider($providerId)->getGame('', $gameId, true);
        if ($game !== null) return $this->isGameAllowed($game);
        return true;
    }

    public function getStatusName(): string
    {
        $this->isLocked();
        return parent::getStatusName();
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

    public function getProgressHint(): string
    {
        if ($this->spins_available > 0) {
            return __('bonus.spins_made') . ($this->spins_total - $this->spins_available) . '/' . $this->spins_total;
        }
        if ($this->isProgressAvailable()) {
            return __('bonuses.swBonusProgress', [
                'bet' => floor($this->total_bet),
                'total' => floor($this->wager_to_complete)
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
        // only one game in freespins and all games for wager
        if ($this->getSpinsAvailable() > 0) return false;
        return true;
    }

    public function isDirectPlayUrl(): bool
    {
        if ($this->getSpinsAvailable() <= 0) return false;
        return parent::isDirectPlayUrl();
    }

    public function allGamesAllowed(): bool
    {
        if ($this->getSpinsAvailable() > 0) return false;
        return true;
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

    public function actualize()
    {
        if ($this->isExpired()) return;
        if ($this->isActive()) {
            UserManager::walletSetBonusId($this->user_id, $this->currency, $this->id);
        }
    }

}
