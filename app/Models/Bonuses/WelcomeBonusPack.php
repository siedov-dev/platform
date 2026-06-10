<?php

namespace App\Models\Bonuses;

use App\CustomerIo\CioEventType;
use App\CustomerIo\CustomerIo;
use App\GamesProviders\Game;
use App\GamesProviders\IGameWallet;
use App\Models\Bonuses\Contracts\BonusRenderer;
use App\Models\Bonuses\Render\WelcomeBonusPackRenderer;
use App\Models\Bonuses\Segments\BonusSegment;
use App\Models\BonusPatterns;
use App\MUser;
use App\Services\KBonus\KBonus;

class WelcomeBonusPack extends KUserBonus
{
//    public function actualize()
//    {
//        if ($this->isExpired()) return;
//        if ($this->tries === 4) {
//            if ($this->isPending() && now() > Carbon::parse($this->completed_at)->addDay()) {
//                $this->status = self::STATUS_WAITING_FOR_AGREEMENT;
//                $this->save();
//            }
//            if ($this->isAgreementRequired() && now() > Carbon::parse($this->completed_at)->addDays(2)) {
//                $this->tries = 5;
//                $this->save();
//            }
//        }
//        if ($this->tries === 5) {
//            if ($this->isPending() && now() > Carbon::parse($this->completed_at)->addDays(2)) {
//                $this->status = self::STATUS_WAITING_FOR_AGREEMENT;
//                $this->save();
//            }
//            if ($this->isAgreementRequired() && now() > Carbon::parse($this->completed_at)->addDays(3)) {
//                $this->status = self::STATUS_DONE;
//                $this->save();
//            }
//        }
//    }

//    public function activate(): bool
//    {
//        if ($this->status == self::STATUS_WAITING_FOR_AGREEMENT || $this->status == self::STATUS_NOT_ACTIVATED) {
//            if ($this->tries < 4) {
//                $this->tries++;
//                $this->status = self::STATUS_WAITING_FOR_PAYMENT;
//                $ptn = BonusPatterns::getRegPattern($this->curr_name, BonusPatterns::RB_TYPE_DEP, $this->tries);
//                $this->money_to_activate = $ptn?->getBonusParam('min_sum', $this->curr_name, $this->money_to_activate)?? $this->money_to_activate;
//            } else {
//                $this->actualize();
//                if ($this->tries === 4 && $this->status == self::STATUS_WAITING_FOR_AGREEMENT) {
//                    $this->status = self::STATUS_PENDING;
//                    $this->tries = 5;
//                    $this->save();
//                    //create nondep
//                    $this->createNonDeps(4);
//                } elseif ($this->tries === 5 && $this->status == self::STATUS_WAITING_FOR_AGREEMENT) {
//                    $this->status = self::STATUS_DONE;
//                    $this->save();
//                    //create nondep
//                    $this->createNonDeps(5);
//                }
//            }
//
//            \Illuminate\Support\Facades\DB::table('payout_transactions')
//                ->insert([
//                    'user_id' => Auth::id(),
//                    'hall_id' => UserWallet::currencyHallId($this->currency),
//                    'date' => date('Y-m-d H:i:s'),
//                    'sum' => $this->money_available,
//                    'status_id' => PayoutTransaction::STATUS_BONUS_ACTIVATED,
//                    'currency' => UserWallet::currencyName($this->currency),
//                    'operation' => $this->getName() . ' bonus activated'
//                ]);
//
//            $this->save();
//
//            BonusStatistic::saveStat($this, $this->user_id, BonusStatistic::ACTION_ACTIVATE, $this->money_available,
//                self::STATUS_WAITING_FOR_AGREEMENT, $this->status, $this->pattern_id);
//        }
//
//        return true;
//    }

//    public function confirmPayment(MUser $user, float $amount, int $currency): float
//    {
//        $rest = $amount;
//        $start_amount = $amount;
//
//        if ($this->isPaymentRequired()) {
//            $this->status = static::STATUS_WAITING_FOR_AGREEMENT;
//            $this->completed_at = now();
//            $this->save();
//
//            DB::table('payout_transactions')
//                ->insert([
//                    'user_id' => $user->id,
//                    'hall_id' => $user->hall_id,
//                    'date' => date('Y-m-d H:i:s'),
//                    'sum' => $amount,
//                    'status_id' => PayoutTransaction::STATUS_BONUS_ACTIVATED,
//                    'currency' => UserWallet::currencyName($this->currency),
//                    'operation' => 'welcome bonus payed'
//                ]);
//
//            BonusStatistic::saveStat($this, $this->user_id, BonusStatistic::ACTION_DEPOSIT, $amount,
//                self::STATUS_WAITING_FOR_PAYMENT, $this->status, $this->pattern_id);
//
//            if ($this->tries < 4) {
//                $dep_ptn = BonusPatterns::getRegPattern($this->curr_name, BonusPatterns::RB_TYPE_DEP, $this->tries);
//                if ($dep_ptn) {
//                    $dep_bonus = $dep_ptn->createForUser($this->user, $currency, false);
//                    $dep_bonus->activate();
//                    $dep_bonus->confirmPayment($this->user, $amount, $currency);
//                    $dep_bonus->save();
//
//                    BonusSegment::attachOrCreateByName($dep_bonus, "Welcome Bonus Dep " . $this->tries);
//
//                    // create fs bonus
//                    if ($start_amount >= $dep_bonus->getJsonParam('fs_dep_sum', 0)) {
//                        Log::info("dep:" . $this->tries);
//                        $fs_pattern = BonusPatterns::getRegPattern($this->curr_name, BonusPatterns::RB_TYPE_FS, $this->tries);
//                        if ($fs_pattern) {
//                            /** @var UserBonus $bonus */
//                            $bonus = $fs_pattern->createForUser($user, $currency, true);
//                            $bonus->status = UserBonus::STATUS_LOCKED;
//                            $bonus->save();
//                            BonusSegment::attachOrCreateByName($bonus, "Welcome Bonus FS " . $this->tries);
//                        } else {
//                            Log::error("can't find welcome fs pattern");
//                        }
//                    }
//
//                    if ($this->tries < 3) {
//                        //retention next dep email send job
//                        $blank = 'ret2_dep' . ($this->tries + 1);
//                        SendMessageBlankJob::dispatch($this->user_id, $blank);
//                    }
//                }
//            }
//
//            Log::info('dep n:' . $this->tries);
//
//            if ($this->tries === 3) {
//                $this->tries++;
//
//                // if a user hasn't got 3 fs bonuses (haven't completed requirement), bonus is done
//                $users_fs_bonuses = \App\Models\Bonuses\UserBonus::where('user_id', $user->getId())
//                    ->whereIn('pattern_id', BonusPatterns::getRegFsPatternIds($this->curr_name))
//                    ->orderBy('pattern_id')
//                    ->get();
//
//                $this->status = self::STATUS_PENDING;
//
//                if ($users_fs_bonuses->count() < 3) {
//                    $this->status = self::STATUS_DONE;
//                }
//
//                $this->save();
//
//                //conditions completed mail
//                if ($users_fs_bonuses->count() >= 3) {
//                    //retention next dep email send job
//                    $blank = 'ret2_wb_completed';
//                    SendMessageBlankJob::dispatch($this->user_id, $blank);
//                }
//            }
//
//            return $rest;
//        }
//
//        Log::error('WelcomeBonus error confirm payment');
//
//        return $start_amount;
//
//    }

