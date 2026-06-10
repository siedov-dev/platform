<?php

namespace App\Models\Bonuses;


use App\GamesProviders\IGameWallet;
use App\Helpers\UI;
use App\MUser;
use App\Services\KBonus\KBonus;

class NonDepositBonus extends KUserBonus
{

    /**
     * Creates non-deposit bonus for a given user.
     *
     * @param MUser $user
     * @param float $money
     * @return KUserBonus
     */
    public static function createForUser(MUser $user, float $money = 100): KUserBonus
    {
        $jparams['money'] = $money;
        $jparams['wager'] = 35;
        return (new self)->createFromParams(json_encode($jparams), $user->id, $user->currency);

//        $bonus = new static();
//
//        $params = $bonus->getCurrencyParams($user->currency, $jparams);
//
//        $money = $params['money'];
//
//        $bonus = new static();
//        $bonus->status = static::STATUS_NOT_ACTIVATED;
//        $bonus->user_id = $user->id;
//        $bonus->hall_id = $user->hall_id;
//        $bonus->currency = $user->currency;
//        $bonus->spins_available = -1;
//        $bonus->money_available = $money;
//        $bonus->wager_to_complete = 35;
//        $bonus->wager_to_payout =  35;
//        $bonus->multiplier = 0;//accumulation percent 0.01 = 1%
//
//        $d = new \DateTime();
//        $d->add(new \DateInterval('P5D'));
//        $bonus->active_to = $d->format('Y-m-d H:i:s');
//
//        $bonus->save();
//
//        return $bonus;
    }

//    public function createFromParams(string $params, int $user_id, ?int $currency = null, ?bool $keep_params = false, ?bool $tmp = false): NonDepositBonus
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
//        $bonus->user_id = $user_id;
//        $bonus->status = static::STATUS_NOT_ACTIVATED;
//        $bonus->hall_id = $hall_id;
//        $bonus->currency = $curr;
//        $bonus->spins_available = -1;
//        $bonus->money_available = $params['money'];
//        $bonus->wager_to_complete = $params['money'] * $params['wager'];
//        $bonus->wager_to_payout = $params['wager'];
//        $bonus->multiplier = 0; //accumulation percent
//
//        $d = new \DateTime();
//        $d->add(new \DateInterval('P5D'));
//        $bonus->active_to = $d->format('Y-m-d H:i:s');
//        $bonus->json_params = json_encode($params?? []);
//
//        if (!empty($params['enable_after'])) {
//            $bonus->enable_after = json_encode($params['enable_after']);
//        }
//
//        if (!$tmp) $bonus->save();
//
//        return $bonus;
//    }

    public function getName(): string
    {
        return __('bonus.bonus_2_name');
    }

    public function getDescription(): string
    {
        return __('bonus.bonus_2_descr1', ['prc' => round($this->multiplier * 100)]);
//        return 'В зависимости от сумы пополнения, вам будет возвращаться определённый % за ваши прокруты на реальные деньги. Активный возврат '.round($this->multiplier*100).'%';
    }

    public function getDescriptionName(): ?string
    {
        return 'active-cashback';
    }

    public function getIconUrl(): string
    {
        return '/img/icons/dollar-chip.svg';
    }

    public function isProgressAvailable(): bool
    {
        return $this->status === self::STATUS_ACTIVE || $this->status === self::STATUS_FILTHY;
    }

    public function getProgressHint(): string
    {
        if ($this->status === self::STATUS_ACTIVE) {
            return __('bonus.bonus_2_bets') . round($this->total_bet) . '/' . round($this->wager_to_complete);
        }
        if ($this->status === self::STATUS_FILTHY) {
            return __('bonus.bonus_2_available') . round($this->total_bet_for_payout) . ' / ' . round($this->money_available);
        }
        return '';
    }

    public function getProgressPercents(): int
    {
        if ($this->status === self::STATUS_ACTIVE) {
            return round($this->total_bet / $this->wager_to_complete * 100);
        }
        return 0;
    }

    public function getMoneyAvailableForPlay(): float
    {
        return $this->money_available;
    }

