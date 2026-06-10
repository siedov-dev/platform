<?php


namespace App\Models\Bonuses;

/**
 * @deprecated
 */
class SuperFsCashbackBonus extends SuperFreeSpinsBonus
{
//    public function __construct(array $attributes = [])
//    {
//        parent::__construct($attributes);
//        $this->type = static::TYPE_SUPER_FS_CASHBACK;
//    }
//
//    public function getName(): string
//    {
//        return __('bonus.bonus_13_name');
//    }
//
//    public function getDescription(): string
//    {
//        switch ($this->status) {
//            case self::STATUS_ACTIVE:
//                return __('bonus.bonus_13_descr1');
//
//            case self::STATUS_WAITING_FOR_AGREEMENT:
//                return __('bonus.bonus_13_descr2', ['amount' => number_format($this->money_available, 2), 'curr' => $this->curr_name]);
////                return "Вы выиграли ". number_format($this->money_available, 2) ." $this->curr_name. на фриспинах. После активации ваше пополнение
////                    на сумму не менее выбранной она перейдет на бонусный счет и позволит вам отыграть ваши бонусные средства.
////                    Так же будет предоставлен бонус cashback.";
//
//            case self::STATUS_WAITING_FOR_PAYMENT:
//                return __('bonus.bonus_13_descr3', ['amount' => $this->money_to_activate, 'curr' => $this->curr_name]);
////                return "Пополнитесь на сумму от $this->money_to_activate $this->curr_name. чтобы начать отигрыш вашего бонуса и получить cashback бонус.";
//        }
////        return __('bonus.bonus_13_descr4', [
////            'spins' => $this->spins_total,
////            'amount' => $this->money_to_activate,
////            'curr' => $this->curr_name,
////        ]);
//        return __('bonus.bonus_12_descr4', ['spins' => $this->spins_total]);
////        return "Вам предоставлено $this->spins_total бесплатных вращений.
////            После отыграша выберите вейджер, сумму пополнения и сможете получить
////            бонусные средства просто играя в игры DrumMatic, NetEnt и др.";
//    }
//
//    public function getDescriptionName(): ?string
//    {
//        return 'free-spins-cashback';
//    }
//
//    public function getStatusString(IGameWallet $game_wallet = null): string
//    {
//        if ($this->status != UserBonus::STATUS_ACTIVE) return '';
//        $spinsAvailable = $this->spins_available > 0 ? $this->spins_available : 0;
//        if ($spinsAvailable > 0) {
//            return __('bonus.bonus_pad', [
//                'spin' => ($this->spins_total - $spinsAvailable),
//                'spins' => $this->spins_total,
//                'max_bet' => 20,
//                'amount' => number_format($this->total_win, 2),
//                'curr' => UserWallet::currencyName($this->currency)
//            ]);
//        }
//        return UI::bonusPad(__('bonus.status_active'), $this->getProgressHint());
//    }
//
//    public function getProgressHint(): string
//    {
//        $spinsAvailable = $this->spins_available > 0 ? $this->spins_available : 0;
//        if ($spinsAvailable > 0)
//            return __('bonuses.wagerFSProgress', [
//                'spins' => $this->spins_total - $spinsAvailable,
//                'total' => $this->spins_total
//            ]);
//        if ($this->isProgressAvailable()) {
//            return floatval($this->total_bet) . '/' . intval($this->wager_to_complete);
//        }
//        return '';
//    }
//
//    public static function createForUser($user, $spins = 0, $tries = 0)
//    {
//        if ($spins === 0) {
//            $spins = self::getSetting(self::SET_BONUS_SPINS, 20);
//        }
//        $jparams['wager'] = self::getSetting(self::SET_WAGER_TO_COMPLETE_MULTIPLIER, 20);
//        $jparams['money'] = self::getSetting(self::SET_MAX_BET, 100);
//        $jparams['min_sum'] = self::getSetting(self::SET_MONEY_TO_ACTIVATE, 200);
//        $jparams['freespins'] = $spins;
//
//        $bonus = new static();
//
//        $params = $bonus->getCurrencyParams($user->currency, $jparams);
//        $wager = $params['wager'];
//        $money = $params['money'];
//        $spins = $params['freespins'];
//
//        $bonus->status = UserBonus::STATUS_NOT_ACTIVATED;
//        $bonus->money_to_activate = $params['min_sum'];
//        $bonus->user_id = $user->id;
//        $bonus->hall_id = $user->hall_id;
//        $bonus->currency = $user->currency;
//        $bonus->spins_total = $spins;
//        $bonus->spins_available = $spins;
//        $bonus->money_available = $money;
//        $bonus->total_win = 0;
//        $bonus->wager_to_complete = $wager;
//        $bonus->multiplier_to_complete = self::getSetting(self::SET_WAGER_TO_COMPLETE_MULTIPLIER, 20);
//        $bonus->wager_to_payout = self::getSetting(self::SET_WAGER_TO_PAYOUT_MULTIPLIER, 35);
//        $bonus->multiplier_to_payout = self::getSetting(self::SET_WAGER_TO_PAYOUT_MULTIPLIER, 35);
//        $bonus->tries = $tries;
//
//        $d = new \DateTime();
//        $d->add(new \DateInterval('P5D'));
//        $bonus->active_to = $d->format('Y-m-d H:i:s');
//
//        $bonus->save();
//        return $bonus;
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
//        $bonus->status = UserBonus::STATUS_NOT_ACTIVATED;
//        $bonus->money_to_activate = self::getSetting(self::SET_MONEY_TO_ACTIVATE, 200);
//        $bonus->user_id = $user_id;
//        $bonus->hall_id = $hall_id;
//        $bonus->currency = $curr;
//        $bonus->spins_total = $params['freespins'];
//        $bonus->spins_available = $params['freespins'];
//        $bonus->money_available = $params['money'];
//        $bonus->total_win = 0;
//        $bonus->wager_to_complete = $params['wager'];
//        $bonus->multiplier_to_complete = $params['wager'];
//        $bonus->wager_to_payout = $params['wager'];
//        $bonus->multiplier_to_payout = $params['wager'];
//        $bonus->json_params = json_encode($params?? []);
//
//        $d = new \DateTime();
//        $d->add(new \DateInterval('P5D'));
//        $bonus->active_to = $d->format('Y-m-d H:i:s');
//
//        if (!empty($params['enable_after'])) {
//            $bonus->enable_after = json_encode($params['enable_after']);
//        }
//
//        if (!$tmp) $bonus->save();
//        return $bonus;
//    }
//
//    public function wagerDiminution($percent)
//    {
//        $prc = 1 - ($percent / 100);
//        $this->wager_to_complete = floor($this->wager_to_complete * $prc);
//        $this->multiplier_to_complete = floor($this->multiplier_to_complete * $prc);
//        $this->wager_to_payout = floor($this->wager_to_payout * $prc);
//        $this->multiplier_to_payout = floor($this->multiplier_to_payout * $prc);
//        $this->save();
//        return $this;
//    }
//
//    public function activate(): bool
//    {
//        if ($this->status == self::STATUS_NOT_ACTIVATED || $this->status == self::STATUS_WAITING_FOR_AGREEMENT) {
//            $this->status = self::STATUS_WAITING_FOR_PAYMENT;
//            DB::table('payout_transactions')
//                ->insert([
//                    'user_id' => Auth::user()->id,
//                    'hall_id' => UserWallet::currencyHallId($this->currency),
//                    'date' => date('Y-m-d H:i:s'),
//                    'sum' => $this->spins_available,
//                    'status_id' => PayoutTransaction::STATUS_BONUS_ACTIVATED,
//                    'currency' => $this->curr_name,
//                    'operation' => 'superfreespins CB bonus activated'
//                ]);
//            if ($this->money_to_activate == 0) {
//                $this->status = self::STATUS_ACTIVE;
//            }
//            BonusStatistic::saveStat($this, $this->user_id, BonusStatistic::ACTION_ACTIVATE, $this->money_available,
//                self::STATUS_WAITING_FOR_AGREEMENT, $this->status);
//        }
//
//        $this->save();
//        return true;
//    }
//
//    public function actualize()
//    {
//        if ($this->isExpired()) return;
//
//        if ($this->money_available <= 0 && $this->spins_available < 0) {
//            $this->money_available = 0;
//            $this->status = UserBonus::STATUS_DONE;
//            BonusStatistic::saveStat($this, $this->user_id, BonusStatistic::ACTION_FAIL, $this->money_available,
//                UserBonus::STATUS_ACTIVE, UserBonus::STATUS_DONE);
//        }
//    }
//
//    public function actualizeVegas(string $game_id, IGameWallet $game_wallet, string $system = 'vegas')
//    {
//        // Mark bonus as expired if user don't activated bonus until the specified time.
//        if ($this->status === self::STATUS_PENDING && strtotime($this->active_to) < time()) {
//            $this->status = self::STATUS_EXPIRED;
//            BonusStatistic::saveStat($this, $this->user_id, BonusStatistic::ACTION_EXPIRED,
//                $this->money_available, $this->status, self::STATUS_EXPIRED);
//        }
//
//        // Do nothing for inactive bonus.
//        if ($this->status != self::STATUS_ACTIVE) return;
//
//        if ($this->spins_available > 0) return;
//
//        if ($this->spins_available == 0) {
//            $this->spins_available = -1;
//            $this->money_available += $this->total_win;
//            $this->total_win = 0;
//            $this->total_bet = 0;
//            $this->total_bet_for_payout = 0;
//
//            $this->wager_to_complete = $this->money_available * $this->multiplier_to_complete;
//
//            BonusStatistic::saveStat($this, $this->user_id, BonusStatistic::ACTION_DONE, $this->money_available,
//                self::STATUS_ACTIVE, self::STATUS_DONE);
//
//            Log::info('Vegas фриспины с выбором 2: все прокруты сделаны.');
//        }
//
//        if ($this->wager_to_complete <= $this->total_bet || $this->money_available <= 0) {
//            if ($this->max_win > 0 && $this->money_available > $this->max_win) {
//                $this->money_available = $this->max_win;
//            }
//
//            $game_wallet->addMoney($this->money_available);
//
//            $this->money_paid_out = $this->money_available;
//            $this->writeTransaction($this->money_available);
//            $this->money_available = 0;
//            $this->status = self::STATUS_DONE;
//
//            $user = MUser::find($this->user_id);
//            BonusWin::userWin($user, $this->money_paid_out, $game_id, $this->currency, $system);
//
//            BonusStatistic::saveStat($this, $this->user_id, BonusStatistic::ACTION_WIN, $this->money_paid_out,
//                self::STATUS_ACTIVE, self::STATUS_DONE);
//            return;
//        }
//
//        if ($this->money_available <= 0) {
//            $this->money_available = 0;
//            // call actualize in 5+ seconds
//            ActualizeBonusJob::dispatch($this->getId())->delay(now()->addSeconds(5));
//        }
//    }
//
//    public function withdrawCents(int $sum, IGameWallet $game_wallet, string $system, string $game_id)
//    {
//        if ($this->money_available < ($sum / 100)) return false;
//
//        //limit bet
////        if($sum > 2000) return false;
//
//        if ($this->spins_available > 0) {
//            $this->spins_available--;
//            $this->total_bet += $sum / 100;
//            return intval($this->money_available * 100);
//        }
//
//        $this->money_available -= ($sum / 100);
//        $this->total_bet += $sum / 100;
//        return intval($this->money_available * 100);
//    }
//
//    public function depositCents(int $sum, IGameWallet $game_wallet, string $system, string $game_id): int
//    {
//        if ($this->spins_available > 0) {
//            $this->total_win += $sum / 100;
//            return intval($this->money_available * 100);
//        }
//        $this->money_available += ($sum / 100);
//        $this->total_win += $sum / 100;
//        return intval($this->money_available * 100);
//    }
//
//    public function isActivationMenu(): bool
//    {
//        return true;
//    }
//
////    public function getActivationMenu(): ?array
////    {
////        if ($this->money_available <= 0) {
////            return [
////                'Продолжить' => [
////                    'Переиграть' => 2,
////                ]
////            ];
////        }
////
////        return [
////            'Продолжить' => [
////                'Активировать' => 1,
////                'Переиграть' => 2,
////            ]
////        ];
////    }
//
//    public function getActivationMenu(): ?array
//    {
//        $params = BonusMenuParam::getBonusParams(UserBonus::TYPE_SUPER_FREE_SPINS_PLAYBACK, $this->currency);
//        if ($params !== null) {
//            $data = [];
//            /** @var BonusMenuParam $param */
//            foreach ($params as $param) {
//                $p_name = $param->name . " (" . UI::moneyStr($param->min_value) . $this->curr_name . ").";
//                $data['wager'][$p_name] = $param->min_value;
//            }
//            return $data;
//        }
//
//        Log::error('no menu settings found for bonus #' . $this->id);
//        return null;
//    }
//
//    public function ActivateByMenuOption($option_value): bool
//    {
//        $params = BonusCurrencyConfig::getBonusConfig(Auth::user()->currency, $this->type);
//
//        $money_k = 1;
//
//        if ($params === null) {
//            Log::channel('stack')->warning('bonus currency params not found! Currency:' . $this->currency . ' Bonus type#' . $this->type);
//        } else {
//            $money_k = $params->money_k;
//        }
//
//        $wager = 25;
//        $this->money_to_activate = 200;
//
//        $params = BonusMenuParam::getKeyParams(UserBonus::TYPE_SUPER_FREE_SPINS_PLAYBACK, $this->currency, $option_value);
//        Log::info('activating bonus by selecting menu param ' . var_export($params, true));
//        $wager = $params->getValue();
//        $this->money_to_activate = $params->getMinValue();
//
//        $this->wager_to_complete = $this->money_available * $wager;
//        $this->wager_to_payout = $wager;
//        $this->multiplier_to_payout = $wager;
//        $this->multiplier_to_complete = $wager;
//        $this->max_win = $this->money_available;
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
////    public function ActivateByMenuOption($option_value): bool
////    {
////        $jparams['wager'] = self::getSetting(self::SET_WAGER_TO_COMPLETE_MULTIPLIER, 20);
////        $jparams['money'] = self::getSetting(self::SET_MAX_BET, 100);
////        $jparams['freespins'] = 100;
////
////        $params = $this->getCurrencyParams($this->currency, $jparams);
////        $wager = $params['wager'];
////        $money = $params['money'];
////        $spins = $params['freespins'];
////
////
////        switch ($option_value) {
////            case 1:
////                $this->type = self::TYPE_SUPER_FS_CASHBACK_PB;
////                $this->status = self::STATUS_WAITING_FOR_AGREEMENT;
////                BonusStatistic::saveStat($this, $this->user_id, BonusStatistic::ACTION_ACTIVATION_MENU, 1,
////                    self::STATUS_WAITING_FOR_AGREEMENT, $this->status, $this->pattern_id);
////                break;
////            case 2:
////                $this->tries--;
////                $this->status = self::STATUS_ACTIVE;
////                $this->spins_total = 100;
////                $this->spins_available = $spins;
////                $this->money_available = $money;
////                $this->total_win = 0;
////                $this->wager_to_complete = $wager;
////                $this->multiplier_to_complete = self::getSetting(self::SET_WAGER_TO_COMPLETE_MULTIPLIER, 20);
////                $this->wager_to_payout = self::getSetting(self::SET_WAGER_TO_PAYOUT_MULTIPLIER, 35);
////                $this->multiplier_to_payout = self::getSetting(self::SET_WAGER_TO_PAYOUT_MULTIPLIER, 35);
////                BonusStatistic::saveStat($this, $this->user_id, BonusStatistic::ACTION_ACTIVATION_MENU, 2,
////                    self::STATUS_WAITING_FOR_AGREEMENT, $this->status, $this->pattern_id);
////                BonusStatistic::saveStat($this, $this->user_id, BonusStatistic::ACTION_ACTIVATE, $this->money_available,
////                    self::STATUS_WAITING_FOR_AGREEMENT, $this->status);
////                break;
////        }
////        $this->save();
////        return true;
////    }
//
//    public function confirmPayment(MUser $user, float $amount, int $currency): float
//    {
//        $this->status = UserBonus::STATUS_ACTIVE;
//        $this->money_available += $amount;
//        $this->wager_to_complete = $this->multiplier_to_complete * $this->money_available;
//        $this->max_win = $amount * 5;
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
//                'operation' => 'superfreespin cashback bonus payed'
//            ]);
//
//        BonusStatistic::saveStat($this, $this->user_id, BonusStatistic::ACTION_DEPOSIT,
//            $amount, self::STATUS_WAITING_FOR_PAYMENT, $this->status);
//
//        CashbackEventBonus::cancelForUser($user);
//
//        if ($amount >= CashbackEventBonus::getMinDepSum($this->currency)) {
//            $cb_bonus = CashbackEventBonus::createForUser($user, $amount);
//            $cb_bonus->active_to = now()->addHours(72)->format('Y-m-d H:i:s');
//            $cb_bonus->save();
//        }
//
//        return 0;
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
//                'operation' => 'Выигрыш на бонусе СуперФриСпины (id = ' . $this->id . ')'
//            ]);
//    }

}