    public static function createForUser(MUser $user, int $days = 21): ?KUserBonus
    {
        $ptn = BonusPatterns::getRegPattern($user->getCurrencyName(), BonusPatterns::RB_TYPE_DEP, 1);
        $create_params = [
            'min_sum' => $ptn->getBonusParam('min_sum', $user->getCurrencyName()),
            'status' => static::STATUS_WAITING_FOR_PAYMENT,
            'active_days' => $days,
        ];

        $bonus = KBonus::getInstance()->createFromParams(
            bonus_type: self::TYPE_WELCOME_BONUS_PACK,
            user_id: $user->getId(),
            params: json_encode($create_params),
            currency: $user->getCurrency(),
            keep_params: true,
            tmp: false,
        );

//        if ($bonus !== null) $bonus = KUserBonus::convertByType($bonus);

        KBonus::getInstance()->addBonusToSegmentByName($bonus->getId(), BonusSegment::WB_PACK);

        CustomerIo::getInstance()->sendEvent($user->getId(), CioEventType::WbActivated);

        return $bonus;
    }

    public function getName(): string
    {
        return __('bonuses.welcome_bonus');
    }

    public function isLocked(): bool
    {
        return false;
    }

    public function isGameAllowed(Game $game): bool
    {
        return false;
    }

    public function isGameAllowedByProvider(string $providerId, string $gameId): bool
    {
        return false;
    }

    public function getDescription(): string
    {
        return __('bonuses.welcomePack', [
            'sum' => $this->money_to_activate,
            'currency' => $this->curr_name,
            'bonus' => ceil($this->multiplier_to_payout),
            'wager' => $this->multiplier,
        ]);
    }


    public function getDescriptionName(): ?string
    {
        return 'welcome-bonus-pack';
    }

    public function isProgressAvailable(): bool
    {
        return false;
    }

    public function getProgressPercents(): int
    {
        return 0;
    }

    public function getProgressHint(): string
    {
        return '';
    }

    public function getStatusString(IGameWallet $game_wallet = null): string
    {
        return '';
    }

    public function allGamesAllowed(): bool
    {
        return false;
    }

    public function getRenderer(): BonusRenderer
    {
        return new WelcomeBonusPackRenderer($this);
    }

    public function getShortDescription(): string
    {
        return view('common.bonus-descriptions.wb-pack', ['bonus' => $this])->render();
    }

//    private function createNonDeps(int $dep_n): void
//    {
//        $pattern_fd_ndfs = \App\Models\BonusPatterns::getRegPattern($this->curr_name, \App\Models\BonusPatterns::RB_TYPE_FS, $dep_n);
//
//        $nd = $pattern_fd_ndfs->createForUser($this->user, $this->getCurrency(), true);
//        $nd->save();
//
//        BonusSegment::attachOrCreateByName($nd, "Welcome Bonus бездеп ".($dep_n - 3));
//    }
}
