<?php

namespace App\Models\Bonuses;

use App\CustomerIo\CioEventType;
use App\CustomerIo\CustomerIo;
use App\Helpers\Hlog;
use App\Helpers\Localization;
use App\Models\CashBackPlusLog;
use App\Models\MessageBlank;
use App\Models\Popups\UserPopup;
use App\Models\UserValue;
use App\MUser;
use App\PayoutTransaction;
use App\Services\Users\UserManager;
use App\UserWallet;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CashbackMoneyBonus extends KUserBonus
{

//    /**
//     * DepositBonus constructor.
//     * Sets type of the bonus.
//     * @param array $attributes
//     */
//    public function __construct(array $attributes = [])
//    {
//        parent::__construct($attributes);
//        $this->type = static::TYPE_CASHBACK_MONEY;
//    }


    public function getTypeName()
    {
        return __('bonus.bonus_40_name');
    }

    public function getTitle(): string
    {
        return __('bonus.bonus_40_name');
    }
//
//    public function getDescriptionName(): ?string
//    {
//        return 'cashback-money';
//    }

    /**
     * Creates cashback bonus for given user.
     * @param MUser $user
     * @param float $amount
     * @param int $currency
     * @return KUserBonus|null
     */
    public static function createForUser(MUser $user, float $amount, int $currency, int $days = 3): ?KUserBonus
    {
        $params['money'] = $amount;
        $params['days'] = $days;
        $attributes = [
            "type" => static::TYPE_CASHBACK_MONEY,
        ];
        $meta = [];
        return (new self($attributes, $meta))->createFromParams(json_encode($params), $user->id, $currency, true);
//        $bonus = new static();
//
//        $bonus->status = static::STATUS_NOT_ACTIVATED;
//        $bonus->active_to = now()->addDays(3)->format('Y-m-d H:i:s');
//        $bonus->user_id = $user->id;
//        $bonus->hall_id = $user->hall_id;
//        $bonus->currency = $user->currency;
//        $bonus->spins_available = -1;
//        $bonus->money_available = $amount;
//        $bonus->currency = $currency;
//
//        $bonus->save();
//
//        DB::table('payout_transactions')
//            ->insert([
//                'user_id' => $user->id,
//                'hall_id' => $user->hall_id,
//                'date' => date('Y-m-d H:i:s'),
//                'sum' => $bonus->money_available,
//                'status_id' => 35,
//                'currency' => UserWallet::currencyName($bonus->currency),
//                'operation' => 'CashBackMoney bonus created'
//            ]);
//
//        return $bonus;
    }

//    public function createFromParams(string $params, int $user_id, ?int $currency = null, ?bool $keep_params = false, ?bool $tmp = false): self
//    {
//        $jparams = json_decode($params, true);
//
//        /** @var MUser $user */
//        $user = null;
//        if ($user_id === 0 && !$tmp) {
//            throw new \Exception('User id is not specified for non temp bonus. Params: ' . $params);
//        }
//        if ($user_id !== 0) {
//            $user = MUser::find($user_id);
//        }
//        $params = $jparams;
//
//        if (isset($jparams['currency'])) {
//            $curr = Wallet::getCurrencyCode($jparams['currency']);
//        } else {
//            $curr = $currency ?? $user?->currency?? Wallet::CURRENCY_EUR;
//            if (!$keep_params) {
//                $params = $this->getCurrencyParams($curr, $jparams);
//            }
//        }
//        $hall_id = Wallet::currencyHallId($curr);
//
//        $bonus = new static();
//        $bonus->status = static::STATUS_NOT_ACTIVATED;
//        $bonus->active_to = now()->addDays($params['days'] ?? 3);
//        $bonus->user_id = $user_id;
//        $bonus->hall_id = $hall_id;
//        $bonus->currency = $curr;
//        $bonus->spins_available = -1;
//        $bonus->money_available = $params['money'] ?? 0;
//        $bonus->json_params = json_encode($params?? []);
//
//        if (!empty($params['enable_after'])) {
//            $bonus->enable_after = json_encode($params['enable_after']);
//        }
//
//        if (!$tmp) $bonus->save();
//        return $bonus;
//    }

    public function getDescription(): string
    {
        return __('bonus.bonus_40_descr1', ['amount' => number_format($this->money_available, 2), 'curr' => UserWallet::currencyName($this->currency)]);
    }

//    public function activate(): bool
//    {
//        /** @var MUser $user */
//        $user = MUser::find(Auth::id());
//        $user->getWallet($this->currency)->addMoney($this->money_available)->save();
//        $this->money_paid_out = $this->money_available;
//
//        DB::table('payout_transactions')
//            ->insert([
//                'user_id' => $user->id,
//                'hall_id' => $user->hall_id,
//                'date' => date('Y-m-d H:i:s'),
//                'sum' => $this->money_available,
//                'status_id' => 35,
//                'currency' => UserWallet::currencyName($this->currency),
//                'operation' => 'CashBack bonus payed in actualize'
//            ]);
//
//        $this->money_available = 0;
//        $this->status = self::STATUS_DONE;
//        $user->save();
//        $this->save();
//
//        BonusStatistic::saveStat($this, $this->user_id, BonusStatistic::ACTION_WIN, $this->money_paid_out, self::STATUS_PENDING, self::STATUS_DONE);
//
//        UserNotifications::notify($user->id, __('bonus.bonus_title') . $this->getTypeName(),
//            ' <span class="gold-text">' . UI::moneyStr($this->money_paid_out) .' '. $this->curr_name . '</span>'. __('bonus.transferred_to_balance'));
//
//
//        event(new MoneySpent($user, $this->money_paid_out * -1, UserWallet::currencyName($this->currency)), 'bonus');
//        return true;
//    }

//    public function actualize()
//    {
//        // Mark bonus as expired if user don't activated bonus until the specified time.
//        if (!empty($this->active_to) && strtotime($this->active_to) < time()) {
//            $this->status = self::STATUS_EXPIRED;
//            BonusStatistic::saveStat($this, $this->user_id, BonusStatistic::ACTION_EXPIRED, $this->money_available, $this->status, self::STATUS_EXPIRED);
//        }
//    }

    public function isProgressAvailable(): bool
    {
        return false;
    }

    public static function weeklyCashBack()
    {
        set_time_limit(1200);
        Log::info('processing weekly cashbacks');

        $s_date = now()->startOfDay()->subDays(7)->format('Y-m-d H:i:s');
        $e_date = now()->startOfDay()->subDay()->endOfDay()->format('Y-m-d H:i:s');
        Log::info('weeklyCashBack loses from  ' . $s_date . ' to ' . $e_date);

        foreach (UserWallet::activeCurrencies() as $currency) {

            $currency_name = UserWallet::currencyName($currency);
            Log::info('weeklyCashBack currency' . $currency);

            $losers = PayoutTransaction::select(
                DB::raw("`payout_transactions`.user_id,
                            SUM(`payout_transactions`.sum) as income,
                            COALESCE(outcome.total_payout, 0) as payout,
                            wallet.amount as balance")
            )
                ->where('date', '>=', $s_date)
                ->where('date', '<=', $e_date)
                ->where('currency', $currency_name)
                ->whereIn('status_id', PayoutTransaction::getIncomingStatuses())
                ->leftJoin(DB::raw("(select user_id, sum(sum) as total_payout
                                        from payout_transactions
                                        where status_id in (" . implode(", ", PayoutTransaction::getOutgoingStatuses()) . ")
                                        and date >= '$s_date'
                                        and date <= '$e_date'
                                        and currency = '$currency_name'
                                        group by user_id) as outcome"), function ($join) {
                    $join->on('outcome.user_id', '=', 'payout_transactions.user_id');
                })
                ->join(DB::raw("(select user_id, amount
                                    from user_wallets
                                    where currency = $currency
                                    ) as wallet"), function ($join) {
                    $join->on('wallet.user_id', '=', 'payout_transactions.user_id');
                })
                ->groupBy('payout_transactions.user_id')
                ->groupBy('outcome.total_payout')
                ->groupBy('wallet.amount')
                ->get();

            foreach ($losers as $loser) {
                Log::info('loser id#' . $loser->user_id);
                $credit = $loser->income - $loser->payout - $loser->balance;
                Log::info('loser income:' . $loser->income . ' payout:' . $loser->payout . ' balance:' . $loser->balance);
                Log::info('loser credit:' . $credit);
                if ($credit > 0) {
                    try {
                        /** @var MUser $user */
                        $user = MUser::find($loser->user_id);
                        if ($user) {
                            $cb_amount = floor($credit * ($user->getRank($currency)->getWeeklyCashBackPrc() / 100));
                            Log::info('loser cashback amount:' . $cb_amount);
                            if ($cb_amount >= 1) {
                                try {
//                                    $exists =  UserBonus::where('user_id', $user->id)
//                                        ->where('type', static::TYPE_CASHBACK_MONEY)
//                                        ->where('active_to', '>', now()->startOfDay()->format('Y-m-d 00:00:00'))
//                                        ->first();
//                                    if ($exists !== null) {
//                                        Log::info('cashback bonus already exists for user#' . $user->id);
//                                        continue;
//                                    }
                                    // add cashback+
                                    $cb_amount = $cb_amount + ($cb_amount * $user->getValue('cashback_plus', 0) / 100);
                                    $user->setValue('cashback_plus', 0);
                                    $bonus = self::createForUser($user, $cb_amount, $currency, 3);
//                                $bonus->save();
                                    /** @var MessageBlank $blank */
                                    $blank = MessageBlank::where('name', 'wed_bonus_pers')->first();
                                    if ($blank !== null) {
                                        $blank->sendTelegram([$user->id], [
                                            'amount' => number_format($cb_amount, 2, '.', ''),
                                            'curr' => UserWallet::currencyName($currency)
                                        ]);
                                        $blank->sendMail([$user->id], [
                                            'amount' => number_format($cb_amount, 2, '.', ''),
                                            'curr' => UserWallet::currencyName($currency)
                                        ]);
                                    }
                                    CustomerIo::getInstance()->sendEvent($user->getId(), CioEventType::Cashback, [
                                        'amount' => $cb_amount,
                                        'currency' => UserWallet::currencyName($currency),
                                    ]);
                                    Localization::setAppLocale($user->getLocale());
//                                UserPopup::create($user->getId(), view('common.popups.wcashback')->render(), 'wcashback', 2,
//                                    now(), $bonus->getExpirationDate());
                                    Log::info('cashback nondeposit bonus created for user#' . $user->id);
                                } catch (\Throwable $e) {
                                    Hlog::logException($e, 'mail');
                                }
                            }
                        }
                    } catch (\Exception $e) {
                        Hlog::logException($e);
                    }
                }
            }

            //reset all cashbacks
            UserManager::deleteAllUserKeyValue('cashback_plus');
//            /** @var UserValue $value */
//            foreach (UserValue::where('key', 'cashback_plus')->where('value', '!=', '0')->get() as $value) {
//                UserValue::setValue($value->user_id, 'cashback_plus', 0);
//                CashBackPlusLog::log($value->user_id, 0);
//            }

        }
    }

    public static function losersList()
    {
        set_time_limit(1200);
        Log::info('processing weekly cashbacks');

        $s_date = now()->subDays(7)->startOfWeek(Carbon::MONDAY)->format('Y-m-d H:i:s');
        $e_date = now()->subDays(7)->endOfWeek(Carbon::SUNDAY)->format('Y-m-d H:i:s');
        Log::info('weeklyCashBack loses from  ' . $s_date . ' to ' . $e_date);

        foreach (UserWallet::activeCurrencies() as $currency) {

            $currency_name = UserWallet::currencyName($currency);
            Log::info('weeklyCashBack currency' . $currency);

            $losers = PayoutTransaction::select(
                DB::raw("`payout_transactions`.user_id,
                            SUM(`payout_transactions`.sum) as income,
                            COALESCE(outcome.total_payout, 0) as payout,
                            wallet.amount as balance")
            )
                ->where('date', '>=', $s_date)
                ->where('date', '<=', $e_date)
                ->where('currency', $currency_name)
                ->where('payout_transactions.user_id',  358127)
                ->whereIn('status_id', PayoutTransaction::getIncomingStatuses())
                ->leftJoin(DB::raw("(select user_id, sum(sum) as total_payout
                                        from payout_transactions
                                        where status_id in (" . implode(", ", PayoutTransaction::getOutgoingStatuses()) . ")
                                        and date >= '$s_date'
                                        and date <= '$e_date'
                                        and currency = '$currency_name'
                                        group by user_id) as outcome"), function ($join) {
                    $join->on('outcome.user_id', '=', 'payout_transactions.user_id');
                })
                ->join(DB::raw("(select user_id, amount
                                    from user_wallets
                                    where currency = $currency
                                    ) as wallet"), function ($join) {
                    $join->on('wallet.user_id', '=', 'payout_transactions.user_id');
                })
                ->groupBy('payout_transactions.user_id')
                ->groupBy('outcome.total_payout')
                ->groupBy('wallet.amount')
                ->get();

            dump($losers->toArray());

            foreach ($losers as $loser) {
                dump('loser id#' . $loser->user_id);
                $credit = $loser->income - $loser->payout - $loser->balance;
                dump('loser income:' . $loser->income . ' payout:' . $loser->payout . ' balance:' . $loser->balance);
                dump('loser credit:' . $credit);
            }

        }
    }

