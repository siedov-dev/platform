<?php

namespace App\Models\Bonuses;


use App\GamesProviders\IGameWallet;
use App\Helpers\Hlog;
use App\Helpers\UI;
use App\Models\Bonuses\Segments\BonusSegment;
use App\Models\Bonuses\Traits\FsWagerActualize;
use App\Models\BonusStatistic;
use App\MUser;
use App\Services\KBonus\KBonus;
use App\UserWallet;
use App\Wallet;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DepositPrizeBonus extends KUserBonus
{
//    use FsWagerActualize;
//    /**
//     * DepositPrizeBonus constructor.
//     * Sets type of the bonus.
//     * @param array $attributes
//     */
//    public function __construct(array $attributes = [])
//    {
//        parent::__construct($attributes);
//        $this->type = static::TYPE_DEPOSIT_PRIZE;
//    }
//
//    protected $guarded = ['type'];


//    public function createFromParams(string $params, int $user_id, ?int $currency = null, ?bool $keep_params = false, ?bool $tmp = false): DepositPrizeBonus
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
//        $bonus->status = $params['status'] ?? static::STATUS_WAITING_FOR_AGREEMENT;
//        $bonus->user_id = $user_id;
//        $bonus->hall_id = $hall_id;
//        $bonus->currency = $curr;
//        $bonus->spins_available = -1;
//        //money should be set after pay in
//        $bonus->money_available = 0;
//        //minimal refill sum
//        $bonus->money_to_activate = $params['min_sum'];
//        //max refill sum
//        $bonus->multiplier_to_payout = $params['max_sum'];
//        //wager koef
//        $bonus->multiplier = $params['wager'];
//        $bonus->max_win = $params['max_win']?? 0;
//        $bonus->multiplier_to_complete = $params['max_x_win']?? 0;
//
//        $bonus->active_to = now()->addDays($params['days_active'] ?? 5)->format('Y-m-d H:i:s');
//        $bonus->json_params = json_encode($params?? []);
//
//        if (!empty($params['bonus_prizes'])) {
//            $bonus->bonus_prizes = json_encode($params['bonus_prizes']);
//        }
//
//        if (!empty($params['enable_after'])) {
//            $bonus->enable_after = json_encode($params['enable_after']);
//        }
//        if (!$tmp) $bonus->save();
//        return $bonus;
//    }

    /**
     * @return bool
     */
//    public function activate(): bool
//    {
//        if ($this->status == self::STATUS_WAITING_FOR_AGREEMENT || $this->status == self::STATUS_NOT_ACTIVATED) {
//            $this->status = self::STATUS_WAITING_FOR_PAYMENT;
//            BonusStatistic::saveStat($this, $this->user_id, BonusStatistic::ACTION_ACTIVATE, $this->money_to_activate,
//                self::STATUS_WAITING_FOR_AGREEMENT, $this->status, $this->pattern_id);
//        } else {
//            BonusStatistic::saveStat($this, $this->user_id, BonusStatistic::ACTION_ACTIVATE, $this->money_available,
//                self::STATUS_WAITING_FOR_AGREEMENT, $this->status, $this->pattern_id);
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
//
//        return true;
//    }


//    public function confirmPayment(MUser $user, float $amount, int $currency): float
//    {
//        $rest = $amount;
//        $start_amount = $amount;
//        //if refill sum is bigger then bonus max limit
//        if ($amount > $this->multiplier_to_payout) {
////            $rest = $amount - $this->multiplier_to_complete;
//            $amount = $this->multiplier_to_payout;
//        }
//
//        if ($this->isPaymentRequired()) {
//            $this->money_available += $amount * $this->getJsonParam('multiplier', 100) / 100;
//            //win limit payment sum * max_x_win
//            if ($this->multiplier_to_complete > 0) $this->max_win = $amount * $this->multiplier_to_complete;
//            $this->wager_to_complete = $this->money_available * $this->multiplier;
//            $this->status = static::STATUS_ACTIVE;
//
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
//                    'operation' => 'deposit prize bonus payed'
//                ]);
//
//            BonusStatistic::saveStat($this, $this->user_id, BonusStatistic::ACTION_DEPOSIT, $amount,
//                self::STATUS_WAITING_FOR_PAYMENT, $this->status, $this->pattern_id);
//
//            $this->onActivate();
//
//            return $rest;
//        }
//
//        Log::error('DepositPrizeBonus error confirm payment');
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
                return __('bonuses.depBonusDescActive');

            case self::STATUS_WAITING_FOR_AGREEMENT:
                return __('bonuses.depBonusDescAgreement', [
                    'sum' => $this->money_to_activate,
                    'currency' => $this->curr_name,
                    'bonus' => ceil($this->multiplier_to_payout),
                    'wager' => $this->multiplier,
                ]);

            case self::STATUS_WAITING_FOR_PAYMENT:
                return __('bonuses.depBonusDescPayment', [
                    'sum' => $this->money_to_activate,
                    'currency' => $this->curr_name,
                ]);
        }
        return __('bonuses.depBonusDescAgreement', [
            'sum' => $this->money_to_activate,
            'currency' => $this->curr_name,
            'bonus' => ceil($this->multiplier_to_payout),
            'wager' => $this->multiplier,
        ]);
    }


    public function getDescriptionName(): ?string
    {
        return "deposit-prize";
    }


    public function getProgressHint(): string
    {
        if ($this->isProgressAvailable()) {
            return __('bonuses.depBonusProgress', [
                'bet' => floor($this->total_bet),
                'total' => floor($this->wager_to_complete)
            ]);
        }
        return '';
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

//        BonusSegment::attachOrCreateByName($this, $this->getJsonParam('multiplier', 100)."% к депозиту");
        KBonus::getInstance()->addBonusToSegmentByName($this->getId(), $this->getJsonParam('multiplier', 100)."% к депозиту");
    }
}
