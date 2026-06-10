<?php

namespace App\Models\Bonuses;

/**
 * @deprecated
 */
class SuperFreeSpinsBonus extends KUserBonus
{
//    /**
//     * DepositBonus constructor.
//     * Sets type of the bonus.
//     * @param array $attributes
//     */
//    public function __construct(array $attributes = [])
//    {
//        parent::__construct($attributes);
//        $this->type = static::TYPE_SUPER_FREE_SPINS;
//    }
//
//
//    protected $guarded = ['type'];
//
//
//    public static function getSetting($setting, $default)
//    {
//        return BonusSettings::getProperty(self::TYPE_SUPER_FREE_SPINS, $setting, $default);
//    }
//
//    /**
//     * Creates freespin bonus for given user.
//     * @param MUser $user
//     * @param int $spins
//     * @return SuperFreeSpinsBonus
//     */
//    public static function createForUser($user, $spins = 0)
//    {
//        if ($spins === 0) {
//            $spins = self::getSetting(self::SET_BONUS_SPINS, 20);
//        }
//
//        $jparams['wager'] = self::getSetting(self::SET_WAGER_TO_COMPLETE_MULTIPLIER, 20);
//        $jparams['money'] = self::getSetting(self::SET_MAX_BET, 100);
//        $jparams['freespins'] = $spins;
//
//        $bonus = new static();
//
//        $params = $bonus->getCurrencyParams($user->currency, $jparams);
//        $wager = $params['wager'];
//        $money = $params['money'];
//        $spins = $params['freespins'];
//
//        $bonus = new static();
//        $bonus->status = UserBonus::STATUS_ACTIVE;
//        $bonus->money_to_activate = self::getSetting(self::SET_MONEY_TO_ACTIVATE, 100);
//        $bonus->user_id = $user->id;
//        $bonus->hall_id = $user->hall_id;
//        $bonus->currency = $user->currency;
//        $bonus->spins_total = $spins;
//        $bonus->spins_available = $spins;
//        $bonus->money_available = $money;
//        $bonus->total_win = 0;
//        $bonus->wager_to_complete = self::getSetting(self::SET_WAGER_TO_COMPLETE_MULTIPLIER, 20);
//        $bonus->multiplier_to_complete = self::getSetting(self::SET_WAGER_TO_COMPLETE_MULTIPLIER, 20);
//        $bonus->wager_to_payout = self::getSetting(self::SET_WAGER_TO_PAYOUT_MULTIPLIER, 35);
//        $bonus->multiplier_to_payout = self::getSetting(self::SET_WAGER_TO_PAYOUT_MULTIPLIER, 35);
//
//        $d = new \DateTime();
//        $d->add(new \DateInterval('P5D'));
//        $bonus->active_to = $d->format('Y-m-d H:i:s');
//
//        $bonus->save();
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
//
//    public function activate(): bool
//    {
//        if ($this->status == self::STATUS_WAITING_FOR_AGREEMENT) {
//            $this->type = self::TYPE_SUPER_FREE_SPINS_PLAYBACK;
//            $this->status = self::STATUS_WAITING_FOR_PAYMENT;
//            BonusStatistic::saveStat($this, $this->user_id, BonusStatistic::ACTION_ACTIVATE, $this->money_to_activate,
//                self::STATUS_WAITING_FOR_AGREEMENT, $this->status);
//        }
//        if ($this->status == self::STATUS_NOT_ACTIVATED) {
//            DB::table('payout_transactions')
//                ->insert([
//                    'user_id' => Auth::id(),
//                    'hall_id' => UserWallet::currencyHallId($this->currency),
//                    'date' => date('Y-m-d H:i:s'),
//                    'sum' => $this->spins_available,
//                    'status_id' => 35,
//                    'currency' => UserWallet::currencyName($this->currency),
//                    'operation' => 'superfreespins bonus activated'
//                ]);
//            $this->status = self::STATUS_ACTIVE;
//            BonusStatistic::saveStat($this, $this->user_id, BonusStatistic::ACTION_ACTIVATE, $this->money_available,
//                self::STATUS_WAITING_FOR_AGREEMENT, $this->status);
//        }
//
//        if ($this->hours_active) {
//            $this->active_to = now()->addHours($this->hours_active)->format('Y-m-d H:i:s');
//        }
//
//        return $this->save();
//    }
//
//
//    public function actualize()
//    {
//        if ($this->isExpired()) return;
//
//        if ($this->spins_available <= 0) {
//            $this->spins_available = -1;
//            $this->type = self::TYPE_SUPER_FREE_SPINS_PLAYBACK;
//            $this->status = self::STATUS_WAITING_FOR_AGREEMENT;
//            $this->save();
//            BonusStatistic::saveStat($this, $this->user_id, BonusStatistic::ACTION_FS_DONE, $this->money_available,
//                self::STATUS_ACTIVE, self::STATUS_DONE);
//        }
//    }
//
//
//    public function getName(): string
//    {
//        return __('bonuses.wagerFSName');
//    }
//
//    public function getDescription(): string
//    {
//        return __('bonuses.wagerFSFullDesc', [
//            'count' => $this->spins_total,
//        ]);
//    }
//
//    public function getDescriptionName(): ?string
//    {
//        return 'super-free-spins';
//    }
//
//    public function isProgressAvailable(): bool
//    {
//        return $this->status == self::STATUS_ACTIVE;
//    }
//
//    public function getProgressHint(): string
//    {
//        $spinsAvailable = $this->spins_available > 0 ? $this->spins_available : 0;
//        return __('bonuses.wagerFSProgress', [
//            'spins' => $this->spins_total - $spinsAvailable,
//            'total' => $this->spins_total
//        ]);
//    }
//
//    public function getProgressPercents(): int
//    {
//        return $this->spins_available <= 0 ? 100 : (100 - round($this->spins_available / $this->spins_total * 100));
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
//        $spinsAvailable = $this->spins_available > 0 ? $this->spins_available : 0;
//        return __('bonus.bonus_pad', [
//            'spin' => ($this->spins_total - $spinsAvailable),
//            'spins' => $this->spins_total,
//            'max_bet' => 20,
//            'amount' => number_format($this->total_win, 2),
//            'curr' => UserWallet::currencyName($this->currency)
//        ]);
////        return 'Активен бонус. Макс ставка: 20 '.UserWallet::currencyName($this->currency).'. Прокрутов сделано: '.($this->spins_total - $spinsAvailable).'/'.$this->spins_total.' Выиграно: '.number_format($this->total_win, 2).' '.UserWallet::currencyName($this->currency);
//    }
//
//    public function actualizeVegas(string $game_id, IGameWallet $game_wallet, string $system = 'vegas')
//    {
//        log::info('actualizing freespins');
//        log::info($this->spins_available);
//        // Mark bonus as expired if user don't activated bonus until the specified time.
//        if ($this->status === self::STATUS_PENDING && strtotime($this->active_to) < time()) {
//            $this->status = self::STATUS_EXPIRED;
//            BonusStatistic::expired($this);
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
//            // Transform this bonus to the playback.
//            $this->type = self::TYPE_SUPER_FREE_SPINS_PLAYBACK;
//            $this->status = self::STATUS_WAITING_FOR_AGREEMENT;
//
//            $this->wager_to_complete = $this->money_available * $this->multiplier_to_complete;
//
//            BonusStatistic::saveStat($this, $this->user_id, BonusStatistic::ACTION_FS_DONE, $this->money_available,
//                self::STATUS_ACTIVE, self::STATUS_DONE);
//
//            Log::info('Vegas Супер-фриспины: все прокруты сделаны.');
//        }
//
//        if ($this->money_available <= 0) {
//            $this->money_available = 0;
//            $this->status = self::STATUS_DONE;
//
//            BonusStatistic::saveStat($this, $this->user_id, BonusStatistic::ACTION_FAIL, $this->money_available, self::STATUS_ACTIVE, self::STATUS_DONE);
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
//        }
//
//        return intval($this->money_available * 100);
//    }
//
//    public function depositCents(int $sum, IGameWallet $game_wallet, string $system, string $game_id): int
//    {
//        $this->total_win += $sum / 100;
//        return intval($this->money_available * 100);
//    }
}
