<?php

namespace App\Models\Bonuses;

use App\GamesProviders\IGameWallet;
use App\GamesProviders\Qt\QtGamesProviderApi;
use App\Helpers\UI;
use App\Jobs\ActualizeBonusJob;
use App\Models\BonusStatistic;
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
class QtFsWager extends UserBonus
{
    protected $guarded = ['type'];
    protected QtGamesProviderApi $_api;

    public function __construct(array $attributes = [])
    {
        $this->_api = new QtGamesProviderApi();
        parent::__construct($attributes);
        $this->type = static::TYPE_QT_FS_WAGER;
        if ($this->currency !== null) $this->curr_name = UserWallet::currencyName($this->currency);
    }

    /**
     * Creates bonus for given user.
     * @param MUser $user
     * @param int $spins
     * @param float $money
     * @param int $wager
     * @return QtFsWager
     */
    public static function createForUser(MUser $user, float $spins_sum = 0, float $money = 0, int $wager = 0): QtFsWager
    {
        $bonus = new static();
        $bonus->user_id = $user->id;
        $bonus->hall_id = $user->hall_id;
        $bonus->currency = $user->currency;
        $bonus->spins_available = 1;
        $bonus->spins_total = 1;
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
        $bonus->user_id = $user?->id;
        $bonus->hall_id = $hall_id;
        $bonus->currency = $curr;
        $bonus->spins_available = 1;
        $bonus->spins_total = 1;
        $bonus->status = static::STATUS_NOT_ACTIVATED;
        $bonus->money_available = $money;
        $bonus->money_to_activate = $params['min_sum']?? 0;
        $bonus->multiplier_to_complete = $params['spins_amount'];// fs amount
        $bonus->wager_to_complete = 0;// start wager amount
        $bonus->wager_to_payout = $money;
        $bonus->hours_active = 24;
        $bonus->multiplier = $wager; //wager coefficient
        $bonus->max_win = $params['max_win']?? 0;
        $bonus->active_to = now()->addHours(72)->format('Y-m-d H:i:s');
        $this->json_params = json_encode($params?? []);

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
        if ($this->spins_available > 0) {
            return $this->spins_available <= 0 ? 100 : (100 - round($this->spins_available / $this->spins_total * 100));
        }
        $p = $this->total_bet / $this->wager_to_complete * 100;
        if ($p > 100) $p = 100;
        return $p;
    }

    public function getName(): string
    {
        return __('bonus.bonus_32_name');
    }

    public function getDescription(): string
    {
        switch ($this->status) {
            case self::STATUS_ACTIVE:
                return __('bonus.bonus_32_descr1');

            case self::STATUS_WAITING_FOR_PAYMENT:
                return __('bonus.bonus_32_descr2', ['amount' => $this->money_to_activate, 'curr' => $this->curr_name]);
        }

        return __('bonus.bonus_32_descr3');
    }

    public function getBalanceString()
    {
        if ($this->isDone()) {
            return number_format($this->getTotalMoneyWon(), 2, ".", "") . " " . UserWallet::currencySymbol($this->currency);
        }
        if ($this->spins_available > 0) {
            return floor($this->multiplier_to_complete);
        } else {
            return number_format($this->money_available, 2, ".", "") . " " . UserWallet::currencySymbol($this->currency);
        }
    }

    public function getDescriptionName(): ?string
    {
        return 'qt-free-spins';
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
            $this->_api->deleteFreeRounds($this->user_id, $this->getJsonParam('qt_bonus_id'));
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
        if ($this->status != static::STATUS_NOT_ACTIVATED) return false;
        if ($this->money_to_activate > 0) {
            $this->status = self::STATUS_WAITING_FOR_PAYMENT;
            BonusStatistic::saveStat($this, $this->user_id, BonusStatistic::ACTION_ACTIVATE, $this->money_to_activate,
                self::STATUS_WAITING_FOR_AGREEMENT, $this->status, $this->pattern_id);
            $this->save();
            return true;
        }

        $this->status = static::STATUS_ACTIVE;

        $resp = $this->_api->createFreeRounds($this->getId(), $this->user_id, $this->game_id, $this->multiplier_to_complete);
        if (empty($resp['bonusId'])) {
            Log::error('Ошибка при активации бонуса QtFs #' . $this->id);
            Log::error('resp ' . var_export($resp, true));
            return false;
        }
        $this->setJsonParam('bonusId', $resp['bonusId']);
        $this->save();

        BonusStatistic::saveStat($this, $this->user_id, BonusStatistic::ACTION_ACTIVATE, $this->money_available,
            self::STATUS_WAITING_FOR_AGREEMENT, $this->status, $this->pattern_id);

        DB::table('payout_transactions')
            ->insert([
                'user_id' => Auth::id(),
                'hall_id' => UserWallet::currencyHallId($this->currency),
                'date' => date('Y-m-d H:i:s'),
                'sum' => $this->money_available,
                'status_id' => 35,
                'currency' => $this->curr_name,
                'operation' => $this->getName() . ' bonus activated'
            ]);
        return true;
    }

