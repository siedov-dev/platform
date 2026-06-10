<?php


namespace App\Models\Bonuses;


/**
 * @deprecated
 */
class SuperFsCashbackPbBonus extends SuperFreeSpinsPlayback
{
//    public function __construct(array $attributes = [])
//    {
//        parent::__construct($attributes);
//        $this->type = static::TYPE_SUPER_FS_CASHBACK_PB;
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
//        return __('bonus.bonus_13_descr4', [
//            'spins' => $this->spins_total,
//            'amount' => $this->money_to_activate,
//            'curr' => $this->curr_name,
//        ]);
////        return "Вам предоставлено $this->spins_total фриспинов.
////            После пополнения на сумму $this->money_to_activate $this->curr_name. вы сможете получить
////            бонусные средства просто играя в игры. Так же будет предоставлен бонус cashback.";
//    }
//
//    public function getDescriptionName(): ?string
//    {
//        return 'free-spins-cashback';
//    }
//
//    public function activate(): bool
//    {
//        if ($this->status == self::STATUS_WAITING_FOR_AGREEMENT) {
//            $this->type = self::TYPE_SUPER_FS_CASHBACK_PB;
//            $this->status = self::STATUS_WAITING_FOR_PAYMENT;
//        }
//
//        $this->save();
//        return true;
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

}
