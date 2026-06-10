<?php


namespace App\Models\Bonuses;

/**
 * @deprecated
 */
class BezdepBonus extends WagerFreeSpinsBonus
{
//    use FsWagerActualize;
//    /**
//     * BezdepBonus constructor.
//     * @param array $attributes
//     */
//    public function __construct(array $attributes = [])
//    {
//        parent::__construct($attributes);
//        $this->type = static::TYPE_BEZDEP;
//    }
//
//    public function getName(): string
//    {
//        return __('bonus.bonus_17_name');
//    }
//
//    public function getDescription(): string
//    {
//        if ($this->status == static::STATUS_NOT_ACTIVATED) {
////            return "После активации, пополнитесь на сумму от ".number_format($this->money_to_activate, 2)." $this->curr_name и в случае проигрыша вам будет активирован бездепозитный бонус.";
//            return __('bonus.bonus_17_descr1', ['amount' => number_format($this->money_to_activate, 2), 'curr' => $this->curr_name]);
//        }
//        if ($this->status == static::STATUS_WAITING_FOR_PAYMENT) {
////            return "Пополнитесь на сумму от ".number_format($this->money_to_activate, 2)." $this->curr_name. чтобы активировать кешбек бонус, который будет активирован в случае проигрыша.";
//            return __('bonus.bonus_17_descr2', ['amount' => number_format($this->money_to_activate, 2), 'curr' => $this->curr_name]);
//        }
//        if ($this->status == static::STATUS_WAITING_FOR_LOSE) {
////            return "Активен. В случае проигрыша будет активирован бездепозитный бонус. При выводе денег бонус будет анулирован.";
//            return __('bonus.bonus_17_descr3');
//        }
//        return __('bonus.bonus_17_descr4');
//    }
//
//    public function getDescriptionName(): ?string
//    {
//        return 'non-deposit';
//    }
//
//
//    public function activate(): bool
//    {
//        if ($this->status == self::STATUS_NOT_ACTIVATED) {
//
//            $this->money_to_activate = $this->money_available;
//            $this->status = self::STATUS_WAITING_FOR_PAYMENT;
//
//            $dt = new \DateTime();
//
//            if ($this->hours_active) {
//                $dt->add(new \DateInterval('PT' . $this->hours_active . 'H'));
//                $this->active_to = $dt->format('Y-m-d H:i:s');
//            }
//            $this->save();
//
//            DB::table('payout_transactions')
//                ->insert([
//                    'user_id' => Auth::id(),
//                    'hall_id' => UserWallet::currencyHallId($this->currency),
//                    'date' => date('Y-m-d H:i:s'),
//                    'sum' => $this->money_available,
//                    'status_id' => PayoutTransaction::STATUS_BONUS_ACTIVATED,
//                    'currency' => $this->curr_name,
//                    'operation' => $this->getName() . ' drummatik bonus activated'
//                ]);
//
//            BonusStatistic::saveStat($this, $this->user_id, BonusStatistic::ACTION_ACTIVATE,
//                $this->money_to_activate, self::STATUS_NOT_ACTIVATED, self::STATUS_WAITING_FOR_PAYMENT);
//
//            return true;
//        }
//
//        return false;
//    }
//
//    public static function createForUser(MUser $user, int $spins = -1, float $money = 1000, int $wager = 70)
//    {
//
//        $jparams['money'] = $money;
//        $jparams['wager'] = $wager;
//
//        $bonus = new static();
//
//        $params = $bonus->getCurrencyParams($user->currency, $jparams);
//
//        $money = $params['money'];
//        $wager = $params['wager'];
//
//        $bonus->user_id = $user->id;
//        $bonus->hall_id = $user->hall_id;
//        $bonus->currency = $user->currency;
//        $bonus->spins_available = -1;
//        $bonus->spins_total = 0;
//        $bonus->status = static::STATUS_NOT_ACTIVATED;
//        $bonus->money_available = $money;
//        $bonus->money_to_activate = $money;
//        $bonus->wager_to_complete = $money * $wager;
//        $bonus->wager_to_payout = $money;
//        $bonus->hours_active = 24;
//        $bonus->multiplier = $wager;
//        $bonus->max_win = $money * 5;
//        $bonus->active_to = now()->addHours(48)->format('Y-m-d H:i:s');
//        $bonus->save();
//
////        BonusStatistic::saveStat($bonus, $bonus->user_id, BonusStatistic::ACTION_CREATED,
////            $bonus->money_available, self::STATUS_NOT_ACTIVATED, $bonus->status, $bonus->pattern_id, $params);
//
//        $user->setRedisValue('has_new_bonus', true);
//        return $bonus;
//    }
//
//    public function wagerDiminution($percent)
//    {
//        $prc = 1 - ($percent / 100);
//        $this->multiplier = floor($this->multiplier * $prc);
//        $this->wager_to_complete = $this->money_to_activate * $this->multiplier;
//        $this->save();
//        return $this;
//    }
////
////    public function shouldProcessPayment(): bool
////    {
////        return $this->status == static::STATUS_WAITING_FOR_PAYMENT;
////    }
//
//    public function confirmPayment(MUser $user, float $amount, int $currency): float
//    {
//        $this->status = static::STATUS_WAITING_FOR_LOSE;
//        $this->money_to_activate = 0;
//        $this->save();
//
//        DB::table('payout_transactions')
//            ->insert([
//                'user_id' => $user->id,
//                'hall_id' => $user->hall_id,
//                'date' => date('Y-m-d H:i:s'),
//                'sum' => $amount,
//                'status_id' => 35,
//                'currency' => $this->curr_name,
//                'operation' => 'Бездепозитный бонус оплачено'
//            ]);
//
//        BonusStatistic::saveStat($this, $this->user_id, BonusStatistic::ACTION_DEPOSIT, $amount,
//            self::STATUS_WAITING_FOR_PAYMENT, self::STATUS_WAITING_FOR_LOSE);
//
//        return $amount;
//    }
//
//    public function createFromParams(string $params, int $user_id, ?int $currency = null, ?bool $keep_params = false, ?bool $tmp = false): BezdepBonus
//    {
//        $jparams = json_decode($params, true);
//        /** @var MUser $user */
//        $user = MUser::find($user_id);
//        $params = $jparams;
//
//        if (isset($jparams['currency'])) {
//            $curr = UserWallet::getCurrencyCode($jparams['currency']);
//        } else {
//            $curr = $currency ?? $user->currency;
//            if (!$keep_params) {
//                $params = $this->getCurrencyParams($curr, $jparams);
//            }
//        }
//        $hall_id = UserWallet::currencyHallId($curr);
//
//        $money = $params['money'];
//        $wager = $params['wager'];
//
//        $bonus = new static();
//        $bonus->user_id = $user->id;
//        $bonus->hall_id = $hall_id;
//        $bonus->currency = $user->currency;
//        $bonus->spins_available = -1;
//        $bonus->spins_total = 0;
//        $bonus->status = static::STATUS_NOT_ACTIVATED;
//        $bonus->money_available = $money;
//        $bonus->money_to_activate = $money;
//        $bonus->wager_to_complete = $money * $wager;
//        $bonus->wager_to_payout = $money;
//        $bonus->hours_active = 24;
//        $bonus->multiplier = $wager;
//        $bonus->max_win = $money * 5;
//        $bonus->active_to = now()->addHours(48)->format('Y-m-d H:i:s');
//        $bonus->json_params = json_encode($params?? []);
//
//        if (!empty($params['enable_after'])) {
//            $bonus->enable_after = json_encode($params['enable_after']);
//        }
//
//        if (!$tmp) $bonus->save();
//        return $bonus;
//    }
//
//    private function writeTransaction($amount)
//    {
//
//        $user = MUser::find($this->user_id);
//
//        DB::table('payout_transactions')
//            ->insert([
//                'user_id' => $user->id,
//                'hall_id' => $user->hall_id,
//                'date' => date('Y-m-d H:i:s'),
//                'sum' => $amount,
//                'status_id' => 35,
//                'currency' => $this->curr_name,
//                'operation' => 'Выигрыш на бонусе бездепозит (id = ' . $this->id . ')'
//            ]);
//    }
//
//    public function onUserLose()
//    {
//        BonusStatistic::saveStat($this, $this->user_id, BonusStatistic::ACTION_ACTIVATE, $this->money_available,
//            $this->status, self::STATUS_ACTIVE, $this->pattern_id, ['activated_on_loose' => true]);
//        $this->status = static::STATUS_ACTIVE;
//        $this->save();
//    }

}
