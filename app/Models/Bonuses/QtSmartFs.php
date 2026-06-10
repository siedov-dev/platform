<?php

namespace App\Models\Bonuses;

use App\GamesProviders\IGameWallet;
use App\GamesProviders\Qt\QtGamesProviderApi;
use App\Helpers\Hlog;
use App\Helpers\UI;
use App\Jobs\ActualizeBonusJob;
use App\Models\Bonuses\Segments\BonusSegment;
use App\Models\BonusPatterns;
use App\Models\BonusStatistic;
use App\Models\BonusWagerPrc;
use App\Models\BonusWin;
use App\MUser;
use App\UserWallet;
use App\Wallet;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;

/**
 * @deprecated
 */
class QtSmartFs extends UserBonus
{
    protected $guarded = ['type'];
    protected $_api;

    public function __construct(array $attributes = [])
    {
        $this->_api = new QtGamesProviderApi();
        parent::__construct($attributes);
        $this->type = static::TYPE_QT_SMART_FS;
        if ($this->currency !== null) $this->curr_name = UserWallet::currencyName($this->currency);
    }

    /**
     * Creates bonus for given user.
     * @param MUser $user
     * @param int $spins
     * @param float $money
     * @param int $wager
     * @return PPlayFreeSpins
     */
    public static function createForUser(MUser $user, float $spins_amount = 0, float $money = 0, int $wager = 0): self
    {
        $bonus = new static();
        $bonus->user_id = $user->id;
        $bonus->hall_id = $user->hall_id;
        $bonus->currency = $user->currency;
        $bonus->spins_available = $spins_amount > 0 ? 1 : -1;
        $bonus->spins_total = 0;
        $bonus->multiplier_to_complete = $spins_amount;
        $bonus->status = static::STATUS_NOT_ACTIVATED;
        $bonus->money_available = $money;
        $bonus->money_to_activate = 0;
        $bonus->wager_to_complete = $money * $wager;
        $bonus->wager_to_payout = $money;
        $bonus->hours_active = 24;
        $bonus->multiplier = $wager;
        $bonus->max_win = $money;
        $bonus->active_to = now()->addHours(72)->format('Y-m-d H:i:s');
        $bonus->save();

        return $bonus;
    }

    public function wagerDiminution($percent)
    {
        $prc = 1 - ($percent / 100);
        $this->multiplier = floor($this->multiplier * $prc);
        $this->wager_to_complete = $this->multiplier * $this->money_available;
        $this->save();
        return $this;
    }

    public function createFromParams(string $params, int $user_id, ?int $currency = null, ?bool $keep_params = false, ?bool $tmp = false): self
    {
        $jparams = json_decode($params, true);
        /** @var MUser $user */
        $user = null;
        if ($user_id === 0 && !$tmp) {
            throw new \Exception('User id is not specified for non temp bonus. Params: ' . $params);
        }
        if ($user_id !== 0) {
            $user = MUser::find($user_id);
        }
        $params = $jparams;

        if (isset($jparams['currency'])) {
            $curr = Wallet::getCurrencyCode($jparams['currency']);
        } else {
            $curr = $currency ?? $user?->currency?? Wallet::CURRENCY_EUR;
            if (!$keep_params) {
                $params = $this->getCurrencyParams($curr, $jparams);
            }
        }
        $hall_id = Wallet::currencyHallId($curr);

        $money = $params['money']?? 0;
        $wager = $params['wager'];

        $bonus = new static();
        $bonus->json_params = json_encode($params);
        $bonus->user_id = $user?->id;
        $bonus->hall_id = $hall_id;
        $bonus->currency = $curr;
        $bonus->spins_available = 1;
        $bonus->total_bet_for_payout = $params['spins_amount'];// fs amount
        $bonus->spins_total = 0;
        $bonus->status = static::STATUS_NOT_ACTIVATED;
        $bonus->money_available = $money;
        $bonus->wager_to_complete = $money * $wager;
        //minimal refill sum
        $bonus->money_to_activate = $params['min_sum'];
        //max refill sum
        $bonus->multiplier_to_complete = $params['max_sum'];
        //wager koef
        $bonus->multiplier = $params['wager'];
        $bonus->multiplier_to_payout = $params['multiplier'];
        $bonus->hours_active = 24;
        $bonus->multiplier = $wager;
        $bonus->max_win = $params['max_win']?? 0;
        $bonus->active_to = now()->addDays($params['days_active'] ?? 5)->format('Y-m-d H:i:s');
        $bonus->json_params = json_encode($params?? []);

        if (!empty($params['bonus_prizes'])) {
            $bonus->bonus_prizes = json_encode($params['bonus_prizes']);
        }

        if (!empty($params['enable_after'])) {
            $bonus->enable_after = json_encode($params['enable_after']);
        }

        if (!$tmp) $bonus->save();
        return $bonus;
    }

