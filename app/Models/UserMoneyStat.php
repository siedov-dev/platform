<?php

namespace App\Models;

use App\MUser;
use App\PayoutTransaction;
use App\Services\KPay\KPay;
use App\UserWallet;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

/**
 * Статистические платежные данные для ускорения выборки.
 *
 * @property int $id
 * @property int $user_id
 * @property int $currency_id
 * @property string $type
 * @property int $deposit_count
 * @property float $deposit_sum
 * @property ?Carbon $first_dep
 * @property ?Carbon $last_dep
 * @property int $payout_count
 * @property float $payout_sum
 * @property ?Carbon $first_payout
 * @property ?Carbon $last_payout
// * @property MUser $user
 */
class UserMoneyStat
{
    public int $id;
    public int $user_id;
    public int $currency_id;
    public string $type;
    public int $deposit_count;
    public float $deposit_sum;
    public ?Carbon $first_dep;
    public ?Carbon $last_dep;
    public int $payout_count;
    public float $payout_sum;
    public ?Carbon $first_payout;
    public ?Carbon $last_payout;

    public function __construct(array $attributes)
    {
        $this->id = $attributes['id'] ?? 0;
        $this->user_id = $attributes['user_id'] ?? 0;
        $this->currency_id = $attributes['currency_id'] ?? 0;
        $this->type = $attributes['type'] ?? '';
        $this->deposit_count = $attributes['deposit_count'] ?? 0;
        $this->deposit_sum = $attributes['deposit_sum'] ?? 0;
        $this->first_dep = $attributes['first_dep'] ?? null;
        $this->last_dep = $attributes['last_dep'] ?? null;
        $this->payout_count = $attributes['payout_count'] ?? 0;
        $this->payout_sum = $attributes['payout_sum'] ?? 0;
        $this->first_payout = $attributes['first_payout'] ?? null;
        $this->last_payout = $attributes['last_payout'] ?? null;
    }

//    use HasFactory;

    const TYPE_TOTAL = 'total'; // общие данные
    const TYPE_AIFORY = "aifory"; // данные платежной системы aifory
    const TYPE_PAYADMIT_MOBILE = "padmit_mobile"; // данные платежной системы PayAdmit (mobile)
    const TYPE_PAYADMIT = "padmit"; // данные платежной системы PayAdmit (mobile)

//    protected $guarded = ['id'];

//    private static int $user_id = 0;
//    private static int $currency_id = 0;
    private static ?UserMoneyStat $totals = null;

    /**
     * Возвращает данные
     * @param MUser $user пользователь
     * @param int $currency_id ид валюты
     * @param string $type  тип данных
     * @param bool $update обновить данные
     * @return UserMoneyStat|null
     */
    public static function getStat(MUser $user, int $currency_id, string $type, bool $update = false): ?UserMoneyStat
    {
        return KPay::getInstance()->getUserMoneyStats($user->getId(), $type, $currency_id, $update);
//        if ($type === self::TYPE_TOTAL && self::$user_id === $user->getId() && self::$currency_id === $currency_id) {
//            $stat = self::$totals;
//        } else {
//            $stat = UserMoneyStat::where('user_id', $user->getId())
//                ->where('currency_id', $currency_id)
//                ->where('type', $type)
//                ->first();
//        }
//
//        if ($type === self::TYPE_TOTAL) self::$totals = $stat;
//
//        if ($stat !== null && !$update) return $stat;
//
//        if ($stat === null) {
//            $stat = new UserMoneyStat();
//            $stat->setUserId($user->getId());
//            $stat->setCurrencyId($currency_id);
//            $stat->setType($type);
//        }
//
//        if ($type === self::TYPE_TOTAL) {
//            $deps = PayoutTransaction::selectRaw('user_id, sum(`sum`) as sum, count(id) as cnt, max(`date`) as last_dep, min(`date`) as first_dep')
//                ->where('user_id', $user->getId())
//                ->whereIn('status_id', PayoutTransaction::getIncomingStatuses())
//                ->where('currency', UserWallet::currencyName($currency_id))
//                ->groupBy('user_id')
//                ->first();
//
//            $stat->setDepositCount($deps?->cnt?? 0);
//            $stat->setDepositSum($deps?->sum?? 0);
//            $stat->first_dep = $deps?->first_dep;
//            $stat->last_dep =  $deps?->last_dep;
//
//            $payouts = PayoutTransaction::selectRaw('user_id, sum(`sum`) as sum, count(id) as cnt, max(`date`) as last_pay, min(`date`) as first_pay')
//                ->where('user_id', $user->getId())
//                ->whereIn('status_id', PayoutTransaction::getOutgoingStatuses())
//                ->where('currency', UserWallet::currencyName($currency_id))
//                ->groupBy('user_id')
//                ->first();
//
//            $stat->setPayoutCount($payouts?->cnt?? 0);
//            $stat->setPayoutSum($payouts?->sum?? 0);
//            $stat->first_payout = $payouts?->first_pay;
//            $stat->last_payout = $payouts?->last_pay;
//            $stat->save();
//
//            self::$user_id = $user->getId();
//            self::$currency_id = $currency_id;
//            self::$totals = $stat;
//            return $stat;
//        }
//        return null;
    }

