<?php

namespace App\Models\Bonuses;

use App\GamesProviders\IGameWallet;
use App\Helpers\UI;
use App\Models\BonusMenuParam;

/**
 * @deprecated
 */
class SlegFsMenu extends KUserBonus
{

    public function getActivationMenu(): ?array
    {
        $params = BonusMenuParam::getBonusParams($this->type, $this->currency);
        if ($params !== null) {
            $data = [];
            /** @var BonusMenuParam $param */
            foreach ($params as $param) {
                $p_name = $param->name;
                $data['wager'][$p_name] = $param->min_value;
            }
            return $data;
        }
        return [];
    }

    public function getName(): string
    {
        return __('bonus.bonus_39_name');
    }

    public function getDescription(): string
    {
        switch ($this->status) {
            case self::STATUS_ACTIVE:
                return __('bonus.bonus_39_descr1');

            case self::STATUS_WAITING_FOR_PAYMENT:
                return __('bonus.bonus_39_descr2', ['amount' => $this->money_to_activate, 'curr' => $this->curr_name]);
        }

        return __('bonus.bonus_39_descr3');
    }

    public function getDescriptionName(): ?string
    {
        return 'sleg-fs-menu';
    }

    public function getStatusString(IGameWallet $game_wallet = null): string
    {
        if ($this->status != static::STATUS_ACTIVE) return '';
        if ($this->spins_available > 0) return '';
        return UI::bonusPad(__('bonus.status_active'), $this->getProgressHint());
    }

}
