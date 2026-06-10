<?php

namespace App\Models\Bonuses;

use App\Helpers\UI;
use App\Models\Bonuses\Contracts\FirstDepBonusInfo;
use App\Models\BonusMenuParam;
use App\GamesProviders\IGameWallet;
use Illuminate\Support\Facades\Log;

/**
 * @deprecated use PSSFsDyn
 */
class SlegSfsDyn extends KUserBonus implements FirstDepBonusInfo
{
    public function getMinDepSum($currency): float
    {
        return BonusMenuParam::getLowestMinValue(UserBonus::TYPE_SLEG_SFS_DYNAMIC, $currency);
    }

    public function getName(): string
    {
        return __('bonus.bonus_43_name');
    }

    public function getDescription(): string
    {
        switch ($this->status) {
            case self::STATUS_ACTIVE:
                return __('bonus.bonus_43_descr1');

            case self::STATUS_WAITING_FOR_PAYMENT:
                return __('bonus.bonus_43_descr2', ['amount' => $this->money_to_activate, 'curr' => $this->curr_name]);
        }

        return __('bonus.bonus_43_descr3');
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

    public function freespinsConditions(): array
    {
        $params = BonusMenuParam::getBonusParams($this->type, $this->getCurrency());
        if ($params === null) {
            Log::info('range params for bonus '.__CLASS__. ' currency ' . $this->getCurrency());
            return [];
        }
        $result = [];
        foreach ($params as $param) {
            $result[$param->value] = $param->min_value;
        }
        ksort($result);

        return $result;
    }

}
