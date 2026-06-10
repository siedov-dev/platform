<?php

namespace App\Models\Bonuses;

use App\GamesProviders\GamesProvider;
use App\GamesProviders\IGameWallet;
use App\Helpers\UI;
use App\Jobs\ActualizeBonusJob;
use App\Models\Bonuses\Contracts\BonusRenderer;
use App\Models\Bonuses\Contracts\ExternalFsHandler;
use App\Models\Bonuses\Handlers\PragPlayFsHandler;
use App\Models\Bonuses\Handlers\SlotegratorFsHandler;
use App\Models\Bonuses\Render\WelcomeFsBonusRenderer;
use App\Models\BonusPatterns;
use App\Models\BonusStatistic;
use App\Models\BonusWin;
use App\MUser;
use App\UserWallet;
use App\Wallet;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PSNonDepFs extends KUserBonus
{

    public function getName(): string
    {
        return __('bonus.bonus_36_name');
    }

    public function getDescription(): string
    {
        switch ($this->status) {
            case self::STATUS_ACTIVE:
                return __('bonus.bonus_35_descr1');

            case self::STATUS_WAITING_FOR_PAYMENT:
                return __('bonus.bonus_30_descr2', ['amount' => $this->money_to_activate, 'curr' => $this->curr_name]);
        }

        return __('bonus.bonus_35_descr3');
    }

    public function getDescriptionName(): ?string
    {
        return 'ps-non-deposit';
    }

    public function getStatusString(IGameWallet $game_wallet = null): string
    {
        if ($this->status != static::STATUS_ACTIVE) return '';
        if ($this->spins_available > 0) return '';
        return UI::bonusPad(__('bonus.status_active'), $this->getProgressHint());
    }


    public function getRenderer(): BonusRenderer
    {
        //if welcome bonus freespins
        if (in_array($this->pattern_id, [...BonusPatterns::getRegFsPatternIds($this->curr_name), ...BonusPatterns::getRegNonDepPatternIds($this->curr_name)])) {
            return new WelcomeFsBonusRenderer($this);
        }
        return parent::getRenderer();
    }

    public function getShortDescription(): string
    {
        if (in_array($this->pattern_id, [...BonusPatterns::getRegFsPatternIds($this->curr_name), ...BonusPatterns::getRegNonDepPatternIds($this->curr_name)])) {
            return view('common.bonus-descriptions.wb-fs', ['bonus' => $this])->render();
        }
        return parent::getShortDescription();
    }

}
