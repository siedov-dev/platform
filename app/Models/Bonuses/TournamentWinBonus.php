<?php


namespace App\Models\Bonuses;

/**
 * based on NonDepWagerBonus
 * @deprecated
 */
class TournamentWinBonus extends KUserBonus
{
//    public function __construct(array $attributes = [])
//    {
//        parent::__construct($attributes);
//        $this->type = static::TYPE_TOURNAMENT_WIN;
//    }
//
//    public static function getSetting($setting, $default)
//    {
//        return BonusSettings::getProperty(self::TYPE_TOURNAMENT_WIN, $setting, $default);
//    }
//
//
//    public function getName(): string
//    {
//        return __('bonus.tournament_win');
//    }
//
//    public function getDescriptionName(): ?string
//    {
//        return 'tournament-win';
//    }
//
//    public function getDescription(): string
//    {
//        return __('bonus.tournament_win');
//    }
//
//    /**
//     * Creates deposit bonus for given user.
//     * @param MUser $user
//     * @param int $money
//     * @param int $wager
//     * @param int $max_win_k
//     * @param int $freespins
//     * @return TournamentWinBonus
//     */
//    public static function createForUser($user, int $money = 100, $wager = 10, $max_win_k = 10, $freespins = -1, $currency = null)
//    {
//        $jparams['wager'] = $wager;
//        $jparams['money'] = $money;
//        $jparams['max_win_k'] = $max_win_k;
//        $jparams['freespins'] = $freespins;
//
//        $bonus = new static();
//        $bcurrency = $currency?? $user->currency;
//
//        if ($currency === null) {
//            $params = $bonus->getCurrencyParams($bcurrency, $jparams);
//        } else {
//            $params = $jparams;
//        }
//        $wager = $params['wager'];
//        $money = $params['money'];
//        $max_win_k = $params['max_win_k'];
//        $freespins = $params['freespins'];
//
//        $bonus->status = static::STATUS_WAITING_FOR_AGREEMENT;
//        $bonus->user_id = $user->id;
//        $bonus->hall_id = UserWallet::currencyHallId($bcurrency);
//        $bonus->currency = $bcurrency;
//        $bonus->spins_available = $freespins;
//        $bonus->spins_total = $freespins;
//        $bonus->money_available = $money;
//        $bonus->money_to_activate = 0;
//        //max win koef
//        $bonus->multiplier_to_complete = $max_win_k;
//        //wager koef
//        $bonus->multiplier = $wager;
//        $bonus->multiplier_to_payout = 0;
//        //max_win
//        $bonus->wager_to_payout = $money * $max_win_k;
//        $bonus->wager_to_complete = $wager * $money;
//        $bonus->active_to = now()->addHours(72)->format('Y-m-d H:i:s');
//        $bonus->save();
//
//        return $bonus;
//    }
//
//    public function wagerDiminution($percent)
//    {
//        $prc = 1 - ($percent / 100);
//        $this->multiplier = floor($this->multiplier * $prc);
//        $this->save();
//        return $this;
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
//        $bonus->status = static::STATUS_WAITING_FOR_AGREEMENT;
//        $bonus->user_id = $user_id;
//        $bonus->hall_id = $hall_id;
//        $bonus->currency = $curr;
//        $bonus->spins_available = $params['freespins'] ?? -1;
//        $bonus->spins_total = $params['freespins'] ?? -1;
//        $bonus->money_available = $params['money'] ?? 100;
//        //max win koef
//        $bonus->multiplier_to_complete = $params['max_win_k'] ?? 10;
//        //wager koef
//        $bonus->multiplier = $params['wager'] ?? 10;
//        $bonus->wager_to_payout = $this->money_available * ($params['max_win_k'] ?? 10);
//        $bonus->wager_to_complete = $bonus->multiplier * $bonus->money_available;
//        $bonus->active_to = now()->addHours($params['hours_active'] ?? 72)->format('Y-m-d H:i:s');
//        $bonus->money_to_activate = $params['activate_sum'] ?? 0;
//        $bonus->multiplier_to_payout = $params['activate_wager'] ?? 0;
//        $bonus->json_params = json_encode($params?? []);
//
//        //if max win sum is set max win koef disabled
//        if (!empty($params['max_win_sum']) && $params['max_win_sum'] > 0) {
//            $bonus->multiplier_to_complete = 0;
//            $bonus->wager_to_payout = $params['max_win_sum'];
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
//    /**
//     * @return bool
//     */
//    public function activate(): bool
//    {
//        /**
//         * @var MUser $user
//         */
//        $user = MUser::find(Auth::id());
//
//        if (!$user->isEmailVerified()) {
//            $this->_error = __('bonus.bonus_26_email_required');
//            return false;
//        }
//
//        if ($this->money_to_activate > 0) {
//            $this->status = self::STATUS_WAITING_FOR_PAYMENT;
//            BonusStatistic::saveStat($this, $this->user_id, BonusStatistic::ACTION_ACTIVATE, $this->money_to_activate,
//                self::STATUS_WAITING_FOR_AGREEMENT, $this->status);
//        } elseif ($this->status == self::STATUS_WAITING_FOR_AGREEMENT || $this->status == self::STATUS_NOT_ACTIVATED) {
//            $this->status = self::STATUS_ACTIVE;
//            BonusStatistic::saveStat($this, $this->user_id, BonusStatistic::ACTION_ACTIVATE, $this->money_available,
//                self::STATUS_WAITING_FOR_AGREEMENT, $this->status);
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
//                'operation' => $this->getName() . ' bonus activated'
//            ]);
//
//        $this->save();
//
//        return true;
//    }
//
//
//    public function confirmPayment(MUser $user, float $amount, int $currency): float
//    {
//        try {
//            if ($this->status == self::STATUS_WAITING_FOR_PAYMENT) {
//                if ($amount >= $this->money_to_activate) {
//                    $this->status = self::STATUS_ACTIVE;
//                }
//
//                if ($this->save()) {
//                    if ($this->multiplier_to_payout > 0) {
//                        $user->getWallet($this->currency)->addLockedMoney($this->money_to_activate);
//                    }
//                }
//            }
//            BonusStatistic::saveStat($this, $this->user_id, BonusStatistic::ACTION_DEPOSIT, $amount,
//                self::STATUS_WAITING_FOR_PAYMENT, $this->status, $this->pattern_id);
//
//        } catch (\Exception $e) {
//            Log::error($e->getMessage());
//        }
//        return $amount;
//    }
//
//    /**
//     * bonus doesn't have money to payout
//     * @return float
//     */
//    public function getMoneyToPayout(): float
//    {
//        return 0;
//    }
//
//    public function isProgressAvailable(): bool
//    {
//        return $this->status == self::STATUS_ACTIVE;
//    }
//
//    public function getProgressPercents(): int
//    {
//        if ($this->spins_total > 0 && $this->spins_available > 0) {
//            $p = floor(($this->spins_total - $this->spins_available) / $this->spins_total * 100);
//            if ($p > 100) $p = 100;
//            return $p;
//        }
//        $p = floor($this->total_bet / $this->wager_to_complete * 100);
//        if ($p > 100) $p = 100;
//        return $p;
//    }
//
//    public function getProgressHint(): string
//    {
//        if ($this->isProgressAvailable()) {
//            if ($this->spins_total > 0 && $this->spins_available > 0) {
//                return __('bonus.bonus_26_played') . ($this->spins_total - $this->spins_available) . " / " . ($this->spins_total);
//            }
//            return __('bonus.bonus_26_played') . floor($this->total_bet) . " / " . floor($this->wager_to_complete);
//        }
//        return '';
//    }
//
//    public function getTotalMoneyWon(): float
//    {
//        if (!$this->isDone()) return 0;
//        return $this->money_available + $this->money_paid_out;
//    }
//
//    public function actualize()
//    {
//        if ($this->isExpired()) return;
//
//        if ($this->money_available <= 0 && $this->spins_available < 0) {
//            $this->money_available = 0;
//            $this->status = self::STATUS_DONE;
//
//            try {
//                BonusStatistic::saveStat($this, $this->user_id, BonusStatistic::ACTION_FAIL,
//                    $this->money_available, self::STATUS_ACTIVE, self::STATUS_DONE);
//            } catch (\Exception $e) {
//                Log::error($e->getMessage());
//            }
//        }
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
//    public function actualizeVegas(string $game_id, IGameWallet $game_wallet, string $system = 'vegas')
//    {
//        // Mark bonus as expired if user don't activated bonus until the specified time.
//        if ($this->status === self::STATUS_PENDING && strtotime($this->active_to) < time()) {
//            $this->status = self::STATUS_EXPIRED;
//            BonusStatistic::saveStat($this, $this->user_id, BonusStatistic::ACTION_EXPIRED, $this->money_available, $this->status, self::STATUS_EXPIRED);
//            return;
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
//            $money = $this->money_available;
//            if ($money > $this->wager_to_payout) $money = $this->wager_to_payout;
//            $this->money_available = 0;
//            $this->spins_available = -1;
//            $this->money_paid_out = $money;
//
//            $game_wallet->addMoney($money);
//
//            $user = MUser::find($this->user_id);
//            try {
//                BonusWin::userWin($user, $this->money_paid_out, $game_id, $this->currency, $system);
//                BonusStatistic::saveStat($this, $this->user_id, BonusStatistic::ACTION_WIN, $this->money_paid_out, self::STATUS_ACTIVE, self::STATUS_DONE);
//            } catch (\Exception $e) {
//                Log::error($e->getMessage());
//            }
//
//            $this->writeTransaction($this->money_paid_out);
//
//            $this->status = self::STATUS_DONE;
//
//            return;
//        }
//
//        if ($this->money_available <= 0 && $this->spins_available < 0) {
//            $this->money_available = 0;
//            $this->status = self::STATUS_DONE;
//            // call actualize in 5+ seconds
//            ActualizeBonusJob::dispatch($this->getId())->delay(now()->addSeconds(5));
//        }
//    }
//
//    public function depositCents(int $sum, IGameWallet $game_wallet, string $system, string $game_id): int
//    {
////        $this->money_available += ($sum / 100);
//        if ($this->spins_available > 0) {
//            $this->total_win += $sum / 100;
//        } else {
//            $this->money_available += ($sum / 100);
//        }
//        return intval($this->money_available * 100);
//    }
//
//    public function withdrawCents(int $sum, IGameWallet $game_wallet, string $system, string $game_id)
//    {
//        if ($this->spins_available > 0) {
//            $this->spins_available--;
//            if ($this->spins_available <= 0) {
//                $this->spins_available = -1;
//                $this->money_available += $this->total_win;
//                //if max win koef is set
//                if ($this->multiplier_to_complete > 0) {
//                    $this->wager_to_payout = $this->money_available * $this->multiplier_to_complete;
//                }
//                $this->wager_to_complete = $this->multiplier * $this->money_available;
//            }
//            return;
//        }
//        $this->money_available -= $sum / 100;
//        $this->total_bet += $sum / 100;
//    }
//
//    private function writeTransaction($amount)
//    {
//
//        $user = MUser::find($this->user_id);
//
//        \Illuminate\Support\Facades\DB::table('payout_transactions')
//            ->insert([
//                'user_id' => $user->id,
//                'hall_id' => $user->hall_id,
//                'date' => date('Y-m-d H:i:s'),
//                'sum' => $amount,
//                'status_id' => 35,
//                'currency' => UserWallet::currencyName($this->currency),
//                'operation' => 'Выигрыш на бонусе Пополнения (id = \' . $this->id . \')'
//            ]);
//    }
}
