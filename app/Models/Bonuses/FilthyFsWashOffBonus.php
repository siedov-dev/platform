<?php

namespace App\Models\Bonuses;

/**
 * @deprecated
 */
class FilthyFsWashOffBonus extends KUserBonus
{
//    /**
//     * FilthyFsWashOffBonus constructor.
//     * @param array $attributes
//     */
//    public function __construct(array $attributes = [])
//    {
//        parent::__construct($attributes);
//        $this->type = static::TYPE_FILTHY_FS_WASH_OFF;
//    }
//
//    protected $guarded = ['type'];
//
//    public static function getSetting($setting, $default)
//    {
//        return BonusSettings::getProperty(static::TYPE_FILTHY_FS_WASH_OFF, $setting, $default);
//    }
//
//    public function activate(): bool
//    {
//        if ($this->status == static::STATUS_WAITING_FOR_AGREEMENT) {
//
//            $this->status = static::STATUS_FILTHY;
//
//            if ($this->hours_active) {
//                $this->active_to = now()->addDays($this->hours_active)->format('Y-m-d H:i:s');
//            }
//            $this->wager_to_complete = $this->money_available * $this->wager_to_payout;
//            $this->total_bet = 0;
//            $this->save();
//
//            \Illuminate\Support\Facades\DB::table('payout_transactions')
//                ->insert([
//                    'user_id' => Auth::id(),
//                    'hall_id' => UserWallet::currencyHallId($this->currency),
//                    'date' => date('Y-m-d H:i:s'),
//                    'sum' => $this->money_available,
//                    'status_id' => 35,
//                    'currency' => UserWallet::currencyName($this->currency),
//                    'operation' => $this->getName() . ' отмываение activated'
//                ]);
//
//            BonusStatistic::saveStat($this, $this->user_id, BonusStatistic::ACTION_ACTIVATE, $this->money_available,
//                self::STATUS_WAITING_FOR_AGREEMENT, $this->status, $this->pattern_id, [
//                    'wager' => $this->wager_to_complete,
//                ]);
//
//            return true;
//        }
//
//        return true;
//    }
//
//    public function actualize()
//    {
//        if ($this->isExpired()) return;
//
//        if ($this->status == static::STATUS_FILTHY) $this->onUserBet(0, '');
//    }
//
//    public function getName(): string
//    {
//        return 'Filthy FreeSpins';
//    }
//
//    public function getDescription(): string
//    {
//        if ($this->status == static::STATUS_FILTHY || $this->status == static::STATUS_WAITING_FOR_AGREEMENT)
////        return "Вы выиграли ". number_format($this->money_available, 2, '.', ''). "$this->curr_name.
////            Отмойте выигранную суму, до завершения срока действия, отыграв вейджер Х".floor($this->wager_to_payout)." на реальные деньги просто играя в игры DrumMatic, NetEnt и др.";
//            return __('bonus.bonus_15_descr', [
//                'amount' => number_format($this->money_available, 2, '.', ''),
//                'curr' => $this->curr_name,
//                'wager' => floor($this->wager_to_payout),
//            ]);
//        return "";
//    }
//
//    public function getDescriptionName(): ?string
//    {
//        return 'filthy-free-spins';
//    }
//
//
//    public function isProgressAvailable(): bool
//    {
//        return $this->status == self::STATUS_FILTHY;
//    }
//
//    public function getProgressHint(): string
//    {
//        if ($this->isProgressAvailable()) {
//            return floatval($this->total_bet) . '/' . intval($this->wager_to_complete);
//        }
//        return '';
//    }
//
//    public function getProgressPercents(): int
//    {
//        return round($this->total_bet / $this->wager_to_complete * 100);
//    }
//
//    public function isVegasAllowed(): bool
//    {
//        return true;
//    }
//
//    public function onUserBet(float $amount, string $game_id)
//    {
//        if ($this->status == static::STATUS_FILTHY) {
//            $this->total_bet = (float)bcadd($this->total_bet, $amount, 4);
//            if ($this->total_bet >= $this->wager_to_complete) {
//                /** @var MUser $user */
//                $user = MUser::find($this->user_id);
//                $this->money_paid_out = $this->money_available;
//                $this->money_available = 0;
//                $this->status = self::STATUS_DONE;
//                if ($this->save()) {
//                    $user->addMoney($this->money_paid_out, $this->currency);
//                    $this->writeTransaction($this->money_paid_out, $user);
//                    $game = Vegas::findGame($game_id);
//                    BonusWin::userWin($user, $this->money_paid_out, $game_id, $this->currency, 'vegas', $game ? $game['SectionId'] : '');
//
//                    BonusStatistic::saveStat($this, $this->user_id, BonusStatistic::ACTION_WIN, $this->money_paid_out,
//                        self::STATUS_FILTHY, self::STATUS_DONE);
//
//                    event(new MoneySpent($user, $this->money_paid_out * -1, UserWallet::currencyName($this->currency)), 'bonus');
//                }
//            }
//            $this->save();
//        }
//    }
//
//    private function writeTransaction(float $amount, MUser $user)
//    {
//
//        DB::table('payout_transactions')
//            ->insert([
//                'user_id' => $user->id,
//                'hall_id' => $user->hall_id,
//                'date' => date('Y-m-d H:i:s'),
//                'sum' => $amount,
//                'status_id' => 35,
//                'currency' => $this->curr_name,
//                'operation' => 'Выигрыш на бонусе ГрязныйФриСпин (id = ' . $this->id . ')'
//            ]);
//    }
//
//    public static function getDone($user_id): ?FilthyFsWashOffBonus
//    {
//        /** @var MUser $user */
//        $user = MUser::find($user_id);
//
//        if ($user) {
//            return static::where('user_id', $user->id)
//                ->where('currency', $user->currency)
//                ->where('status', static::STATUS_FILTHY)
//                ->where('type', static::TYPE_FILTHY_FS_WASH_OFF)
//                ->first();
//        }
//
//        return null;
//    }
}