//    public static function sendTodaysMails()
//    {
//        set_time_limit(1200);
//        $bonuses = self::where('type', static::TYPE_CASHBACK_MONEY)
//            ->where('created_at', '>=', now()->subDay()->format('Y-m-d 00:00:00'))
//            ->where('status', static::STATUS_NOT_ACTIVATED)
//            ->get();
//
//        $blank = MessageBlank::where('name', 'wed_bonus_pers')->first();
//        if ($blank === null) throw new RuntimeException('blank not found!');
//
//        echo("bonuses: ".$bonuses->count());
//
//        /** @var CashbackMoneyBonus $bonus */
//        foreach ($bonuses as $bonus) {
//            if ($blank !== null) {
//                echo("sending bonus tg to user $bonus->user_id currency $bonus->currency money $bonus->money_available");
//                $blank->sendTelegram([$bonus->user_id], [
//                    'amount' => number_format($bonus->money_available, 2, '.', ''),
//                    'curr' => UserWallet::currencyName($bonus->currency)
//                ]);
//                echo("sending bonus mail to user $bonus->user_id currency $bonus->currency money $bonus->money_available".PHP_EOL);
//                $blank->sendMail([$bonus->user_id], [
//                    'amount' => number_format($bonus->money_available, 2, '.', ''),
//                    'curr' => UserWallet::currencyName($bonus->currency)
//                ]);
//            }
//        }
//    }
}
