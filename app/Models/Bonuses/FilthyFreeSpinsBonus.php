<?php


namespace App\Models\Bonuses;


/**
 * @deprecated
 */
class FilthyFreeSpinsBonus extends SuperFreeSpinsBonus
{
//
//    /**
//     * FilthyFreeSpinsBonus constructor.
//     * @param array $attributes
//     */
//    public function __construct(array $attributes = [])
//    {
//        parent::__construct($attributes);
//        $this->type = static::TYPE_FILTHY_FS;
//    }
//
//    protected $guarded = ['type'];
//
//
//    public static function getSetting($setting, $default)
//    {
//        return BonusSettings::getProperty(static::TYPE_FILTHY_FS, $setting, $default);
//    }
//
//    /**
//     * @param MUser $user
//     * @param int $spins
//     * @param int $wager
//     * @param int $lifetime in days
//     * @param float $money
//     * @return SuperFreeSpinsBonus|static
//     */
//    public static function createForUser($user, $spins = 100, int $wager = 20, int $lifetime = 14, float $money = 100)
//    {
//        if ($spins === 0) {
//            $spins = self::getSetting(self::TYPE_FILTHY_FS, 100);
//        }
//
//        $jparams['wager'] = $wager;
//        $jparams['freespins'] = $spins;
//        $jparams['win_limit'] = 0;
//        $jparams['money'] = $money;
//
//        $bonus = new static();
//
//        $params = $bonus->getCurrencyParams($user->currency, $jparams);
//        $wager = $params['wager'];
//        $spins = $params['freespins'];
//        $money = $params['money'];
//
//        $bonus = new static();
//        $bonus->status = UserBonus::STATUS_NOT_ACTIVATED;
//        $bonus->money_to_activate = 0;
//        $bonus->user_id = $user->id;
//        $bonus->hall_id = $user->hall_id;
//        $bonus->currency = $user->currency;
//        $bonus->spins_total = $spins;
//        $bonus->spins_available = $spins;
//        $bonus->money_available = $money;
//        $bonus->total_win = 0;
//        $bonus->wager_to_complete = 0;
//        $bonus->multiplier_to_complete = 0;
//        $bonus->wager_to_payout = $wager;
//        $bonus->multiplier_to_payout = 0;
//        $bonus->hours_active = $lifetime;
//
//        $bonus->active_to = now()->addDays($lifetime)->format('Y-m-d H:i:s');
//
//        $bonus->save();
//
//        return $bonus;
//    }
//
//    public function wagerDiminution($percent)
//    {
//        $prc = 1 - ($percent / 100);
//        $this->wager_to_payout = floor($this->wager_to_payout * $prc);
//        $this->save();
//        return $this;
//    }
//
//    public function createFromParams(string $params, int $user_id, ?int $currency = null, ?bool $keep_params = false, ?bool $tmp = false): FilthyFreeSpinsBonus
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
//        $bonus->status = $params['status'] ?? UserBonus::STATUS_NOT_ACTIVATED;
//        $bonus->user_id = $user_id;
//        $bonus->hall_id = $hall_id;
//        $bonus->currency = $curr;
//        $bonus->spins_total = $params['freespins'];
//        $bonus->spins_available = $params['freespins'];
//        $bonus->money_available = $params['money'];
//        $bonus->total_win = 0;
//        $bonus->wager_to_complete = 0;
//        $bonus->multiplier_to_complete = $params['win_limit'] ?? 0;
//        $bonus->wager_to_payout = $params['wager'];
//        $bonus->money_to_activate = $params['activation_sum'] ?? 0;
//        $bonus->multiplier_to_payout = $params['activate_wager'] ?? 0;
//        $bonus->json_params = json_encode($params?? []);
//
//
//        $bonus->hours_active = isset($params['lifetime']) ? $params['lifetime'] : 14;
//
//        $bonus->active_to = now()->addDays($bonus->hours_active)->format('Y-m-d H:i:s');
//
//        if (!empty($params['bonus_prizes'])) {
//            $bonus->bonus_prizes = json_encode($params['bonus_prizes']);
//        }
//
//        if (!empty($params['enable_after'])) {
//            $bonus->enable_after = json_encode($params['enable_after']);
//        }
//
//        if (!$tmp) $bonus->save();
//        return $bonus;
//    }
//
//    public function activate(): bool
//    {
//        if ($this->status == self::STATUS_NOT_ACTIVATED && $this->money_to_activate > 0) {
//            $this->status = self::STATUS_WAITING_FOR_PAYMENT;
//            BonusStatistic::saveStat($this, $this->user_id, BonusStatistic::ACTION_ACTIVATE, $this->money_to_activate,
//                self::STATUS_WAITING_FOR_AGREEMENT, $this->status);
//        }
//
//        if ($this->status == self::STATUS_WAITING_FOR_AGREEMENT) {
//            $this->type = self::TYPE_FILTHY_FS_WASH_OFF;
//            $this->status = self::STATUS_FILTHY;
//            BonusStatistic::saveStat($this, $this->user_id, BonusStatistic::ACTION_ACTIVATE, $this->money_available,
//                self::STATUS_WAITING_FOR_AGREEMENT, $this->status);
//        }
//
//        if ($this->status == self::STATUS_NOT_ACTIVATED) {
//            DB::table('payout_transactions')
//                ->insert([
//                    'user_id' => Auth::id(),
//                    'hall_id' => UserWallet::currencyHallId($this->currency),
//                    'date' => date('Y-m-d H:i:s'),
//                    'sum' => $this->spins_available,
//                    'status_id' => 35,
//                    'currency' => UserWallet::currencyName($this->currency),
//                    'operation' => 'filthy freespins bonus activated'
//                ]);
//            $this->status = self::STATUS_ACTIVE;
//            BonusStatistic::saveStat($this, $this->user_id, BonusStatistic::ACTION_ACTIVATE, $this->money_available, self::STATUS_NOT_ACTIVATED, $this->status);
//        }
//
//        if ($this->hours_active) {
//            $this->active_to = now()->addHours(72)->format('Y-m-d H:i:s');
//        }
//
//        return $this->save();
//    }
//
//    public function confirmPayment(MUser $user, float $amount, int $currency): float
//    {
//        try {
//            if ($this->status == self::STATUS_WAITING_FOR_PAYMENT) {
//                if ($amount >= $this->money_to_activate) {
//                    BonusStatistic::saveStat($this, $this->user_id, BonusStatistic::ACTION_DEPOSIT, $this->money_to_activate,
//                        self::STATUS_NOT_ACTIVATED, $this->status);
//                    DB::table('payout_transactions')
//                        ->insert([
//                            'user_id' => Auth::id(),
//                            'hall_id' => UserWallet::currencyHallId($this->currency),
//                            'date' => date('Y-m-d H:i:s'),
//                            'sum' => $this->spins_available,
//                            'status_id' => PayoutTransaction::STATUS_BONUS_ACTIVATED,
//                            'currency' => UserWallet::currencyName($this->currency),
//                            'operation' => 'filthy freespins bonus activated'
//                        ]);
//                    $this->status = self::STATUS_ACTIVE;
//                    BonusStatistic::saveStat($this, $this->user_id, BonusStatistic::ACTION_ACTIVATE, $this->money_available,
//                        self::STATUS_NOT_ACTIVATED, $this->status);
//                }
//
//                if ($this->save()) {
//                    if ($this->multiplier_to_payout > 0) {
//                        $user->getWallet($this->currency)->addLockedMoney($this->money_to_activate);
//                    }
//                }
//            }
//        } catch (\Exception $e) {
//            Log::error($e->getMessage());
//        }
//        return $amount;
//    }
//
//    public function actualize()
//    {
//        if ($this->isExpired()) return;
//
//        if ($this->spins_available <= 0) {
//            $this->spins_available = -1;
//            $this->type = self::TYPE_FILTHY_FS_WASH_OFF;
//            $this->status = self::STATUS_WAITING_FOR_AGREEMENT;
//            $this->save();
//        }
//    }
//
//    public function getName(): string
//    {
//        return 'Filthy FreeSpins';
//    }
//
//
//    public function getDescription(): string
//    {
//        if ($this->status == self::STATUS_WAITING_FOR_PAYMENT) {
//            //deposit required
//            return __('bonus.bonus_21_descr2', ['amount' => $this->money_to_activate, 'curr' => $this->curr_name]);
//        }
//        return __('bonus.bonus_14_descr1', ['spins' => $this->spins_total]);
////        return "Вам предоставлено $this->spins_total бесплатных вращений.
////            После прокрутов бесплатных вращений \"можно отыграть\" просто играя в игры DrumMatic, NetEnt и др.";
//    }
//
//    public function getDescriptionName(): ?string
//    {
//        return 'filthy-free-spins';
//    }
//
//    public function isVegasAllowed(): bool
//    {
//        return true;
//    }
//
//    public function actualizeVegas(string $game_id, IGameWallet $game_wallet, string $system = 'vegas')
//    {
//        log::info('actualizing filthy freespins #'.$this->id);
//        log::info($this->spins_available);
//        // Mark bonus as expired if user don't activated bonus until the specified time.
//        if ($this->status === self::STATUS_PENDING && strtotime($this->active_to) < time()) {
//            $this->status = self::STATUS_EXPIRED;
//            BonusStatistic::saveStat($this, $this->user_id, BonusStatistic::ACTION_EXPIRED, $this->money_available, $this->status, self::STATUS_EXPIRED);
//            return;
//        }
//
//        // Do nothing for inactive bonus.
//        if ($this->status != static::STATUS_ACTIVE) return;
//
//        if ($this->spins_available == 0) {
//            $this->spins_available = -1;
//            $this->money_available = $this->total_win;
//            $this->total_win = 0;
//            $this->total_bet = 0;
//            $this->total_bet_for_payout = 0;
//
//            if ($this->multiplier_to_complete > 0 && $this->money_available > $this->multiplier_to_complete) $this->money_available = $this->multiplier_to_complete;
//
//            // Transform this bonus to the playback.
//            $this->type = self::TYPE_FILTHY_FS_WASH_OFF;
//            $this->status = self::STATUS_WAITING_FOR_AGREEMENT;
//
//            $this->wager_to_complete = $this->money_available * $this->wager_to_payout;
//            BonusStatistic::saveStat($this, $this->user_id, BonusStatistic::ACTION_DONE, $this->money_available, self::STATUS_ACTIVE, self::STATUS_DONE);
//            Redis::set("bonus-done-$this->user_id", __('bonus.filthy_fs_done', [
//                'curr_fs' => 0,
//                'total_fs' => $this->spins_total,
//                'win_sum' => number_format($this->money_available, 2, ".", ""),
//                'curr' => $this->curr_name,
//            ]));
//
//            Log::info('Vegas Filthy-фриспины: все прокруты сделаны. #'.$this->id);
//        }
//
//        if ($this->money_available <= 0) {
//            $this->money_available = 0;
//            $this->status = self::STATUS_DONE;
//            BonusStatistic::saveStat($this, $this->user_id, BonusStatistic::ACTION_FAIL, $this->money_available,
//                self::STATUS_ACTIVE, self::STATUS_DONE);
//
//            Redis::set("bonus-done-$this->user_id", __('bonus.filthy_fs_done', [
//                'curr_fs' => 0,
//                'total_fs' => $this->spins_total,
//                'win_sum' => number_format($this->money_available, 2, ".", ""),
//                'curr' => $this->curr_name,
//            ]));
//        }
//    }
}
