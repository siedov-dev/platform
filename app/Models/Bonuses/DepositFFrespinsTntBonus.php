<?php


namespace App\Models\Bonuses;

/**
 * not implemented
 * @deprecated
 */
class DepositFFrespinsTntBonus extends DepositPrizeBonus
{
//    public function __construct(array $attributes = [])
//    {
//        parent::__construct($attributes);
//        $this->type = static::TYPE_DEPOSIT_FFS_TNT;
//    }
//
//    protected $guarded = ['type'];
//
//    public static function getSetting($setting, $default)
//    {
//        return BonusSettings::getProperty(self::TYPE_DEPOSIT_FFS_TNT, $setting, $default);
//    }

    public function getName(): string
    {
        $params = json_decode($this->json_params, true);

        if($params === null || !is_array($params)) {
            return __('bonus.bonus_25_name');
        }

        return __('bonus.bonus_25_name').($params['enable_tournament']?'/'.__('tournament.bonus_tournament') : '');
    }

    public function getDescription(): string
    {
        switch ($this->status) {
            case self::STATUS_ACTIVE:
                return __('.bonus.bonus_25_descr1');
//                return "Играйте в игры чтобы перевести все бонусные средства на основной счет.";

            case self::STATUS_WAITING_FOR_AGREEMENT:
                return __('bonus.bonus_25_descr2', [
                    'amount' => $this->money_to_activate,
                    'curr' => $this->curr_name,
                    'prc' => ceil($this->multiplier_to_payout),
                    'wager' => $this->multiplier,
                ]);
//                return "После активации и пополнения на сумму от $this->money_to_activate $this->curr_name вам будет начислен бонус
//                 ".ceil($this->multiplier_to_payout)."% от пополнения и включен вейджер Х$this->multiplier.На ваши средства X1. + Выдан подорок 40FS ";

            case self::STATUS_WAITING_FOR_PAYMENT:
                $params = json_decode($this->json_params, true);
                if(!empty($params && isset($params['enable_tournament']))) {
                    return __('bonus.bonus_25_descr3', [
                            'amount' => $this->money_to_activate,
                            'curr' => $this->curr_name,
                        ]) . ($params['enable_tournament'] ? '/' . __('tournament.bonus_tournament') : '');
                } else {
                    return __('bonus.bonus_25_descr3', [
                            'amount' => $this->money_to_activate,
                            'curr' => $this->curr_name,
                        ]);
                }
//                return "Пополнитесь на сумму от $this->money_to_activate $this->curr_name чтобы активировать бонус + FreeSpin".($params['enable_tournament']?'/'.__('tournament.bonus_tournament') : '');
        }

        return __('bonus.bonus_25_descr2', [
            'amount' => $this->money_to_activate,
            'curr' => $this->curr_name,
            'prc' => ceil($this->multiplier_to_payout),
            'wager' => $this->multiplier,
        ]);
    }

    public function getDescriptionName(): ?string
    {
        return 'deposit-free-spins-tournament';
    }

//    /**
//     * Creates deposit bonus for given user.
//     * @param MUser $user
//     * @param int $wager
//     * @param int $payment_multiplier
//     * @return DepositPrizeBonus
//     */
//    public static function createForUser($user, $wager = 40, $payment_multiplier = 150)
//    {
//        $jparams['wager'] = $wager;
//        $jparams['min_sum'] = 500;
//        $jparams['max_sum'] = 10000;
//
//        $bonus = new static();
//
//        $params = $bonus->getCurrencyParams($user->currency, $jparams);
//
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
//        $user->setRedisValue('has_new_bonus', true);
//
//        return $bonus;
//    }


//    public function createFromParams(string $params, int $user_id, ?int $currency = null, ?bool $keep_params = false, ?bool $tmp = false): DepositFFrespinsTntBonus
//    {
//        $sparams = $params;
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
//        $bonus->status = $params['status']?? static::STATUS_WAITING_FOR_AGREEMENT;
//        $bonus->user_id = $user_id;
//        $bonus->hall_id = $hall_id;
//        $bonus->currency = $curr;
//        $bonus->spins_available = -1;
//        //money should be set after pay in
//        $bonus->money_available = 0;
//        //minimal refill sum
//        $bonus->money_to_activate = $params['min_sum'];
//        //max refill sum
//        $bonus->multiplier_to_complete = $params['max_sum'];
//        //wager koef
//        $bonus->multiplier = $params['wager'];
//        //money multiplier in %
//        $bonus->multiplier_to_payout = $params['multiplier'];
//        $bonus->wager_to_payout = 0;
//        $bonus->active_to = now()->addHours($params['hours_active']?? 90)->format('Y-m-d H:i:s');
//        $bonus->json_params = json_encode($params?? []);
//
//        if(!empty($params['bonus_prizes'])){
//            $bonus->bonus_prizes = json_encode($params['bonus_prizes']);
//        }
//
//        if(!empty($params['enable_after'])){
//            $bonus->enable_after = json_encode($params['enable_after']);
//        }
//        if (!$tmp) $bonus->save();
//        return $bonus;
//    }

//    public function confirmPayment(MUser $user, float $amount, int $currency): float
//    {
//        $params = json_decode($this->json_params, true);
//
//        $rest = $amount;
//        $start_amount = $amount;
//        //if refill sum is bigger then bonus max limit
//        if($amount > $this->multiplier_to_complete) {
////            $rest = $amount - $this->multiplier_to_complete;
//            $amount = $this->multiplier_to_complete;
//        }
//
//        if($this->isPaymentRequired()) {
//            $this->money_available = $amount;
//            //win limit payment sum
//            $this->wager_to_payout = $amount;
//            $this->money_available = $this->money_available * $this->multiplier_to_payout / 100;
//            $this->wager_to_complete = $this->money_available * $this->multiplier;
//            $this->status = static::STATUS_ACTIVE;
//            $this->save();
//
//            $user->getWallet($this->currency)->addLockedMoney($amount * $params['balance_wager']??1);
//
//            $user->notify($this->getName(), __('bonus.bonus_money_locked').$user->getWallet($this->currency)->getLockedMoney().' '.$user->getWallet($this->currency)->getCurrencyName());
//
//            DB::table('payout_transactions')
//                ->insert([
//                    'user_id' => $user->id,
//                    'hall_id' => $user->hall_id,
//                    'date' => date('Y-m-d H:i:s'),
//                    'sum' => $amount,
//                    'status_id' => 35,
//                    'currency' => UserWallet::currencyName($this->currency),
//                    'operation' => 'deposit wager bonus payed'
//                ]);
//
//            BonusStatistic::saveStat($this, $this->user_id, BonusStatistic::ACTION_DEPOSIT, $amount,
//                self::STATUS_WAITING_FOR_PAYMENT, self::STATUS_ACTIVE);
//
//            $this->onActivate();
//
//            return $rest;
//        }
//
//        Log::error('DepositPrizeBonus error confirm payment');
//
//        return $start_amount;
//    }

//    public function onActivate()
//    {
//        $params = json_decode($this->json_params, true);
//        $user = MUser::find($this->user_id);
//
//        FilthyFreeSpinsBonus::createForUser($user, $params['freespins']??40, $params['fs_wager']);
//
//        if ($params['enable_tournament'] == 1){
//            $cmd = UserCommand::create(['type' => UserCommand::UCMD_ADD_TO_REG_TNT]);
//            $cmd->execute($user, $this->currency);
//        }
//    }

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