    public function confirmPayment(MUser $user, float $amount, int $currency): float
    {
        if ($amount < $this->money_to_activate) return $amount;
        $rest = $amount;

        try {
            $b_amount = $amount > $this->getJsonParam('max_sum', 0) ? $this->getJsonParam('max_sum', 0) : $amount;
            $this->status = static::STATUS_ACTIVE;
            $this->active_to = now()->addHours($this->hours_active)->format('Y-m-d H:i:s');
            if ($this->getJsonParam('max_x_win', 0) > 0) $this->max_win = $b_amount * $this->getJsonParam('max_x_win', 0);
            $this->save();

            $resp = $this->_api->createFreeRounds($this->getId(), $this->user_id, $this->game_id, $this->multiplier_to_complete);
            if (empty($resp['bonusId'])) {
                Log::error('Ошибка при активации бонуса PPlay #' . $this->id);
                return $amount;
            }
            $this->setJsonParam('bonusId', $resp['bonusId']);

            BonusStatistic::saveStat($this, $this->user_id, BonusStatistic::ACTION_DEPOSIT, $b_amount,
                self::STATUS_WAITING_FOR_PAYMENT, $this->status, $this->pattern_id);

            BonusStatistic::saveStat($this, $this->user_id, BonusStatistic::ACTION_ACTIVATE, $this->money_available,
                self::STATUS_WAITING_FOR_AGREEMENT, $this->status, $this->pattern_id);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
        }
        return $rest;
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

        // Complete bonus and transfer money if user won necessary amount of money.
        if ($this->spins_available < 0 && $this->total_bet >= $this->wager_to_complete) {
            if ($this->max_win > 0 && $this->money_available > $this->max_win) {
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
                'operation' => 'Выигрыш на бонусе Qt FS (id = ' . $this->id . ')'
            ]);
    }

    public function externFreeSpinBet(IGameWallet $game_wallet, string $system, string $game_id, float $amount): float
    {
        $this->withdrawCents($amount * 100, $game_wallet, $system, $game_id);
        $this->updateFsData();
        $balance = $this->getBalanceCents($game_wallet) / 100;
        Redis::set('bonus-status-' . $this->user_id, $this->getStatusString());
        $this->save();
        return $balance;
    }

    public function externFreeSpinWin(IGameWallet $game_wallet, string $system, string $game_id, float $amount): float
    {
        $this->depositCents($amount * 100, $game_wallet, $system, $game_id);
        $this->updateFsData();
        $balance = $this->getBalanceCents($game_wallet) / 100;
        Redis::set('bonus-status-' . $this->user_id, $this->getStatusString());
        $this->save();
        return $balance;
    }

    public function externFreeSpinsCompleted(IGameWallet $game_wallet, string $system, string $game_id, float $amount): float
    {
        $this->spins_available = -1;
        $this->wager_to_complete = (float)bcmul($this->money_available, $this->multiplier);
        BonusStatistic::saveStat($this, $this->user_id, BonusStatistic::ACTION_FS_DONE, $this->money_available,
            self::STATUS_ACTIVE, $this->status, $this->pattern_id, [
                'wager' => $this->wager_to_complete,
            ]);
        $this->updateFsData();
        $this->save();
        return $this->getBalanceCents($game_wallet) / 100;
    }

    private function updateFsData()
    {

    }
}
