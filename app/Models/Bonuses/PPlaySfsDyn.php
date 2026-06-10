<?php

namespace App\Models\Bonuses;

use App\GamesProviders\PragmaticPlay\PragmaticPlay;
use App\GamesProviders\IGameWallet;
use App\Helpers\UI;
use App\Jobs\ActualizeBonusJob;
use App\Models\Bonuses\Contracts\FirstDepBonusInfo;
use App\Models\BonusMenuParam;
use App\Models\BonusPatterns;
use App\Models\BonusStatistic;
use App\Models\BonusWagerPrc;
use App\Models\BonusWin;
use App\MUser;
use App\UserWallet;
use App\Wallet;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;

/**
 * @deprecated use PSSFsDyn
 */
class PPlaySfsDyn extends KUserBonus implements FirstDepBonusInfo
{

    public function getMinDepSum($currency): float
    {
        return BonusMenuParam::getLowestMinValue(UserBonus::TYPE_PPLAY_SFS_DYNAMIC, $currency);
    }

    public function getName(): string
    {
        return __('bonus.bonus_42_name');
    }

    public function getDescription(): string
    {
        switch ($this->status) {
            case self::STATUS_ACTIVE:
                return __('bonus.bonus_42_descr1');

            case self::STATUS_WAITING_FOR_PAYMENT:
                return __('bonus.bonus_42_descr2', ['amount' => $this->money_to_activate, 'curr' => $this->curr_name]);
        }

        return __('bonus.bonus_42_descr3');
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
}
