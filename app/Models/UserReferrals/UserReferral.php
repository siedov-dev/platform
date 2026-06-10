<?php

namespace App\Models\UserReferrals;

use App\Events\MoneySpent;
use App\Helpers\Currency;
use App\MUser;
use App\UserWallet;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * Class UserReferral
 * @package App\Models
 * @property int $id
 * @property int $user_id
 * @property string $ref_code
 * @property string $bonus_ref_code
 * @property int $profit_prc
 * @property MUser $user
 */
class UserReferral extends Model
{
    const REGISTER_BONUS_MONEY = 10;
    const DEFAULT_PROFIT_PRC = 15;
    //
    protected $guarded = [];

    public function user()
    {
        return $this->belongsTo(MUser::class, 'user_id', 'id');
    }

    public function getRefCode(): string
    {
        if (empty($this->ref_code)) {
            $this->ref_code = Str::random(10);
            $this->save();
        }
        return $this->ref_code;
    }

    public function getBonusRefCode(): string
    {
        if (empty($this->bonus_ref_code)) {
            $this->bonus_ref_code = Str::random(10);
            $this->save();
        }
        return $this->bonus_ref_code;
    }

    /**
     * creates spent event with profit amount
     * that is paid monthly
     *
     * @param MoneySpent $event
     */
    public function onMoneySpent(MoneySpent $event)
    {
        if ($event->user === null || empty($event->user->ref_id) || $event->amount == 0) return;

        $profit = $event->amount * $this->profit_prc / 100;

        Log::info('uref money spent user#'.$event->user->id.' amount:'.$event->amount.' profit:'.$profit);

        /** @var UserReferral $user_ref */
        $user_ref = UserReferral::find($event->user->ref_id);

        if ($user_ref === null) {
            Log::info('can`t find user_referall '.$event->user->ref_id);
            return;
        }

        if ($user_ref->user === null) {
            Log::info('can`t find referall`s user ');
            return;
        }

        if (!$user_ref->user->hasActiveCurrency(UserWallet::getCurrencyCode($event->currency))) {
            Log::info('user doesn`t have active currency '.$event->currency);
            return;
        }

        $r_event = new ReferralEvents(['type' => ReferralEvents::TYPE_MONEY_SPENT]);
        $r_event->ref_id = $this->id;
        $r_event->amount = $event->amount;
        $r_event->profit = $profit;
        $r_event->currency = UserWallet::getCurrencyCode($event->currency);
        $r_event->user_id = $event->user->id;
        $r_event->save();

        //once per month made in artisan command "referrals:process"
//        $this->user->getWallet(UserWallet::getCurrencyCode($event->currency))->addReferralMoney($profit);
    }

    /**
     * creates reg event
     * and pays reward on bonus wallet
     *
     * @param MUser $reged_user
     */
    public function onRegister(MUser $reged_user)
    {
        Log::info('uref register user#'.$reged_user->id);

        $r_event = new ReferralEvents(['type' => ReferralEvents::TYPE_REGISTER]);
        $r_event->ref_id = $this->id;
        $r_event->user_id = $reged_user->id;
        $r_event->save();

//        if ($this->user === null) {
//            Log::info('can`t find referall`s user ');
//            return;
//        }
//
//        if (!$this->user->hasActiveCurrency($reged_user->currency)) {
//            Log::info('user doesn`t have active currency '.$reged_user->currency);
//            return;
//        }
//
//        $money_k = Currency::getMoneyCoef($reged_user->currency);
//        $reward = self::REGISTER_BONUS_MONEY * $money_k;
//
//        $r_event->profit = $reward;
//        $r_event->currency = $reged_user->currency;
//        $r_event->save();
//        $this->user->getWallet($reged_user->currency)->addBonusMoney($reward);
    }

    public function onDeposit(MUser $user)
    {
        Log::info('uref deposit user#'.$user->id);

        $deps_total = $user->getDepositsCount();

        if ($deps_total === 1) {
            $r_event = new ReferralEvents(['type' => ReferralEvents::TYPE_FIRST_DEPOSIT]);
            $r_event->ref_id = $this->id;
            $r_event->user_id = $user->id;
            $r_event->save();

            if ($this->user === null) {
                Log::info('can`t find referall`s user ');
                return;
            }

            if (!$this->user->hasActiveCurrency($user->currency)) {
                Log::info('user doesn`t have active currency '.$user->currency);
                return;
            }

            $money_k = Currency::getMoneyCoef($user->currency);
            $reward = self::REGISTER_BONUS_MONEY * $money_k;

            $r_event->profit = $reward;
            $r_event->currency = $user->currency;
            $r_event->save();
            $this->user->getWallet($user->currency)->addBonusMoney($reward);
        }

        $r_event = new ReferralEvents(['type' => ReferralEvents::TYPE_DEPOSIT]);
        $r_event->ref_id = $this->id;
        $r_event->user_id = $user->id;
        $r_event->save();

    }
}