    public function getProgressHint(): string
    {
        if ($this->spins_available > 0) {
            return __('bonus.fs_active');
        }
        if ($this->isProgressAvailable()) {
            return __('bonus.played') . number_format($this->total_bet, 2, '.', '') . '/' . number_format($this->wager_to_complete, 2, '.', '');
        }
        return '';
    }

    public function getProgressPercents(): int
    {
        if ($this->spins_total > 0) {
            return $this->spins_available <= 0 ? 100 : (100 - round($this->spins_available / $this->spins_total * 100));
        }
        $p = $this->total_bet / $this->wager_to_complete * 100;
        if ($p > 100) $p = 100;
        return $p;
    }

    public function getName(): string
    {
        return __('bonus.bonus_33_name');
    }

    public function getDescription(): string
    {
        switch ($this->status) {
            case self::STATUS_ACTIVE:
                return __('bonus.bonus_33_descr1');

            case self::STATUS_WAITING_FOR_PAYMENT:
                return __('bonus.bonus_33_descr2', ['amount' => $this->money_to_activate, 'curr' => $this->curr_name]);
        }

        return __('bonus.bonus_33_descr3');
    }

    public function getDescriptionName(): ?string
    {
        if ($this->pattern_id == BonusPatterns::PTN_FIRST_DEP_BONUS_SW) return 'first-deposit-sw';
        if ($this->pattern_id == BonusPatterns::PTN_SECOND_DEP_BONUS_SW) return 'second-deposit-sw';
        if ($this->pattern_id == BonusPatterns::PTN_THIRD_DEP_BONUS_SW) return 'third-deposit-sw';
        return 'qt-smart-wager';
    }

    public function cancelBonus()
    {
        $this->_api->deleteFreeRounds($this->user_id ,$this->getJsonParam('qt_bonus_id'));
        parent::cancelBonus();
    }

    public function actualize()
    {
        if ($this->isExpired()) return;

        // Mark bonus as expired if user don't activated bonus until the specified time.
        if ($this->status == self::STATUS_ACTIVE && strtotime($this->active_to) < time()) {
            $this->status = self::STATUS_EXPIRED;
            BonusStatistic::expired($this);
            $this->save();
            $this->_api->deleteFreeRounds($this->user_id ,$this->getJsonParam('qt_bonus_id'));
        }

        // Do nothing for inactive bonus.
        if ($this->status != self::STATUS_ACTIVE) return;

        // Complete bonus and transfer money if user won necessary amount of money.
        if ($this->spins_available < 0 && $this->total_bet >= $this->wager_to_complete) {
            if ($this->money_available > $this->max_win) {
                $this->money_available = $this->max_win;
            }
            $this->spins_available = -1;
            $this->money_paid_out = $this->money_available;
            $this->writeTransaction($this->money_available);

            /** @var MUser $user */
            $user = MUser::find($this->user_id);
            if ($user) {
                $user->getWallet($this->currency)->addMoney($this->money_available)->save();
            }

            $this->money_available = 0;
            $this->status = self::STATUS_DONE;
            $this->save();

            BonusStatistic::saveStat($this, $this->user_id, BonusStatistic::ACTION_WIN, $this->money_paid_out,
                self::STATUS_ACTIVE, self::STATUS_DONE);
            return;
        }

        if ($this->money_available <= 0 && $this->spins_available < 0) {
            $this->money_available = 0;
            $this->status = self::STATUS_DONE;
            BonusStatistic::fail($this);
        }

        $this->save();
    }

    public function isProgressAvailable(): bool
    {
        return $this->status == self::STATUS_ACTIVE;
    }


    public function activate(): bool
    {
        if ($this->status == self::STATUS_WAITING_FOR_AGREEMENT || $this->status == self::STATUS_NOT_ACTIVATED) {
            $this->status = self::STATUS_WAITING_FOR_PAYMENT;
        }

        \Illuminate\Support\Facades\DB::table('payout_transactions')
            ->insert([
                'user_id' => Auth::id(),
                'hall_id' => UserWallet::currencyHallId($this->currency),
                'date' => date('Y-m-d H:i:s'),
                'sum' => $this->money_available,
                'status_id' => 35,
                'currency' => UserWallet::currencyName($this->currency),
                'operation' => $this->getName() . ' bonus activated'
            ]);

        $this->save();

        BonusStatistic::saveStat($this, $this->user_id, BonusStatistic::ACTION_ACTIVATE, $this->money_available,
            self::STATUS_WAITING_FOR_AGREEMENT, $this->status, $this->pattern_id);

        return true;
    }

