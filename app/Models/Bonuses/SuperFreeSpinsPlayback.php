<?php

namespace App\Models\Bonuses;

/**
 * @deprecated
 */
class SuperFreeSpinsPlayback extends KUserBonus
{
//    /**
//     * DepositBonus constructor.
//     * Sets type of the bonus.
//     * @param array $attributes
//     */
//    public function __construct(array $attributes = [])
//    {
//        parent::__construct($attributes);
//        $this->type = static::TYPE_SUPER_FREE_SPINS_PLAYBACK;
//    }
//
//
//    protected $guarded = ['type'];
//
//
//    public static function getSetting($setting, $default)
//    {
//        return BonusSettings::getProperty(self::TYPE_SUPER_FREE_SPINS_PLAYBACK, $setting, $default);
//    }
//
//    public function activate(): bool
//    {
//        if ($this->status == self::STATUS_WAITING_FOR_AGREEMENT) {
//            $this->type = self::TYPE_SUPER_FREE_SPINS_PLAYBACK;
//            $this->status = self::STATUS_WAITING_FOR_PAYMENT;
//            BonusStatistic::saveStat($this, $this->user_id, BonusStatistic::ACTION_ACTIVATE, $this->money_to_activate,
//                self::STATUS_WAITING_FOR_AGREEMENT, $this->status);
//
//        }
//
//        $this->save();
//
//        return true;
//    }
//
//    public function getName(): string
//    {
//        return __('bonus.bonus_10_name');
//    }
//
//    public function getDescriptionName(): ?string
//    {
//        return 'super-free-spins';
//    }
//
//    public function getDescription(): string
//    {
//        switch ($this->status) {
//            case self::STATUS_ACTIVE:
//                return __('bonus.bonus_11_descr1');
//
//            case self::STATUS_WAITING_FOR_AGREEMENT:
//                return __('bonus.bonus_11_descr2', ['amount' => number_format($this->money_available, 2), 'curr' => $this->curr_name]);
////                return "Вы выиграли ". number_format($this->money_available, 2).$this->curr_name.". на бесплатных вращениях. После активации ваше пополнение
////                    на сумму не менее выбранной она перейдет на бонусный счет и позволит вам отыграть ваши бонусные средства.";
//
//            case self::STATUS_WAITING_FOR_PAYMENT:
//                return __('bonus.bonus_11_descr3', ['amount' => number_format($this->money_to_activate, 2), 'curr' => $this->curr_name]);
////                return "Пополнитесь на сумму от $this->money_to_activate $this->curr_name чтобы начать отыгрыш вашего бонуса.";
//        }
//        return __('bonus.bonus_11_descr4', ['spins' => $this->spins_total, 'amount' => number_format($this->money_to_activate, 2), 'curr' => $this->curr_name]);
////        return "Вам предоставлено $this->spins_total бесплатных вращений.
////            После пополнения на сумму $this->money_to_activate $this->curr_name. вы сможете получить
////            бонусные средства просто играя в игры.";
//    }
//
//
//    public function isProgressAvailable(): bool
//    {
//        return $this->status == self::STATUS_ACTIVE;
//    }
//
//    public function isAgreementRequired(): bool
//    {
//        return $this->status == self::STATUS_WAITING_FOR_AGREEMENT;
//    }
//
//    public function getProgressHint(): string
//    {
//        if ($this->isProgressAvailable()) {
//            return floatval($this->total_bet) . '/' . intval($this->wager_to_complete);
//        }
//        return '';
//    }
//
//    public function getProgressPercents(): int
//    {
//        $p = $this->total_bet / $this->wager_to_complete * 100;
//        if ($p > 100) $p = 100;
//        return $p;
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
//
//        Log::error('no menu settings found for bonus #'.$this->id);
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
//        $params = BonusMenuParam::getKeyParams($this->type, $this->currency, $option_value);
//        Log::info('activating bonus by selecting menu param '.var_export($params, true));
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
//    public function isVegasAllowed(): bool
//    {
//        return true;
//    }
//
//    public function getStatusString(IGameWallet $game_wallet = null): string
//    {
//        if ($this->status != static::STATUS_ACTIVE) return '';
//        return UI::bonusPad(__('bonus.status_active'), $this->getProgressHint());
//    }
//
//    public function actualize()
//    {
//        if ($this->isExpired()) return;
//
//        if ($this->money_available <= 0) {
//            $this->money_available = 0;
//            $this->status = self::STATUS_DONE;
//
//            BonusStatistic::saveStat($this, $this->user_id, BonusStatistic::ACTION_FAIL, $this->money_available,
//                self::STATUS_ACTIVE, self::STATUS_DONE);
//        }
//    }
//
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
//        if ($this->spins_available > 0) return;
//
//        if ($this->spins_available == 0) {
//            $this->spins_available = -1;
//            $this->money_available = $this->total_win;
//            $this->total_win = 0;
//            $this->total_bet = 0;
//            $this->total_bet_for_payout = 0;
//
//            // Transform this bonus to the playback.
//            $this->type = self::TYPE_SUPER_FREE_SPINS_PLAYBACK;
//            $this->status = self::STATUS_WAITING_FOR_AGREEMENT;
//
//            $this->wager_to_complete = $this->money_available * $this->multiplier_to_complete;
//
//            BonusStatistic::saveStat($this, $this->user_id, BonusStatistic::ACTION_DONE, $this->money_available, self::STATUS_ACTIVE, self::STATUS_DONE);
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
//                'operation' => 'superfreespin bonus payed'
//            ]);
//
//        BonusStatistic::saveStat($this, $this->user_id, BonusStatistic::ACTION_DEPOSIT, $amount, self::STATUS_WAITING_FOR_PAYMENT, $this->status);
//
//        return 0;
//    }

}
