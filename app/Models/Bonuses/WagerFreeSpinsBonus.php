<?php

namespace App\Models\Bonuses;

/**
 * @deprecated
 */
class WagerFreeSpinsBonus extends KUserBonus
{
//    /**
//     * WagerFreeSpinsBonus constructor.
//     * Sets type of the bonus.
//     * @param array $attributes
//     */
//    public function __construct(array $attributes = [])
//    {
//        parent::__construct($attributes);
//        $this->type = static::TYPE_FREE_SPINS_WAGER;
//    }
//
//
//    protected $guarded = ['type'];
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
//        $money = $params['money'];
//        $wager = $params['wager'];
//
//        $bonus = new static();
//        $bonus->user_id = $user?->id;
//        $bonus->hall_id = $hall_id;
//        $bonus->currency = $curr;
//        $bonus->spins_available = -1;
//        $bonus->spins_total = 0;
//        $bonus->status = static::STATUS_NOT_ACTIVATED;
//        $bonus->money_available = $money;
//        $bonus->money_to_activate = 0;
//        $bonus->wager_to_complete = $money * $wager;
//        $bonus->wager_to_payout = $money;
//        $bonus->hours_active = 24;
//        $bonus->multiplier = $wager;
//        $bonus->max_win = $money;
//        $bonus->active_to = now()->addHours(72)->format('Y-m-d H:i:s');
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
//
//    public function getProgressHint(): string
//    {
//        if ($this->spins_available > 0) {
//            return __('bonus.spins_made') . ($this->spins_total - $this->spins_available) . '/' . $this->spins_total;
//        }
//        if ($this->isProgressAvailable()) {
//            return __('bonus.played') . number_format($this->total_bet, 2, '.', '') . '/' . number_format($this->wager_to_complete, 2, '.', '');
//        }
//        return '';
//    }
//
//    public function getProgressPercents(): int
//    {
//        if ($this->spins_available > 0) {
//            return $this->spins_available <= 0 ? 100 : (100 - round($this->spins_available / $this->spins_total * 100));
//        }
//        $p = $this->total_bet / $this->wager_to_complete * 100;
//        if ($p > 100) $p = 100;
//        return $p;
//    }
//
//    public function getName(): string
//    {
//        return __('bonus.bonus_21_name');
//    }
//
//    public function getDescription(): string
//    {
//        switch ($this->status) {
//            case self::STATUS_ACTIVE:
//                return __('bonus.bonus_21_descr1');
//
//            case self::STATUS_WAITING_FOR_PAYMENT:
//                return __('bonus.bonus_21_descr2');
////                return "Пополнитесь на сумму от $this->money_to_activate $this->curr_name. чтобы начать отыгрыш вашего бонуса.";
//        }
//
//        return __('bonus.bonus_21_descr3');
////        return 'Вам предоставлено '.number_format($this->money_available, 2, '.', '').' бонусных '.$this->curr_name.
////        '. Выберите вейджер и соответствующую сумму пополнения. Пополненная сума будет добавленка к вашему бонусному счету и включен выбраный вейджер.';
//    }
//
//    public function getDescriptionName(): ?string
//    {
//        return 'active-non-deposit';
//    }
//
//
//    public function actualize()
//    {
//        if ($this->isExpired()) return;
//
//        // Mark bonus as expired if user don't activated bonus until the specified time.
//        if ($this->status == self::STATUS_ACTIVE && strtotime($this->active_to) < time()) {
//            $this->status = self::STATUS_EXPIRED;
//            BonusStatistic::expired($this);
//            $this->save();
//        }
//
//        // Do nothing for inactive bonus.
//        if ($this->status != self::STATUS_ACTIVE) return;
//
//        if ($this->spins_available == 0) {
//            $this->spins_available = -1;
//            $this->wager_to_complete = (float)bcmul($this->money_available, $this->wager_to_complete);
//        }
//
//        // Complete bonus and transfer money if user won necessary amount of money.
//        if ($this->spins_available < 0 && $this->total_bet >= $this->wager_to_complete) {
//            if ($this->money_available > $this->max_win) {
//                $this->money_available = $this->max_win;
//            }
//            $this->spins_available = -1;
//            $this->money_paid_out = $this->money_available;
//            $this->writeTransaction($this->money_available);
//
//            /** @var MUser $user */
//            $user = MUser::find($this->user_id);
//            if ($user) {
//                $user->getWallet($this->currency)->addMoney($this->money_available)->save();
//            }
//
//            $this->money_available = 0;
//            $this->status = self::STATUS_DONE;
//            $this->save();
//
//            BonusStatistic::saveStat($this, $this->user_id, BonusStatistic::ACTION_WIN, $this->money_paid_out,
//                self::STATUS_ACTIVE, self::STATUS_DONE);
//            return;
//        }
//
//        if ($this->money_available <= 0 && $this->spins_available < 0) {
//            $this->money_available = 0;
//            $this->status = self::STATUS_DONE;
//            BonusStatistic::fail($this);
//        }
//
//        $this->save();
//    }
//
//    public function isProgressAvailable(): bool
//    {
//        return $this->status == self::STATUS_ACTIVE;
//    }
//
//    public function activate(): bool
//    {
//        if ($this->status == self::STATUS_NOT_ACTIVATED) {
//            if ($this->money_to_activate > 0) {
//                $this->status = self::STATUS_WAITING_FOR_PAYMENT;
//                BonusStatistic::saveStat($this, $this->user_id, BonusStatistic::ACTION_ACTIVATE, $this->money_to_activate,
//                    self::STATUS_WAITING_FOR_AGREEMENT, $this->status, $this->pattern_id);
//            } else {
//                $this->status = self::STATUS_ACTIVE;
//                BonusStatistic::saveStat($this, $this->user_id, BonusStatistic::ACTION_ACTIVATE, $this->money_available,
//                    self::STATUS_WAITING_FOR_AGREEMENT, $this->status, $this->pattern_id);
//            }
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
//                    'status_id' => 35,
//                    'currency' => $this->curr_name,
//                    'operation' => $this->getName() . ' drummatik bonus activated'
//                ]);
//
//            return true;
//        }
//
//        return false;
//    }
//
//    public function depositInfoText(): string
//    {
//        return "Активен " . $this->getName() . " бонус. При пополнении, однократно, сумма пополнения добавится к бонусному счету, а коэфициент вейджера будет изменен.
//        (100$this->curr_name - вейджер 20; 200$this->curr_name - вейджер 13; 300$this->curr_name - вейджер 10; 500$this->curr_name - вейджер 7; 1000$this->curr_name - вейджер 5)";
//    }
//
//    public function isActivationMenu(): bool
//    {
//        return true;
//    }
//
//    public function getActivationMenu(): ?array
//    {
//        $params = BonusMenuParam::getBonusParams($this->type, $this->currency);
//        if ($params !== null) {
//            $data = [];
//            /** @var BonusMenuParam $param */
//            foreach ($params as $param) {
//                $p_name = $param->name . " (" . UI::moneyStr($param->min_value) . $this->curr_name .  ").";
//                $data['wager'][$p_name] = $param->min_value;
//            }
//            return $data;
//        }
//        return [];
//    }
//
//    public function ActivateByMenuOption($option_value): bool
//    {
//        $params = BonusCurrencyConfig::getBonusConfig($this->currency, $this->type);
//
//        $money_k = 1;
//
//        if ($params === null) {
//            Log::channel('stack')->warning('bonus currency params not found! Currency:' . Auth::user()->currency . ' Bonus type#' . $this->type);
//        } else {
//            $money_k = $params->money_k;
//        }
//
//        $wager = $this->multiplier;
//        $this->money_to_activate = 0;
//        $max_win = $this->money_available;
//
//        $params = BonusMenuParam::getKeyParams($this->type, $this->currency, $option_value);
//        Log::info('activating bonus by selecting menu param '.var_export($params, true));
//        $wager = $params->getValue();
//        $this->money_to_activate = $params->getMinValue();
//        $max_win = $this->money_to_activate * 5;
//        if ($max_win == 0) $max_win = $this->money_available;
//
//        $this->wager_to_complete = $this->money_available * $wager;
//        $this->wager_to_payout = $wager;
//        $this->multiplier = $wager;
//        $this->multiplier_to_payout = $wager;
//        $this->multiplier_to_complete = $wager;
//        $this->max_win = $max_win;
//
//        BonusStatistic::saveStat($this, $this->user_id, BonusStatistic::ACTION_ACTIVATION_MENU, $option_value,
//            self::STATUS_WAITING_FOR_AGREEMENT, $this->status, $this->pattern_id, [
//                'wager' => $this->wager_to_complete,
//                'wager_k' => $wager,
//                'max_win' => $this->max_win,
//                'money_to_activate' => $this->money_to_activate,
//            ]);
//
//        $this->activate();
//        return true;
//    }
//
//    public function confirmPayment(MUser $user, float $amount, int $currency): float
//    {
//        $rest = 0;
//        if ($amount > $this->money_to_activate) {
//            $rest = $amount - $this->money_to_activate;
//            $amount = $this->money_to_activate;
//        }
//        $this->status = UserBonus::STATUS_ACTIVE;
//        $this->money_available += $amount;
//        $this->wager_to_complete = $this->multiplier * $this->money_available;
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
//                'operation' => 'active nondeposit bonus payed'
//            ]);
//        BonusStatistic::saveStat($this, $this->user_id, BonusStatistic::ACTION_DEPOSIT, $amount,
//            self::STATUS_WAITING_FOR_PAYMENT, $this->status, $this->pattern_id);
//
//        return $rest;
//    }
//
//    public function getStatusString(IGameWallet $game_wallet = null): string
//    {
//        if ($this->status != static::STATUS_ACTIVE) return '';
//        return UI::bonusPad(__('bonus.status_active'), $this->getProgressHint());
//    }
//
//    public function actualizeVegas(string $game_id, IGameWallet $game_wallet, string $system = 'vegas')
//    {
//        // Mark bonus as expired if user don't activated bonus until the specified time.
//        if ($this->status == self::STATUS_ACTIVE && strtotime($this->active_to) < time()) {
//            $this->status = self::STATUS_EXPIRED;
//            BonusStatistic::expired($this);
//            return;
//        }
//
//        // Do nothing for inactive bonus.
//        if ($this->status != self::STATUS_ACTIVE) return;
//
//        if ($this->spins_available == 0) {
//            $this->spins_available = -1;
//            $this->wager_to_complete = (float)bcmul($this->money_available, $this->wager_to_complete);
//            BonusStatistic::saveStat($this, $this->user_id, BonusStatistic::ACTION_FS_DONE, $this->money_available,
//                self::STATUS_ACTIVE, $this->status, $this->pattern_id, [
//                    'wager' => $this->wager_to_complete,
//                ]);
//        }
//
//        // Complete bonus and transfer money if user won necessary amount of money.
//        if ($this->spins_available < 0 && $this->total_bet >= $this->wager_to_complete) {
//            if ($this->money_available > $this->max_win) {
//                $this->money_available = $this->max_win;
//            }
//            $this->spins_available = -1;
//            $this->money_paid_out = $this->money_available;
//            $this->writeTransaction($this->money_available);
//
//            $game_wallet->addMoney($this->money_available);
//
//            $this->money_available = 0;
//            $this->status = self::STATUS_DONE;
//
//            $user = MUser::find($this->user_id);
//            BonusWin::userWin($user, $this->money_paid_out, $game_id, $this->currency, $system);
//            BonusStatistic::win($this, $this->money_paid_out);
//            return;
//        }
//
//        if ($this->money_available <= 0 && $this->spins_available < 0) {
//            $this->money_available = 0;
//            // call actualize in 5+ seconds
//            ActualizeBonusJob::dispatch($this->getId())->delay(now()->addSeconds(5));
//        }
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
//                'operation' => 'Выигрыш на бонусе Активный бездепозит (id = ' . $this->id . ')'
//            ]);
//    }

}
