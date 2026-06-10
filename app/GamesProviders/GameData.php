<?php

namespace App\GamesProviders;

use App\Models\Bonuses\KUserBonus;
use App\Models\Bonuses\NonDepositBonus;
use App\Models\BonusWagerPrc;
use App\MUser;
use App\Wallet;
use Illuminate\Support\Facades\Log;

/**
 * Class GameData является DTO с информацией об игровой сессии для запуска игр
 * @property string $system
 * @property string $game_id
 * @property string $game_name
 * @property string $provider
 * @property int $currency
 * @property int $bonus_id
 * @property int $tnt_game_id
 * @property int $resident_tnt_game_id
 * @property int $cashback_bonus_id
 * @property int $non_dep_bonus_id
 * @property int $filthy_bonus_id
 * @property int $lose_bonus_id
 * @property float $locked_money
 * @property int $wager_prc
 */
class GameData
{
    public string $system = '';
    public string $game_id = '';
    public string $game_name = '';
    public string $provider = '';
    public int $currency = Wallet::CURRENCY_RUB;
    public int $bonus_id = 0;
    public int $tnt_game_id = 0;
    public int $resident_tnt_game_id = 0;
    public int $cashback_bonus_id = 0;
    public int $non_dep_bonus_id = 0;
    public int $filthy_bonus_id = 0;
    public int $lose_bonus_id = 0;
    public float $locked_money = 0.00;
    public int $wager_prc = 100;

    protected MUser $user;

    public function __construct(MUser $user)
    {
        $this->user = $user;
    }

    /**
     * save data to redis
     * @return GameData
     * @deprecated
     */
    public function save(): GameData
    {
        return $this;
    }

    public function toaArray(): array
    {
        $redis_data['system'] = $this->system;
        $redis_data['game_id'] = $this->game_id;
        $redis_data['game_name'] = $this->game_name;
        $redis_data['provider'] = $this->provider;
        $redis_data['currency'] = $this->currency;
        $redis_data['bonus_id'] = $this->bonus_id;
        $redis_data['tnt_game_id'] = $this->tnt_game_id;
        $redis_data['resident_tnt_game_id'] = $this->resident_tnt_game_id;
        $redis_data['cashback_bonus_id'] = $this->cashback_bonus_id;
        $redis_data['non_dep_bonus_id'] = $this->non_dep_bonus_id;
        $redis_data['filthy_bonus_id'] = $this->filthy_bonus_id;
        $redis_data['lose_bonus_id'] = $this->lose_bonus_id;
        $redis_data['locked_money'] = $this->locked_money;
        $redis_data['wager_prc'] = $this->wager_prc;

        return $redis_data;
    }

    /**
     * if no data for specified gamesystem, game_id, currency sets them
     * data should be set before game start
     * @param string $system
     * @param string $game_id
     * @param int $currency
     * @return GameData
     * @deprecated
     */
    public function getDataFormGame(string $system, string $game_id, int $currency): GameData
    {
        if ($this->system != $system || $this->game_id != $game_id || $this->currency != $currency) {
            Log::warning(__CLASS__ . " no data for game $system $game_id $currency, loading...");
            $this->loadDataForGame($system, $game_id, $currency);
        }
        return $this;
    }

    /**
     * loadDataForGame Загружает необходимую информацию для запуска игровой сессии
     * @param string $system
     * @param string $game_id
     * @param int $currency
     * @return GameData
     */
    public function loadDataForGame(string $system, string $game_id, int $currency): GameData
    {
        $this->user->refresh();
        $user = $this->user;

        $this->system = $system;
        $this->game_id = $game_id;
        $this->currency = $currency;

        Log::info(__CLASS__ . "user $user->id balance mode $user->balance_mode");

        // Процент отыгрыша вейджера
        $conf = BonusWagerPrc::where('system', $system)->where('game_id', $game_id)->first();
        $this->wager_prc = $conf? $conf->prc : 100;
//        $this->locked_money = $user->getWallet($currency)->getLockedMoney();

        // Текущий активный бонус
        if ($user->balance_mode === MUser::BALANCE_MODE_BONUS) {
//            $g_bonus = KUserBonus::getFirstActiveGameBonus($user->id, $system, $game_id, $currency);
//            if ($g_bonus !== null) {
//                Log::info(__CLASS__ . "user $user->id found game bonus $g_bonus->id");
//                $this->bonus_id = $g_bonus->id;
//            } else {
                /** @var KUserBonus $d_bonus */
                $d_bonus = KUserBonus::findFirstActiveBonus($user->id, $currency);
                if ($d_bonus && !$d_bonus->isGameAllowedByProvider($system, $game_id)) {
                    Log::info(__CLASS__ . "user $user->id found bonus $d_bonus->id not allowed!");
                    $d_bonus = null;
                }
                if ($d_bonus) {
                    Log::info(__CLASS__ . "user $user->id found bonus $d_bonus->id");
                    $this->bonus_id = $d_bonus->id;
                } else {
                    Log::info(__CLASS__ . "user $user->id bonus not found $system $game_id $currency");
                }
//            }
        }

        if ($user->balance_mode == MUser::BALANCE_MODE_MAIN) {
            //set cashback bonus
//            /** @var CashbackEventBonus $cashback_bonus */
//            $cashback_bonus = CashbackEventBonus::where('user_id', $user->id)
//                ->where('hall_id', $user->hall_id)
//                ->where('type', UserBonus::TYPE_EVENT_CASH_BACK)
//                ->where('status', UserBonus::STATUS_PENDING)->first();
//            $this->cashback_bonus_id = $cashback_bonus ? $cashback_bonus->id : 0;
            $this->cashback_bonus_id =0;

            //non deposit bonus
            /** @var NonDepositBonus $nd_bonus */
            $nd_bonus = NonDepositBonus::getDone($user->id);
            $this->non_dep_bonus_id = $nd_bonus ? $nd_bonus->id : 0;

//            /** @var FilthyFsWashOffBonus $nd_bonus */
//            $nd_bonus = FilthyFsWashOffBonus::getDone($user->id);
//            $this->filthy_bonus_id = $nd_bonus ? $nd_bonus->id : 0;

            //lose bonus
//            $lose_bonus = UserBonus::getWaitingLose($user);
//            $this->lose_bonus_id = $lose_bonus ? $lose_bonus->id : 0;
            $this->lose_bonus_id = 0;
        }

//        $this->save();
        return $this;
    }
}
