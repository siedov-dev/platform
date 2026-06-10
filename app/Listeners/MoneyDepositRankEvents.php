<?php

namespace App\Listeners;

use App\Events\MoneyDeposit;
use App\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class MoneyDepositRankEvents
{
    const CASHBACK_DAY = Carbon::MONDAY;
    const FFS_BONUS_DAY = Carbon::TUESDAY;
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  MoneyDeposit  $event
     * @return void
     */
    public function handle(MoneyDeposit $event)
    {
        try {

            if ($event->user === null || $event->amount == 0) return;

//            if (now()->dayOfWeek == self::CASHBACK_DAY) {
//                $user_rank = $event->user->getRank($event->currency);
//                $amount = floor($event->amount * ($user_rank->getWeeklyCashBackPrc() / 100));
//                if ($amount > 0) {
//                    $cb_bonus = new CashbackEventBonus();
//                    CashbackEventBonus::cancelCashBackBonuses($event->user);
//                    $params = [
//                        'money' => $event->amount * ($user_rank->getWeeklyCashBackPrc() / 100),
//                        'currency' => UserWallet::currencyName($event->currency),
//                    ];
//                    $bonus = $cb_bonus->createFromParams(json_encode($params), $event->user->id);
//                    Log::info(var_export($bonus, true));
//                }
//            }

//            if (now()->dayOfWeek == self::FFS_BONUS_DAY && UserDailyStat::getNumericValue($event->user->id, 'event-FFS_BONUS_DAY') == 0) {
//                $user_rank = $event->user->getRank($event->currency);
//
//                if ($user_rank->getDepositFrom() > 0 && $event->amount >= $user_rank->getDepositFrom()) {
//                    $fs_bonus = new FilthyFreeSpinsBonus();
//                    $params = [
//                        'freespins' => $user_rank->getSpins(),
//                        'money' => $user_rank->getBetLimit(),
//                        'win_limit' => $user_rank->getWinLimit(),
//                        'wager' => $user_rank->getWager(),
//                        'lifetime' => 48,
//                        'currency' => UserWallet::currencyName($event->currency),
//                    ];
//                    $bonus = $fs_bonus->createFromParams(json_encode($params), $event->user->id);
//
//                    if ($user_rank->getCashBack() > 0) {
//                        $amount = floor($event->amount * ($user_rank->getCashBack() / 100));
//                        if ($amount > 0) {
//                            $cb_bonus = new CashbackEventBonus();
//                            CashbackEventBonus::cancelCashBackBonuses($event->user);
//                            $params = [
//                                'money' => $event->amount * ($user_rank->getCashBack() / 100),
//                                'currency' => UserWallet::currencyName($event->currency),
//                            ];
//                            $bonus = $cb_bonus->createFromParams(json_encode($params), $event->user->id);
//                        }
//                    }
//                    UserDailyStat::setValue($event->user->id, 'event-FFS_BONUS_DAY', 1);
//                }
//            }
        } catch (\Exception $e){
            Log::error($e->getMessage());
        }

    }
}
