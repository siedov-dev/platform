<?php

namespace App\Models\Bonuses;

use App\GamesProviders\IGameWallet;
use App\Helpers\UI;
use App\Models\BonusMenuParam;
use App\Wallet;

class ChooseWagerFs2 extends KUserBonus
{
    public function getName(): string
    {
        return __('bonus.bonus_27_name');
    }

    public function getDescription(): string
    {
        switch ($this->status) {
            case self::STATUS_ACTIVE:
                return __('bonus.bonus_27_descr1');

            case self::STATUS_WAITING_FOR_PAYMENT:
                return __('bonus.bonus_27_descr3', ['amount' => number_format($this->money_to_activate, 2), 'curr' => $this->curr_name]);
        }

        return __('bonuses.bonus_27_descr4', [
            'count' => $this->spins_total,
        ]);
    }

    public function getDescriptionName(): ?string
    {
        return 'choose-wager-fs2';
    }


    public function isProgressAvailable(): bool
    {
        return $this->status == self::STATUS_ACTIVE;
    }

    public function getProgressHint(): string
    {
        if ($this->spins_available > 0) {
            $spinsAvailable = $this->spins_available > 0 ? $this->spins_available : 0;
            return __('bonuses.wagerFSProgress', [
                'spins' => $this->spins_total - $spinsAvailable,
                'total' => $this->spins_total
            ]);
        }
        if ($this->isProgressAvailable()) {
            return floatval($this->total_bet) . '/' . intval($this->wager_to_complete);
        }
        return '';
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

    public function getActivationMenu(): ?array
    {
        $params = BonusMenuParam::getBonusParams($this->type, $this->currency);
        if ($params !== null) {
            $data = [];
            /** @var BonusMenuParam $param */
            foreach ($params as $param) {
                $p_name = $param->name . " (" . UI::moneyStr($param->min_value) . $this->curr_name .  ").";
                $data[__('bonus.menu_choose_wager')][$p_name] = $param->min_value;
            }
            return $data;
        }
        return [];
    }

    public function getStatusString(IGameWallet $game_wallet = null): string
    {
        if ($this->status != static::STATUS_ACTIVE) return '';

        if ($this->spins_available > 0) {
            if ($this->status != static::STATUS_ACTIVE) return '';
            $spinsAvailable = $this->spins_available > 0 ? $this->spins_available : 0;
            return __('bonus.bonus_pad', [
                'spin' => ($this->spins_total - $spinsAvailable),
                'spins' => $this->spins_total,
                'max_bet' => 20,
                'amount' => number_format($this->total_win, 2),
                'curr' => Wallet::currencyName($this->currency)
            ]);
        }
        return UI::bonusPad('Active bonus', $this->getProgressHint());
    }

}