    public function addDeposit(float $amount): ?UserMoneyStat
    {
        return KPay::getInstance()->userMoneyStatsAddStat($this->user_id, $this->type, $this->currency_id, $amount, "deposit");
    }

    public function addPayout(float $amount): ?UserMoneyStat
    {
        return KPay::getInstance()->userMoneyStatsAddStat($this->user_id, $this->type, $this->currency_id, $amount, "payout");
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function getUserId(): int
    {
        return $this->user_id;
    }

    public function setUserId(int $user_id): void
    {
        $this->user_id = $user_id;
    }

    public function getCurrencyId(): int
    {
        return $this->currency_id;
    }

    public function setCurrencyId(int $currency_id): void
    {
        $this->currency_id = $currency_id;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type): void
    {
        $this->type = $type;
    }

    public function getDepositCount(): int
    {
        return $this->deposit_count?? 0;
    }

    public function setDepositCount(int $deposit_count): void
    {
        $this->deposit_count = $deposit_count;
    }

    public function getDepositSum(): float
    {
        return $this->deposit_sum?? 0;
    }

    public function setDepositSum(float $deposit_sum): void
    {
        $this->deposit_sum = $deposit_sum;
    }

    public function getFirstDep(): ?Carbon
    {
        return $this->first_dep;
    }

    public function setFirstDep(?Carbon $first_dep): void
    {
        $this->first_dep = $first_dep;
    }

    public function getLastDep(): ?Carbon
    {
        return $this->last_dep;
    }

    public function setLastDep(?Carbon $last_dep): void
    {
        $this->last_dep = $last_dep;
    }

    public function getPayoutCount(): int
    {
        return $this->payout_count?? 0;
    }

    public function setPayoutCount(int $payout_count): void
    {
        $this->payout_count = $payout_count;
    }

    public function getPayoutSum(): float
    {
        return $this->payout_sum?? 0;
    }

    public function setPayoutSum(float $payout_sum): void
    {
        $this->payout_sum = $payout_sum;
    }

    public function getFirstPayout(): ?Carbon
    {
        return $this->first_payout;
    }

    public function setFirstPayout(?Carbon $first_payout): void
    {
        $this->first_payout = $first_payout;
    }

    public function getLastPayout(): ?Carbon
    {
        return $this->last_payout;
    }

    public function setLastPayout(?Carbon $last_payout): void
    {
        $this->last_payout = $last_payout;
    }

//    public function user(): HasOne
//    {
//        return $this->hasOne(MUser::class, 'id', 'user_id');
//    }

    public static function syncTotalData(): void
    {

        $c = 0;
        MUser::select('id')->chunk(1000, function ($u) use (&$c) {
            Log::info("syncTotalData users processing chunk " . $c * 1000);
            $ids = $u->pluck('id')->toArray();
            $query = PayoutTransaction::selectRaw('user_id, currency, sum(`sum`) as sum, count(id) as cnt, max(`date`) as last_dep, min(`date`) as first_dep')
                ->whereIn('user_id', $ids)
                ->whereIn('status_id', PayoutTransaction::getIncomingStatuses())
                ->groupBy('user_id')
                ->groupBy('currency');
            $deps = $query->get();


            foreach ($deps as $dep) {
                if (empty($dep->currency)) continue;
                $stat = UserMoneyStat::where('user_id', $dep->user_id)
                    ->where('currency_id', UserWallet::getCurrencyCode($dep->currency))
                    ->where('type', self::TYPE_TOTAL)
                    ->first();

                if ($stat === null) {
                    $stat = new UserMoneyStat();
                    $stat->setUserId($dep['user_id']);
                    $stat->setCurrencyId(UserWallet::getCurrencyCode($dep['currency']));
                    $stat->setType(self::TYPE_TOTAL);
                    $stat->save();
                }

                $stat->setDepositCount($dep?->cnt?? 0);
                $stat->setDepositSum($dep?->sum?? 0);
                $stat->first_dep = $dep?->first_dep;
                $stat->last_dep =  $dep?->last_dep;
                $stat->save();
            }

            $query = PayoutTransaction::selectRaw('user_id, currency, sum(`sum`) as sum, count(id) as cnt, max(`date`) as last_dep, min(`date`) as first_dep')
                ->whereIn('user_id', $ids)
                ->whereIn('status_id', PayoutTransaction::getOutgoingStatuses())
                ->groupBy('user_id')
                ->groupBy('currency');
            $payouts = $query->get();

            foreach ($payouts as $payout) {
                if (empty($payout->currency)) continue;

                $stat = UserMoneyStat::where('user_id', $payout->user_id)
                    ->where('currency_id', UserWallet::getCurrencyCode($payout->currency))
                    ->where('type', self::TYPE_TOTAL)
                    ->first();

                if ($stat === null) {
                    $stat = new UserMoneyStat();
                    $stat->setUserId($payout['user_id']);
                    $stat->setCurrencyId(UserWallet::getCurrencyCode($payout['currency']));
                    $stat->setType(self::TYPE_TOTAL);
                    $stat->save();
                }

                $stat->setPayoutCount($payout?->cnt?? 0);
                $stat->setPayoutSum($payout?->sum?? 0);
                $stat->first_payout = $payout?->first_pay;
                $stat->last_payout = $payout?->last_pay;
                $stat->save();
            }
        });
    }
}