    public function confirmPayment(MUser $user, float $amount, int $currency): float
    {
        $rest = $amount;
        $start_amount = $amount;
        //if refill sum is bigger then bonus max limit
        if ($this->multiplier_to_complete > 0 && $amount > $this->multiplier_to_complete) {
//            $rest = $amount - $this->multiplier_to_complete;
            $amount = $this->multiplier_to_complete;
        }

        if ($this->isPaymentRequired()) {
//            $this->max_win =  ($amount * 5) > $this->max_win ? $this->max_win : ($amount * 5);
            $this->money_available += $amount * $this->multiplier_to_payout / 100;
            $this->wager_to_complete = $this->money_available * $this->multiplier;
            $this->status = static::STATUS_ACTIVE;
            if ($this->getJsonParam('max_x_win', 0) > 0) $this->max_win = $amount * $this->getJsonParam('max_x_win', 0);

            try {
                $resp = $this->_api->createFreeRounds($this->getId(), $this->user_id, $this->game_id, $this->multiplier_to_complete);
                if (empty($resp['bonusId'])) {
                    Log::error('Ошибка при активации бонуса QtFs #' . $this->id);
                    Log::error('resp ' . var_export($resp, true));
                    return false;
                }
                $this->setJsonParam('bonusId', $resp['bonusId']);
                $this->save();
            } catch (\Exception $e) {
                Log::error($e->getMessage());
            }

            $this->save();

//            $user->getWallet($this->currency)->addLockedMoney($amount);

//            $user->notify($this->getName(), __('bonus.bonus_money_locked')
//                . $user->getWallet($this->currency)->getLockedMoney() . ' ' . $user->getWallet($this->currency)->getCurrencyName());

            DB::table('payout_transactions')
                ->insert([
                    'user_id' => $user->id,
                    'hall_id' => $user->hall_id,
                    'date' => date('Y-m-d H:i:s'),
                    'sum' => $amount,
                    'status_id' => 35,
                    'currency' => UserWallet::currencyName($this->currency),
                    'operation' => 'deposit prize bonus payed'
                ]);

            BonusStatistic::saveStat($this, $this->user_id, BonusStatistic::ACTION_DEPOSIT, $amount,
                self::STATUS_WAITING_FOR_PAYMENT, $this->status, $this->pattern_id);

            $this->onActivate();

            return $rest;
        }

        Log::error('DepositPrizeBonus error confirm payment');

        return $start_amount;
    }

    public function isVegasAllowed(): bool
    {
        return true;
    }

    public function getStatusString(IGameWallet $game_wallet = null): string
    {
        if ($this->status != static::STATUS_ACTIVE) return '';
        if ($this->spins_available > 0) return '';
        return UI::bonusPad(__('bonus.status_active'), $this->getProgressHint());
    }

    public function actualizeVegas(string $game_id, IGameWallet $game_wallet, string $system = 'vegas')
    {
        // Mark bonus as expired if user don't activated bonus until the specified time.
        if ($this->status == self::STATUS_ACTIVE && strtotime($this->active_to) < time()) {
            $this->status = self::STATUS_EXPIRED;
            BonusStatistic::expired($this);
            return;
        }

        // Do nothing for inactive bonus.
        if ($this->status != self::STATUS_ACTIVE) return;

        if ($this->spins_available == 0) {
//            $this->spins_available = -1;
//            $this->wager_to_complete = (float)bcmul($this->money_available, $this->wager_to_complete);
//            BonusStatistic::saveStat($this, $this->user_id, BonusStatistic::ACTION_FS_DONE, $this->money_available,
//                self::STATUS_ACTIVE, $this->status, $this->pattern_id, [
//                    'wager' => $this->wager_to_complete,
//                ]);
        }

        // Complete bonus and transfer money if user won necessary amount of money.
        if ($this->spins_available < 0 && $this->total_bet >= $this->wager_to_complete) {
            if ($this->max_win > 0 && $this->money_available > $this->max_win && $this->money_to_activate > 0) {
                $this->money_available = $this->max_win;
            }
            $this->spins_available = -1;
            $this->money_paid_out = $this->money_available;
            $this->writeTransaction($this->money_available);

            $game_wallet->addMoney($this->money_available);

            $this->money_available = 0;
            $this->status = self::STATUS_DONE;

            $user = MUser::find($this->user_id);
            BonusWin::userWin($user, $this->money_paid_out, $game_id, $this->currency, $system);
            BonusStatistic::win($this, $this->money_paid_out);
            return;
        }

        if ($this->money_available <= 0 && $this->spins_available < 0) {
            $this->money_available = 0;
            // call actualize in 5+ seconds
            ActualizeBonusJob::dispatch($this->getId())->delay(now()->addSeconds(5));
        }
    }

