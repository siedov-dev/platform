<?php

namespace App\Models\Bonuses;

/**
 * @deprecated
 */
class SmartWagerBonus extends KUserBonus
{
//    use FsWagerActualize;
//    /**
//     * SmartWagerBonus constructor.
//     * @param array $attributes
//     */
//    public function __construct(array $attributes = [])
//    {
//        parent::__construct($attributes);
//        $this->type = static::TYPE_SMART_WAGER;
//    }
//
//    protected $guarded = ['type'];
//
//    public static function getSetting($setting, $default)
//    {
//        return BonusSettings::getProperty(static::TYPE_SMART_WAGER, $setting, $default);
//    }
//
//    public function isVegasAllowed(): bool
//    {
//        return true;
//    }
//
//    public function activate(): bool
//    {
//        if ($this->status == self::STATUS_WAITING_FOR_AGREEMENT || $this->status == self::STATUS_NOT_ACTIVATED) {
//            $this->status = self::STATUS_WAITING_FOR_PAYMENT;
//        }
//
//        \Illuminate\Support\Facades\DB::table('payout_transactions')
//            ->insert([
//                'user_id' => Auth::id(),
//                'hall_id' => UserWallet::currencyHallId($this->currency),
//                'date' => date('Y-m-d H:i:s'),
//                'sum' => $this->money_available,
//                'status_id' => 35,
//                'currency' => UserWallet::currencyName($this->currency),
//                'operation' => $this->getName() . ' drummatik bonus activated'
//            ]);
//
//        $this->save();
//
//        BonusStatistic::saveStat($this, $this->user_id, BonusStatistic::ACTION_ACTIVATE, $this->money_available,
//            self::STATUS_WAITING_FOR_AGREEMENT, $this->status, $this->pattern_id);
//
//        return true;
//    }
//
//    public function confirmPayment(MUser $user, float $amount, int $currency): float
//    {
//        $rest = $amount;
//        $start_amount = $amount;
//        //if refill sum is bigger then bonus max limit
//        if ($amount > $this->multiplier_to_complete) {
////            $rest = $amount - $this->multiplier_to_complete;
//            $amount = $this->multiplier_to_complete;
//        }
//
//        if ($this->isPaymentRequired()) {
////            $this->max_win =  ($amount * 5) > $this->max_win ? $this->max_win : ($amount * 5);
//            $this->money_available += $amount * $this->multiplier_to_payout / 100;
//            $this->wager_to_complete = ($amount + $this->money_available) * $this->multiplier;
//            $this->status = static::STATUS_ACTIVE;
//
//            $this->save();
//
////            $user->getWallet($this->currency)->addLockedMoney($amount);
//
////            $user->notify($this->getName(), __('bonus.bonus_money_locked')
////                . $user->getWallet($this->currency)->getLockedMoney() . ' ' . $user->getWallet($this->currency)->getCurrencyName());
//
//            DB::table('payout_transactions')
//                ->insert([
//                    'user_id' => $user->id,
//                    'hall_id' => $user->hall_id,
//                    'date' => date('Y-m-d H:i:s'),
//                    'sum' => $amount,
//                    'status_id' => 35,
//                    'currency' => UserWallet::currencyName($this->currency),
//                    'operation' => 'deposit prize bonus payed'
//                ]);
//
//            BonusStatistic::saveStat($this, $this->user_id, BonusStatistic::ACTION_DEPOSIT, $amount,
//                self::STATUS_WAITING_FOR_PAYMENT, $this->status, $this->pattern_id);
//
//            $this->onActivate();
//
//            return $rest;
//        }
//
//        Log::error('DepositPrizeBonus error confirm payment');
//
//        return $start_amount;
//
//    }
//
//    public static function createForUser($user, $wager = 40, $payment_multiplier = 150)
//    {
//        $jparams['wager'] = $wager;
//        $jparams['min_sum'] = 500;
//        $jparams['max_sum'] = 10000;
//
//        $bonus = new static();
//
//        $params = $bonus->getCurrencyParams($user->currency, $jparams);
//        $wager = $params['wager'];
//
//        $bonus->status = static::STATUS_WAITING_FOR_AGREEMENT;
//        $bonus->user_id = $user->id;
//        $bonus->hall_id = $user->hall_id;
//        $bonus->currency = $user->currency;
//        $bonus->spins_available = -1;
//        //money should be set after pay in
//        $bonus->money_available = 0;
//        //minimal refill sum
//        $bonus->money_to_activate = $params['min_sum'];
//        //max refill sum
//        $bonus->multiplier_to_complete = $params['max_sum'];
//        //wager koef
//        $bonus->multiplier = $wager;
//        //money multiplier in %
//        $bonus->multiplier_to_payout = $payment_multiplier;
//        $bonus->wager_to_payout = 0;
//        $bonus->active_to = now()->addDays(90)->format('Y-m-d H:i:s');
//        $bonus->save();
//
//        return $bonus;
//    }
//
//    public function wagerDiminution($percent)
//    {
//        $prc = 1 - ($percent / 100);
//        $this->multiplier = floor($this->multiplier * $prc);
//        $this->save();
//        return $this;
//    }
//
//    public function createFromParams(string $params, int $user_id, ?int $currency = null, ?bool $keep_params = false, ?bool $tmp = false): self
//    {
//        $jparams = json_decode($params, true);
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
//        $bonus->status = $params['status'] ?? static::STATUS_WAITING_FOR_AGREEMENT;
//        $bonus->user_id = $user_id;
//        $bonus->hall_id = $hall_id;
//        $bonus->currency = $curr;
//        $bonus->spins_available = -1;
//        //money should be set after pay in
//        $bonus->money_available = $params['money']?? 0;
//        //minimal refill sum
//        $bonus->money_to_activate = $params['min_sum'];
//        //max refill sum
//        $bonus->multiplier_to_complete = $params['max_sum'];
//        //wager koef
//        $bonus->multiplier = $params['wager'];
//        $bonus->max_win = $params['max_win'] ?? 0;
//        //money multiplier in %
//        $bonus->multiplier_to_payout = $params['multiplier'];
//        $bonus->wager_to_payout = 0;
//        $bonus->active_to = now()->addDays($params['lifetime'] ?? 90)->format('Y-m-d H:i:s');
//
//        if (!empty($params['bonus_prizes'])) {
//            $bonus->bonus_prizes = json_encode($params['bonus_prizes']);
//        }
//
//        if (!empty($params['enable_after'])) {
//            $bonus->enable_after = json_encode($params['enable_after']);
//        }
//        $bonus->json_params = json_encode($params?? []);
//
//        if (!$tmp) $bonus->save();
//
//        return $bonus;
//    }
//
//    /**
//     * bonus doesn't have money to payout
//     * @return float
//     */
//    public function getMoneyToPayout(): float
//    {
//        return 0;
//    }
//
//    public function getName(): string
//    {
////        if (!empty($this->pattern_id)) {
////            if ($pattern = BonusPatterns::find($this->pattern_id)) {
////                return __($pattern->name);
////            }
////        }
//        return 'Smart Wager';
//    }
//
//    public function isLocked(): bool
//    {
//        if (intval($this->status) !== self::STATUS_LOCKED) return false;
//
//        if (empty($this->enable_after) || !is_array(json_decode($this->enable_after, true))) {
//            $this->status = static::STATUS_NOT_ACTIVATED;
//            $this->save();
//            Log::error('Locked bonus has no enable_after ids');
//            return false;
//        }
//
////        if (UserGift::getNew(Auth::id())) {
////            return true;
////        }
//
//        $bonuses = json_decode($this->enable_after);
//        foreach ($bonuses as $bonus_pattern_id) {
//            $bonus = UserBonus::where('user_id', Auth::id())
//                ->where('pattern_id', $bonus_pattern_id)
//                ->whereNotIn('status', [UserBonus::STATUS_CANCELED, UserBonus::STATUS_EXPIRED, UserBonus::STATUS_DONE])->first();
//            if ($bonus) return true;
//        }
//
//        $this->status = static::STATUS_NOT_ACTIVATED;
//        $this->save();
//        return false;
//    }
//
//    public function getStatusName(): string
//    {
//        $this->isLocked();
//        return parent::getStatusName();
//    }
//
//    public function getDescription(): string
//    {
//        switch ($this->status) {
//            case self::STATUS_ACTIVE:
//                return __('bonuses.swBonusDescActive');
//
//            case self::STATUS_WAITING_FOR_AGREEMENT:
//                return __('bonuses.swBonusDescAgreement', [
//                    'sum' => $this->money_to_activate,
//                    'currency' => $this->curr_name,
//                    'bonus' => ceil($this->multiplier_to_payout),
//                    'wager' => $this->multiplier,
//                ]);
//
//            case self::STATUS_WAITING_FOR_PAYMENT:
//                return __('bonuses.swBonusDescPayment', [
//                    'sum' => $this->money_to_activate,
//                    'currency' => $this->curr_name,
//                ]);
//        }
//        return __('bonuses.swBonusDescAgreement', [
//            'sum' => $this->money_to_activate,
//            'currency' => $this->curr_name,
//            'bonus' => ceil($this->multiplier_to_payout),
//            'wager' => $this->multiplier,
//        ]);
//    }
//
//
//    public function getDescriptionName(): ?string
//    {
//        if ($this->pattern_id == BonusPatterns::PTN_FIRST_DEP_BONUS_SW) return 'first-deposit-sw';
//        if ($this->pattern_id == BonusPatterns::PTN_SECOND_DEP_BONUS_SW) return 'second-deposit-sw';
//        if ($this->pattern_id == BonusPatterns::PTN_THIRD_DEP_BONUS_SW) return 'third-deposit-sw';
//        return 'smart-wager';
//    }
//
//    public function isProgressAvailable(): bool
//    {
//        return $this->status == self::STATUS_ACTIVE;
//    }
//
//    public function getProgressPercents(): int
//    {
//        if ($this->wager_to_complete == 0) return 100;
//        $p = floor($this->total_bet / $this->wager_to_complete * 100);
//        if ($p > 100) $p = 100;
//        return $p;
//    }
//
//    public function getProgressHint(): string
//    {
//        if ($this->isProgressAvailable()) {
//            return __('bonuses.swBonusProgress', [
//                'bet' => floor($this->total_bet),
//                'total' => floor($this->wager_to_complete)
//            ]);
//        }
//        return '';
//    }
//
//    public function getStatusString(IGameWallet $game_wallet = null): string
//    {
//        return UI::bonusPad('Progress', $this->getProgressHint())
//            . UI::bonusPad('Progress %', "{$this->getProgressPercents()}%")
//            . UI::bonusPad('Balance', ($game_wallet ? UI::moneyStr($game_wallet->getBalance()) . ' ' . UserWallet::currencySymbol($this->currency) : '...'))
//            . UI::bonusPad('Bonus', UI::moneyStr($this->money_available) . ' ' . UserWallet::currencySymbol($this->currency));
//    }
//
//    public function depositCents(int $sum, IGameWallet $game_wallet, string $system, string $game_id): int
//    {
//        if ($this->real_money_spent > 0){
//            // 50% bonus amount 50% real amount
//            $bonus_amount = intval($sum * 0.5);
//            $real_amount = $sum - $bonus_amount;
//        } else {
//            $bonus_amount = $sum;
//            $real_amount = 0;
//        }
//
//        $this->money_available += ($bonus_amount / 100);
//        $game_wallet->addMoney($real_amount / 100);
//        $this->total_win += $sum / 100;
//        return intval($this->money_available + $game_wallet->getBalance()) * 100;
//    }
//
//    public function withdrawCents(int $sum, IGameWallet $game_wallet, string $system, string $game_id)
//    {
//        // 50% bonus amount 50% real amount
//        if ($this->spins_available > 0) {
//            $this->spins_available--;
//            return;
//        }
//        $k = 0.5;
//        $bonus_amount = intval($sum * $k);
//        $real_amount = $sum - $bonus_amount;
//        $this->real_money_spent = $k;
//
//        if ($game_wallet->getBalance() < ($sum / 100)) {
//            $bonus_amount = $sum;
//            $real_amount = 0;
//            $this->real_money_spent = 0;
//        }
//
//        if ($this->getBalanceCents($game_wallet) < $sum) {
//            return false;
//        }
//
//        $game_wallet->remMoney($real_amount / 100);
//
//        $this->money_available -= $bonus_amount / 100;
//        /** @var BonusWagerPrc $conf */
//        $conf = BonusWagerPrc::where('system', $system)->where('game_id', $game_id)->first();
//        if ($conf === null) {
//            $this->total_bet += $sum / 100;
//        } else {
//            $prc = ($conf->prc ?? 100) / 100;
//            $this->total_bet += ($sum * $prc) / 100;
//        }
//    }
//
//    public function getBalanceCents(IGameWallet $game_wallet = null): int
//    {
//        return intval(($this->money_available + ($game_wallet ? $game_wallet->getBalance() : 0)) * 100);
//    }
//
//    private function writeTransaction($amount)
//    {
//        $user = MUser::find($this->user_id);
//
//        DB::table('payout_transactions')
//            ->insert([
//                'user_id' => $user->id,
//                'hall_id' => $user->hall_id,
//                'date' => date('Y-m-d H:i:s'),
//                'sum' => $amount,
//                'status_id' => 35,
//                'currency' => UserWallet::currencyName($this->currency),
//                'operation' => "Выигрыш на бонусе deposit prize (id = {$this->id})",
//            ]);
//    }
//
//    public function forceToPlay(): bool
//    {
//        return true;
//    }
//
//    public function afterCreated(): void
//    {
//        if (empty($this->id)) {
//            Hlog::logException(new \Exception('Trying attach bonus with no id to segment'));
//            return;
//        }
//        parent::afterCreated();
//
//        BonusSegment::attachOrCreateByName($this, $this->multiplier_to_payout."% к депозиту");
//    }

}
