<?php

namespace App\Models\Bonuses;

use App\GamesProviders\IGameWallet;
use App\GamesProviders\GamesManager;
use App\GamesProviders\GamesProvider;
use App\Helpers\Hlog;
use App\Helpers\UI;
use App\Jobs\ActualizeBonusJob;
use App\Models\Bonuses\Contracts\BonusRenderer;
use App\Models\Bonuses\Contracts\ExternalFsHandler;
use App\Models\Bonuses\Handlers\PragPlayFsHandler;
use App\Models\Bonuses\Handlers\SlotegratorFsHandler;
use App\Models\Bonuses\Handlers\TestFsHandler;
use App\Models\Bonuses\Params\BonusMultiDaysParam;
use App\Models\Bonuses\Render\MultiDaysBonusRenderer;
use App\Models\Bonuses\Segments\BonusSegment;
use App\Models\BonusStatistic;
use App\Models\BonusWin;
use App\Models\UserNotifications;
use App\MUser;
use App\Services\KBonus\KBonus;
use App\UserWallet;
use App\Wallet;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class MultiDaysBonus extends KUserBonus
{
    private ?BonusMultiDaysParam $_current_day_params = null;
    private int $all_spins = 0;


    public function currentDay(): int
    {
        return $this->tries;
    }

    private function setCurrentDay(int $day): void
    {
        $this->tries = $day;
    }

    public function getCurrentDayParams(bool $update = false): BonusMultiDaysParam
    {
        if ($this->_current_day_params === null || $update) {
            $this->_current_day_params = BonusMultiDaysParam::getForDay($this->currentDay());
            if ($this->_current_day_params === null) throw new \Exception("Can't get day params ".$this->currentDay());
        }
        return $this->_current_day_params;
    }

    public function getName(): string
    {
        return __('bonus.bonus_44_name');
    }

    public function getDescription(): string
    {
        switch ($this->status) {
            case self::STATUS_ACTIVE:
                return __('bonus.bonus_44_descr1');

            case self::STATUS_WAITING_FOR_PAYMENT:
                return __('bonus.bonus_44_descr2', ['amount' => $this->money_to_activate, 'curr' => $this->curr_name]);
        }

        return __('bonus.bonus_44_descr3');
    }

    public function getDescriptionName(): ?string
    {
        return 'multi-days-spins';
    }


    public function getStatusString(IGameWallet $game_wallet = null): string
    {
        if ($this->status != static::STATUS_ACTIVE) return '';
        if ($this->spins_available > 0) return '';
        return UI::bonusPad(__('bonus.status_active'), $this->getProgressHint());
    }

    public function getRenderer(): BonusRenderer
    {
        return new MultiDaysBonusRenderer($this);
    }

    public function daysTotal(): int
    {
        return $this->getJsonParam('day_e') - $this->getJsonParam('day_s') + 1;
    }

    public function getAllSpinsCount(): int
    {
        if ($this->all_spins === 0) {
            foreach (range($this->getJsonParam('day_s'), $this->getJsonParam('day_e')) as $day) {
                $this->all_spins += BonusMultiDaysParam::getForDay($day)->getSpins();
            }
        }
        return $this->all_spins;
    }

    public function afterCreated(): void
    {
        if (empty($this->id)) {
            Hlog::logException(new \Exception('Trying attach bonus with no id to segment'));
            return;
        }
        parent::afterCreated();

//        BonusSegment::attachOrCreateByName($this, BonusSegment::MULTIDAYS);
        KBonus::getInstance()->addBonusToSegmentByName($this->getId(), BonusSegment::MULTIDAYS);
    }

}
