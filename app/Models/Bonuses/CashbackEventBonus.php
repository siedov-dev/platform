<?php

namespace App\Models\Bonuses;

/**
 * @deprecated
 */
class CashbackEventBonus extends KUserBonus
{
//    /**
//     * DepositBonus constructor.
//     * Sets a type of the bonus.
//     * @param array $attributes
//     */
//    public function __construct(array $attributes = [])
//    {
//        parent::__construct($attributes);
//        $this->type = static::TYPE_EVENT_CASH_BACK;
//    }
//
//
//    public function getTypeName()
//    {
//        return __('bonus.bonus_22_name');
//    }
//
//    public function getDescriptionName(): ?string
//    {
//        return 'active-cashback';
//    }
//
//    public static function getMinDepSum($currency)
//    {
//        BonusMenuParam::getLowestMinValue(CashbackEventBonus::TYPE_EVENT_CASH_BACK, $currency);
//    }
//
//    /**
//     * Creates cashback bonus for given user.
//     * @param MUser $user
//     * @param float $amount
//     * @param int $prc
//     * @return CashbackEventBonus
//     */
//    public static function createForUser(MUser $user, float $amount = 0, int $prc = 0): CashbackEventBonus
//    {
//        $bonus = new static();
//
//        $bonus->status = static::STATUS_PENDING;
//        $bonus->active_to = null;
//        $bonus->user_id = $user->id;
//        $bonus->hall_id = $user->hall_id;
//        $bonus->currency = $user->currency;
//        $bonus->spins_available = -1;
//        $bonus->money_available = 0;
//
//        if ($prc === 0) {
//            $params = BonusMenuParam::findInRangeParams(self::TYPE_EVENT_CASH_BACK, $user->currency, $amount);
//            if ($params !== null) $bonus->money_available = $amount * ($params->value / 100);
//
//        } else {
//            $bonus->money_available = floor($amount * ($prc / 100));
//        }
//
//        $bonus->save();
//
//        DB::table('payout_transactions')
//            ->insert([
//                'user_id' => $user->id,
//                'hall_id' => $user->hall_id,
//                'date' => date('Y-m-d H:i:s'),
//                'sum' => $bonus->money_available,
//                'status_id' => 35,
//                'currency' => UserWallet::currencyName($bonus->currency),
//                'operation' => 'CashBackEvent bonus created'
//            ]);
//
//        return $bonus;
//    }
//
//    public function createFromParams(string $params, int $user_id, ?int $currency = null, ?bool $keep_params = false, ?bool $tmp = false): CashbackEventBonus
//    {
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
//        $bonus->status = static::STATUS_PENDING;
//        $bonus->active_to = now()->addDays($params['lifetime'] ?? 2);
//        $bonus->user_id = $user_id ?? 0;
//        $bonus->hall_id = $hall_id;
//        $bonus->currency = $curr;
//        $bonus->spins_available = -1;
//        $bonus->money_available = $params['money'] ?? 0;
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
//    public static function cancelForUser(MUser $user)
//    {
//        //todo statistics
//        static::where('type', static::TYPE_EVENT_CASH_BACK)
//            ->where('user_id', $user->id)
//            ->where('status', static::STATUS_PENDING)
//            ->update(['status' => static::STATUS_CANCELED]);
//    }
//
//    public function getDescription(): string
//    {
//        return __('bonus.bonus_22_descr1', ['amount' => number_format($this->money_available, 2), 'curr' => UserWallet::currencyName($this->currency)]);
////        return 'При проигрыше, вам будет возвращено'.number_format($this->money_available, 2).' '.UserWallet::currencyName($this->currency).'
////        Бонус обновляется при очередном пополнении и обнуляется при выводе денег.';
//    }
//
//    public function actualize()
//    {
//        // Mark bonus as expired if the user hasn't activated bonus until the specified time.
//        if (!empty($this->active_to) && strtotime($this->active_to) < time()) {
//            $this->status = self::STATUS_EXPIRED;
//            BonusStatistic::saveStat($this, $this->user_id, BonusStatistic::ACTION_EXPIRED, $this->money_available, $this->status, self::STATUS_EXPIRED);
//        }
//    }
}
