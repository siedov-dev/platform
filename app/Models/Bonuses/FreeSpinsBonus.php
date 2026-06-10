<?php

namespace App\Models\Bonuses;


/**
 * @deprecated
 */
class FreeSpinsBonus extends KUserBonus
{
//    /**
//     * DepositBonus constructor.
//     * Sets type of the bonus.
//     * @param array $attributes
//     */
//    public function __construct(array $attributes = [])
//    {
//        parent::__construct($attributes);
//        $this->type = static::TYPE_FREE_SPINS;
//    }
//
//
//    protected $guarded = ['type'];
//
//
//    public static function getSetting($setting, $default)
//    {
//        return BonusSettings::getProperty(self::TYPE_FREE_SPINS, $setting, $default);
//    }
//
//    /**
//     * Creates deposit bonus for given user.
//     * @param MUser $user
//     * @param int $spins
//     * @return FreeSpinsBonus
//     */
//    public static function createForUser($user, $spins = 0)
//    {
//        if ($spins === 0) {
//            $spins = self::getSetting(self::SET_BONUS_SPINS, 500);
//        }
//
//        $jparams['freespins'] = $spins;
//        $jparams['money'] = self::getSetting(self::SET_MAX_BET, 50);
//
//        $bonus = new static();
//
//        $params = $bonus->getCurrencyParams($user->currency, $jparams);
//        $spins = $params['freespins'];
//        $money = $params['money'];
//
//        $bonus = new static();
//        $bonus->user_id = $user->id;
//        $bonus->hall_id = $user->hall_id;
//        $bonus->currency = $user->currency;
//        $bonus->spins_available = $spins;
//        $bonus->spins_total = $spins;
//        $bonus->money_available = $money;
//        $bonus->wager_to_complete = self::getSetting(self::SET_WAGER_TO_COMPLETE_MULTIPLIER, 35);
//        $bonus->wager_to_payout = self::getSetting(self::SET_WAGER_TO_PAYOUT_MULTIPLIER, 35);
//
//        $d = new \DateTime();
//        $d->add(new \DateInterval('P5D'));
//        $bonus->active_to = $d->format('Y-m-d H:i:s');
//
//        $bonus->save();
//
//        return $bonus;
//    }
//
//    public function createFromParams(string $params, int $user_id, ?int $currency = null, ?bool $keep_params = false, ?bool $tmp = false): FreeSpinsBonus
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
//        $spins = $params['freespins'];
//        $money = $params['money'];
//
//        $bonus = new static();
//        $bonus->user_id = $user?->id;
//        $bonus->hall_id = $hall_id;
//        $bonus->currency = $curr;
//        $bonus->spins_available = $spins;
//        $bonus->spins_total = $spins;
//        $bonus->money_available = $money;
//        $bonus->wager_to_complete = self::getSetting(self::SET_WAGER_TO_COMPLETE_MULTIPLIER, 35);
//        $bonus->wager_to_payout = self::getSetting(self::SET_WAGER_TO_PAYOUT_MULTIPLIER, 35);
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
//
//    public function wagerDiminution($percent)
//    {
//        $prc = 1 - ($percent / 100);
//        $this->wager_to_complete = floor($this->wager_to_complete * $prc);
//        $this->wager_to_payout = floor($this->wager_to_payout * $prc);
//        $this->save();
//        return $this;
//    }
//
//    public function getName(): string
//    {
//        return __('bonuses.freeSpinsName');
//    }
//
//    public function getDescription(): string
//    {
//        return __('bonuses.freeSpinsDesc', ['count' => $this->spins_total]);
//    }
//
//    public function isProgressAvailable(): bool
//    {
//        return $this->status == self::STATUS_ACTIVE;
//    }
//
//    public function getProgressHint(): string
//    {
//        if ($this->spins_available > 0) {
//            $spinsAvailable = $this->spins_available > 0 ? $this->spins_available : 0;
//            return __('bonuses.freeSpinsProgressSpins', [
//                'spins' => $this->spins_total - $spinsAvailable,
//                'total' => $this->spins_total
//            ]);
//        } else {
//            return __('bonuses.freeSpinsProgressBets', [
//                'bets' => round($this->total_bet),
//                'total' => round($this->wager_to_complete)
//            ]);
//        }
//    }
//
//    public function getProgressPercents(): int
//    {
//        if ($this->spins_available > 0) {
//            return $this->spins_available <= 0 ? 100 : (100 - round($this->spins_available / $this->spins_total * 100));
//        }
//        return round($this->total_bet / $this->wager_to_complete * 100);
//    }
//
//    public function activate(): bool
//    {
//        $dt = new \DateTime();
//        $this->status = UserBonus::STATUS_ACTIVE;
//        //не надо это отключать!
//        if($this->hours_active) {
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
//                'operation' => $this->getName().' drummatik bonus activated'
//            ]);
//
//        BonusStatistic::saveStat($this, $this->user_id, BonusStatistic::ACTION_ACTIVATE, $this->money_available,
//            self::STATUS_NOT_ACTIVATED, $this->status);
//
//        return true;
//    }
//
//    public function getStatusString(IGameWallet $game_wallet = null): string
//    {
//        if($this->status != static::STATUS_ACTIVE) return '';
//        return UI::bonusPad(__('bonus.status_active'), $this->getProgressHint());
//    }
}