    public function getStatusString(IGameWallet $game_wallet = null): string
    {
        if ($this->status != static::STATUS_ACTIVE) return '';
        return UI::bonusPad(__('bonus.status_active'), $this->getProgressHint());
    }

//    public function actualizeVegas(string $game_id, IGameWallet $game_wallet, string $system = 'vegas')
//    {
//        // Mark bonus as expired if user don't activated bonus until the specified time.
//        if ($this->status === self::STATUS_PENDING && strtotime($this->active_to) < time()) {
//            $this->status = self::STATUS_EXPIRED;
//            BonusStatistic::saveStat($this, $this->user_id, BonusStatistic::ACTION_EXPIRED, $this->money_available, $this->status, self::STATUS_EXPIRED);
//        }
//
//        // Do nothing for inactive bonus.
//        if ($this->status != self::STATUS_ACTIVE) return;
//
//        if ($this->wager_to_complete <= $this->total_bet || $this->money_available <= 0) {
//            if ($this->multiplier_to_payout <= 1) {
//                $this->multiplier_to_payout = 10;
//            }
//            $this->wager_to_payout = (float)bcmul($this->money_available, $this->multiplier_to_payout);
//            $this->status = self::STATUS_DONE;
//        }
//
//        if ($this->money_available <= 0) {
//            $this->money_available = 0;
//            // call actualize in 5+ seconds
//            ActualizeBonusJob::dispatch($this->getId())->delay(now()->addSeconds(5));
//        }
//    }

//    public function actualize()
//    {
//        if ($this->isExpired()) return;
//
//        if ($this->status == self::STATUS_FILTHY && strtotime($this->active_to) < time()) {
//            $this->status = self::STATUS_EXPIRED;
//            $this->save();
//            BonusStatistic::saveStat($this, $this->user_id, BonusStatistic::ACTION_EXPIRED,
//                $this->money_available, self::STATUS_FILTHY, self::STATUS_EXPIRED);
//        }
//
//        if ($this->status == self::STATUS_FILTHY && $this->money_available <= 0) {
//            $this->status = self::STATUS_DONE;
//            $this->save();
//            BonusStatistic::saveStat($this, $this->user_id, BonusStatistic::ACTION_DONE,
//                $this->money_available, self::STATUS_FILTHY, self::STATUS_DONE);
//        }
//
//        if ($this->status == self::STATUS_DONE && $this->money_available > 0) {
//            $this->status = self::STATUS_FILTHY;
//            $this->save();
//        }
//
//        if ($this->status == self::STATUS_ACTIVE) {
//            $this->status = self::STATUS_FILTHY;
//            $this->save();
//        }
//        // Mark bonus as expired if user don't activated bonus until the specified time.
//        if (($this->status == self::STATUS_PENDING || $this->status == self::STATUS_NOT_ACTIVATED) && strtotime($this->active_to) < time()) {
//            $this->status = self::STATUS_EXPIRED;
//            $this->save();
//            BonusStatistic::saveStat($this, $this->user_id, BonusStatistic::ACTION_EXPIRED, $this->money_available, self::STATUS_PENDING, self::STATUS_EXPIRED);
//        }
//
//        // Do nothing for inactive bonus.
//        if ($this->status != self::STATUS_ACTIVE) return;
//        //unreachable code
//
//        if ($this->wager_to_complete <= $this->total_bet || $this->money_available <= 0) {
//            if ($this->multiplier_to_payout <= 1) {
//                $this->multiplier_to_payout = 10;
//            }
//            $this->wager_to_payout = (float)bcmul($this->money_available, $this->multiplier_to_payout);
//            $this->status = self::STATUS_DONE;
//            $this->save();
//        }
//
//        if ($this->money_available <= 0) {
//            $this->money_available = 0;
//            $this->status = self::STATUS_DONE;
//            BonusStatistic::saveStat($this, $this->user_id, BonusStatistic::ACTION_FAIL, $this->money_available, self::STATUS_ACTIVE, self::STATUS_DONE);
//            $this->save();
//        }
//    }

//    public function activate(): bool
//    {
//        $dt = new \DateTime();
//        $this->status = UserBonus::STATUS_FILTHY;
//
//        if ($this->hours_active) {
//            $dt->add(new \DateInterval('PT' . $this->hours_active . 'H'));
//            $this->active_to = $dt->format('Y-m-d H:i:s');
//        }
//        $this->save();
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
//        BonusStatistic::saveStat($this, $this->user_id, BonusStatistic::ACTION_ACTIVATE, $this->money_available,
//            self::STATUS_WAITING_FOR_AGREEMENT, $this->status);
//
//        return true;
//    }

    public function getMoneyToPayout(): float
    {
        $amount = round($this->total_bet_for_payout, 2);
        if ($amount > $this->money_available) {
            $amount = $this->money_available;
        }

        return round($amount, 2);
    }

