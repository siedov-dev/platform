<?php

namespace App\Models\Bonuses;


use App\GamesProviders\IGameWallet;
use App\Helpers\Hlog;
use App\Helpers\UI;
use App\MUser;
use App\Services\KBonus\KBonus;

/**
 * @deprecated
 */
class DepositWagerBonus extends KUserBonus
{
//    use FsWagerActualize;
//    /**
//     * DepositWagerBonus constructor.
//     * Sets type of the bonus.
//     * @param array $attributes
//     */
//    public function __construct(array $attributes = [])
//    {
//        parent::__construct($attributes);
//        $this->type = static::TYPE_DEPOSIT_WAGER;
//    }
//
//    protected $guarded = ['type'];
//
//    public static function getSetting($setting, $default)
//    {
//        return BonusSettings::getProperty(self::TYPE_DEPOSIT_WAGER, $setting, $default);
//    }


    /**
     * Creates deposit bonus for given user.
     * @param MUser $user
     * @param int $wager
     * @param int $payment_multiplier
     * @return KUserBonus
     */
    public static function createForUser($user, $wager = 20, $payment_multiplier = 300)
    {
        $jparams['wager'] = $wager;
        $jparams['min_sum'] = 100;
        $jparams['max_sum'] = 3000;
        $jparams['multiplier'] = $payment_multiplier;
        return KUserBonus::fromParams(json_encode($jparams), $user->id, $user->currency);

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
//        $bonus->active_to = now()->addHours(72)->format('Y-m-d H:i:s');
//        $bonus->save();
//
//        return $bonus;
    }

//    public function createFromParams(string $params, int $user_id, ?int $currency = null, ?bool $keep_params = false, ?bool $tmp = false): DepositWagerBonus
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
//        $bonus->status = static::STATUS_WAITING_FOR_AGREEMENT;
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
//        $bonus->active_to = now()->addHours(72)->format('Y-m-d H:i:s');
//        $bonus->json_params = json_encode($params?? []);
//
//        if (!empty($params['enable_after'])) {
//            $bonus->enable_after = json_encode($params['enable_after']);
//        }
//
//        if (!$tmp) $bonus->save();
//        return $bonus;
//    }

//    public function getCurrencyParams(int $currency, array $bonus_params)
//    {
//        $params = BonusCurrencyConfig::getBonusConfig($currency, $this->type);
//
//        if ($params === null) {
//            Log::channel('stack')->warning('bonus currency params not found! Currency:' . $currency . ' Bonus id#' . $this->id);
//            return $bonus_params;
//        }
//
//        $bonus_params['min_sum'] = $bonus_params['min_sum'] * $params->min_deposit_k;
//        $bonus_params['max_sum'] = $bonus_params['max_sum'] * $params->max_deposit_k;
//        $bonus_params['wager'] = $bonus_params['wager'] * $params->wager_k;
//
//        return $bonus_params;
//    }

//    /**
//     * @return bool
//     */
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
//        BonusStatistic::saveStat($this, $this->user_id, BonusStatistic::ACTION_ACTIVATE,
//            $this->money_to_activate, self::STATUS_WAITING_FOR_AGREEMENT, $this->status);
//
//        return true;
//    }


//    public function confirmPayment(MUser $user, float $amount, int $currency): float
//    {
//        $rest = 0;
//        $start_amount = $amount;
//        //if refill sum is bigger then bonus max limit
//        if ($amount > $this->multiplier_to_complete) {
//            $rest = $amount - $this->multiplier_to_complete;
//            $amount = $this->multiplier_to_complete;
//        }
//
//        if ($this->isPaymentRequired()) {
//            $this->money_available = $amount;
//            //win limit payment sum X5
//            $this->wager_to_payout = $amount * 5;
//            $this->money_available = $this->money_available + ($this->money_available * $this->multiplier_to_payout / 100);
//            $this->wager_to_complete = $this->money_available * $this->multiplier;
//            $this->status = static::STATUS_ACTIVE;
//            $this->save();
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
//                self::STATUS_WAITING_FOR_PAYMENT, $this->status, $this->pattern_id, [
//                    'money_available' =>  $this->money_available,
//                    'wager_to_payout' =>  $this->wager_to_payout,
//                    'wager_to_complete' =>  $this->wager_to_complete,
//                ]);
//
//            return $rest;
//        }
//
//        Log::error('DepositWagerBonus error confirm payment');
//
//        return $start_amount;
//
//    }

    public function getName(): string
    {
        return __('bonuses.depBonusName');
    }

    public function getDescription(): string
    {
        switch ($this->status) {
            case self::STATUS_ACTIVE:
                return __('bonus.bonus_23_descr1');

            case self::STATUS_WAITING_FOR_AGREEMENT:
                return __('bonus.bonus_23_descr2', [
                    'amount' => $this->money_to_activate,
                    'curr' => $this->curr_name,
                    'prc' => ceil($this->multiplier_to_payout),
                    'wager' => $this->multiplier,
                ]);
//                return "После активации ваше пополнение на сумму от $this->money_to_activate $this->curr_name перейдет на бонусный счет,
//                 к нему будет добавлено ".ceil($this->multiplier_to_payout)."% от пополнения и включен вейджер Х$this->multiplier.";

            case self::STATUS_WAITING_FOR_PAYMENT:
                return __('bonus.bonus_23_descr3', [
                    'amount' => $this->money_to_activate,
                    'curr' => $this->curr_name,
                ]);
        }
        return __('bonus.bonus_23_descr2', [
            'amount' => $this->money_to_activate,
            'curr' => $this->curr_name,
            'prc' => ceil($this->multiplier_to_payout),
            'wager' => $this->multiplier,
        ]);
    }

    public function getDescriptionName(): ?string
    {
        return 'deposit';
    }

    public function getStatusString(IGameWallet $game_wallet = null): string
    {
        if ($this->status != static::STATUS_ACTIVE) return '';
        return UI::bonusPad(__('bonus.status_active'), $this->getProgressHint());
    }

    public function afterCreated(): void
    {
        if (empty($this->id)) {
            Hlog::logException(new \Exception('Trying attach bonus with no id to segment'));
            return;
        }
        parent::afterCreated();

        KBonus::getInstance()->addBonusToSegmentByName($this->getId(), $this->multiplier_to_payout."% к депозиту");
//        BonusSegment::attachOrCreateByName($this, $this->multiplier_to_payout."% к депозиту");
    }
}