    private function writeTransaction($amount)
    {

        $user = MUser::find($this->user_id);

        DB::table('payout_transactions')
            ->insert([
                'user_id' => $user->id,
                'hall_id' => $user->hall_id,
                'date' => date('Y-m-d H:i:s'),
                'sum' => $amount,
                'status_id' => 35,
                'currency' => $this->curr_name,
                'operation' => 'Выигрыш на бонусе Qt Smart FS (id = ' . $this->id . ')'
            ]);
    }

    public function externFreeSpinBet(IGameWallet $game_wallet, string $system, string $game_id, float $amount): float
    {
        $this->withdrawCents($amount * 100, $game_wallet, $system, $game_id);
        $this->updateFsData();
        // spin is not made yet
        $this->spins_available--;
        $balance = $this->getBalanceCents($game_wallet) / 100;
//        if ($this->spins_available > 0) {
//            $balance = $this->getBalanceCents($game_wallet) / 100;
//        } else {
//            $balance = -1;
//        }
//        Log::channel('pragmatik')->info('bet spins available:'. $this->spins_available);
        Redis::set('bonus-status-' . $this->user_id, $this->getStatusString());
        $this->save();
        return $balance;
    }

    public function externFreeSpinWin(IGameWallet $game_wallet, string $system, string $game_id, float $amount): float
    {
        $this->depositCents($amount * 100, $game_wallet, $system, $game_id);
        $this->updateFsData();
        $balance = $this->getBalanceCents($game_wallet) / 100;
//        if ($this->spins_available > 0) {
//            $balance = $this->getBalanceCents($game_wallet) / 100;
//        } else {
//            $balance = -1;
//        }
//        Log::channel('pragmatik')->info('win spins available:'. $this->spins_available);
        Redis::set('bonus-status-' . $this->user_id, $this->getStatusString());
        $this->save();
        return $balance;
    }

    public function externFreeSpinsCompleted(IGameWallet $game_wallet, string $system, string $game_id, float $amount): float
    {
        $this->spins_available = -1;
        $this->total_bet = 0;
        $this->wager_to_complete =  $this->money_available * $this->multiplier;
        BonusStatistic::saveStat($this, $this->user_id, BonusStatistic::ACTION_FS_DONE, $this->money_available,
            self::STATUS_ACTIVE, $this->status, $this->pattern_id, [
                'wager' => $this->wager_to_complete,
            ]);

        $this->updateFsData();
        $this->save();
        Redis::set('bonus-status-' . $this->user_id, $this->getStatusString());
        return $this->getBalanceCents($game_wallet) / 100;
    }

    private function updateFsData()
    {

    }

    public function depositCents(int $sum, IGameWallet $game_wallet, string $system, string $game_id): int
    {
        if ($this->real_money_spent > 0){
            // 50% bonus amount 50% real amount
            $bonus_amount = intval($sum * 0.5);
            $real_amount = $sum - $bonus_amount;
        } else {
            $bonus_amount = $sum;
            $real_amount = 0;
        }

        $this->money_available += ($bonus_amount / 100);
        $game_wallet->addMoney($real_amount / 100);
        $this->total_win += $sum / 100;
        return intval($this->money_available + $game_wallet->getBalance()) * 100;
    }

    public function withdrawCents(int $sum, IGameWallet $game_wallet, string $system, string $game_id)
    {
        // 50% bonus amount 50% real amount
        if ($this->spins_available > 0) {
            $this->spins_available--;
            return;
        }
        $k = 0.5;
        $bonus_amount = intval($sum * $k);
        $real_amount = $sum - $bonus_amount;
        $this->real_money_spent = $k;

        if ($game_wallet->getBalance() < ($sum / 100)) {
            $bonus_amount = $sum;
            $real_amount = 0;
            $this->real_money_spent = 0;
        }

        if ($this->money_available < ($bonus_amount / 100)) {
            return false;
        }

        if ($this->getBalanceCents($game_wallet) < $sum) {
            return false;
        }

        $game_wallet->remMoney($real_amount / 100);

        $this->money_available -= $bonus_amount / 100;
        /** @var BonusWagerPrc $conf */
        $conf = BonusWagerPrc::where('system', $system)->where('game_id', $game_id)->first();
        if ($conf === null) {
            $this->total_bet += $sum / 100;
        } else {
            $prc = ($conf->prc ?? 100) / 100;
            $this->total_bet += ($sum * $prc) / 100;
        }
    }

    public function getBalanceCents(IGameWallet $game_wallet = null): int
    {
        return intval(($this->money_available + ($game_wallet ? $game_wallet->getBalance() : 0)) * 100);
    }

    public function afterCreated(): void
    {
        if (empty($this->id)) {
            Hlog::logException(new \Exception('Trying attach bonus with no id to segment'));
            return;
        }
        parent::afterCreated();

        BonusSegment::attachOrCreateByName($this, $this->multiplier_to_payout."% к депозиту");
    }

}