    public static function getDone($user_id): ?KUserBonus
    {
        /** @var MUser $user */
        $user = MUser::find($user_id);

        if ($user) {
            $bonuses  = KBonus::getInstance()->getUserBonuses($user->getId(), $user->getCurrency());
            foreach ($bonuses as $bonus) {
                if ($bonus->type === static::TYPE_NON_DEPOSIT
                    && $bonus->status === static::STATUS_FILTHY
                    && $bonus->money_available > 0) {
                    return $bonus;
                }
            }
//            return static::where('user_id', $user->id)
//                ->where('currency', $user->currency)
//                ->where('status', static::STATUS_FILTHY)
//                ->where('type', static::TYPE_NON_DEPOSIT)
//                ->where('money_available', '>', 0)->first();
        }

        return null;
    }

//    public function onUserBet(float $amount, string $game_id)
//    {
//        $accumulation = $amount * $this->multiplier;
//        $this->total_bet_for_payout += $accumulation;
//        if ($this->total_bet_for_payout > $this->money_available) {
//            $this->total_bet_for_payout = $this->money_available;
//        }
//        $this->save();
//    }

    public function transferBonusMoney(): float
    {
        $resp = KBonus::getInstance()->transferBonusMoney($this->id);
        if (empty($resp || $resp['cd'] !== 200)) {
            return 0;
        }
        $this->attributes = $resp['attributes'];
        $this->meta = $resp['meta'];
        return $resp['amount'];
//        if ($this->total_bet_for_payout > $this->money_available) {
//            $this->total_bet_for_payout = $this->money_available;
//        }
//
//        if ($this->status == static::STATUS_FILTHY && $this->total_bet_for_payout > 0) {
//
//            /** @var MUser $user */
//            $user = MUser::findOrFail($this->user_id);
//
//            $this->money_available = round($this->money_available - $this->total_bet_for_payout, 2);
//            $this->money_paid_out = round($this->money_paid_out + $this->total_bet_for_payout, 2);
//
//            $money_to_payout = $this->total_bet_for_payout;
//
//            if ($this->money_available <= 0) {
//                $this->status = self::STATUS_DONE;
//            }
//
//            $this->total_bet_for_payout = 0;
//
//            if ($this->save()) {
//                $user->getWallet($this->currency)->addMoney($money_to_payout)->save();
//
//                $transaction = new PayoutTransaction();
//                $transaction->user_id = Auth::id();
//                $transaction->hall_id = $user->hall_id;
//                $transaction->sum = $money_to_payout;
//                $transaction->currency = UserWallet::currencyName($this->currency);
//                $transaction->status_id = 100;
//                $transaction->operation = 'Перевод с бонуса #' . $this->id;
//                $transaction->type_payout_id = 'administrator';
//                $transaction->use_bonus = 1;
//                $transaction->date = date('Y-m-d H:i:s');
//                $transaction->save();
//
//                \Illuminate\Support\Facades\DB::table('new_h_log')->insert([
//                    'user_id' => $user->id,
//                    'game_id' => 0,
//                    'game_status' => 0,
//                    'credits' => $money_to_payout,
//                    'money' => $user->b_money_s + $user->money,
//                    'last_win' => 0,
//                    'line' => 0,
//                    'nominal' => 1,
//                    'bet' => 0,
//                    'action' => 'Перевод на основной счет ' . $money_to_payout . ' ' . UserWallet::currencyName($this->currency) . '. c бонуса ' . $this->id,
//                    'request' => '',
//                    'result' => '',
//                    'action_table_id' => 0,
//                    'user_ip' => \Illuminate\Support\Facades\Request::ip(),
//                    'drum_rand' => ''
//                ]);
//
//                event(new MoneySpent($user, $money_to_payout * -1, UserWallet::currencyName($this->currency)), 'bonus');
//
//                BonusStatistic::saveStat($this, $this->user_id, BonusStatistic::ACTION_WIN, $money_to_payout, self::STATUS_ACTIVE, self::STATUS_DONE);
//
//                return $money_to_payout;
//            }
//        }
//
//        return false;
    }

//    public function confirmPayment(MUser $user, float $amount, int $currency): float
//    {
//        $params = BonusMenuParam::findInRangeParams(self::TYPE_NON_DEPOSIT, $currency, $amount);
//        if ($params !== null) {
//            $this->multiplier = $params->value / 100;
//            $this->save();
//        }
//        return $amount;
//    }
}
