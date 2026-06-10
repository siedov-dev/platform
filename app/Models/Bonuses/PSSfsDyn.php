<?php

namespace App\Models\Bonuses;

use App\Helpers\UI;
use App\Models\Bonuses\Contracts\BonusRenderer;
use App\Models\Bonuses\Contracts\FirstDepBonusInfo;
use App\Models\Bonuses\Render\DynFsBonusRenderer;
use App\Models\BonusMenuParam;
use App\Models\BonusPatterns;
use App\GamesProviders\IGameWallet;
use Illuminate\Support\Facades\Log;

class PSSfsDyn extends KUserBonus implements FirstDepBonusInfo
{

    public function getMinDepSum($currency): float
    {
        return BonusMenuParam::getLowestMinValue($this->type, $currency);
    }

    public function getName(): string
    {
        return __('bonus.bonus_51_name');
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


    public function getRenderer(): BonusRenderer
    {
        return new DynFsBonusRenderer($this);
    }
}
