<?php

namespace App\Models\Rank;

use App\Helpers\Localization;
use App\Models\Slc\SlcItem;
use App\MUser;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

/**
 * Class UserRank
 * @package App\Models
 * @property int $id
 * @property integer $currency_id
 * @property integer $rank_level
 * @property float $start_value
 * @property float $end_value
 * @property int $deposit_from
 * @property int $spins
 * @property int $bet_limit
 * @property int $wager
 * @property int $win_limit
 * @property int $cashback
 * @property int $weekly_cashback
 * @property int $wager_diminution
 * @property bool $vip_events
 * @property bool $secret_prize
 */
class UserRank extends Model
{
    /**
     * Возвращает ид записи в БД
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    /**
     * Возвращает ид валюты ранга
     * @return int
     */
    public function getCurrencyId(): int
    {
        return $this->currency_id;
    }

    public function setCurrencyId(int $currency_id): void
    {
        $this->currency_id = $currency_id;
    }


    /**
     * Возвращает уровень ранга
     * @return int
     */
    public function getLevel(): int
    {
        return $this->rank_level;
    }

    public function setLevel(int $level): void
    {
        $this->rank_level = $level;
    }

    /**
     * Возвращает название ранга
     * @return string
     */
    public function getRankName(): string
    {
        return __('ranks.rank_' . $this->getLevel());
    }

    /**
     * Возвращает все ранги для указанной валюты
     * @param int|null $currency_id
     * @return Collection<UserRank>
     */
    public static function getAllRanks(?int $currency_id = null): Collection
    {
        if ($currency_id === null) {
            $currency_id = MUser::getAuth()?->currency?? Localization::getCurrency();
        }
//        return Cache::remember('all_rank_levels_'.$currency_id, 360, function () use ($currency_id) {
            return UserRank::where('currency_id', $currency_id)->get()->sortBy('start_value');
//        });
    }

    /**
     * Возвращает процент прогресса до следующего уровня.
     * @param float $current_amount
     * @param int $currency
     * @return int
     */
    public function getNextRankProgress(float $current_amount, int $currency): int
    {
        $ranks = self::getAllRanks($currency)->toArray();
        $rank_lvl = $this->getLevel();
//        if ($rank_lvl >= count($ranks)) return 100;
        if ($current_amount > $ranks[$rank_lvl - 1]['end_value']) return 100;
        return floor($current_amount / $ranks[$rank_lvl - 1]['end_value'] * 100);
    }

    /**
     * Возвращает стартовое значение для ранга.
     * @return float
     */
    public function getStartValue(): float
    {
        return $this->start_value;
    }

    public function setStartValue(float $value): void
    {
        $this->start_value = $value;
    }

    /**
     * Возвращает финальное значение для ранга
     * @return float
     */
    public function getEndValue(): float
    {
        return $this->end_value;
    }

    public function setEndValue(float $value): void
    {
        $this->end_value = $value;
    }

    /**
     * filthy fs params
     * @return int
     * @deprecated
     */
    public function getDepositFrom(): int
    {
        return $this->deposit_from;
    }

    /**
     * filthy fs params
     * @return int
     * @deprecated
     */
    public function getSpins(): int
    {
        return $this->spins;
    }

    /**
     * filthy fs params
     * @return int
     * @deprecated
     */
    public function getBetLimit(): int
    {
        return $this->bet_limit;
    }

    /**
     * filthy fs params
     * @return int
     * @deprecated
     */
    public function getWager(): int
    {
        return $this->wager;
    }

    /**
     * filthy fs params
     * @return int
     * @deprecated
     */
    public function getWinLimit(): int
    {
        return $this->win_limit;
    }

    /**
     * weekly bonus cashback percents
     * @return int
     * @deprecated
     */
    public function getCashBack(): int
    {
        return $this->cashback;
    }

    /**
     * Возвращает процент еженедельного кешбэка
     * @return int
     */
    public function getWeeklyCashBackPrc(): int
    {
        return $this->weekly_cashback;
    }

    public function setWeeklyCashBackPrc(int $prc): void
    {
        $this->weekly_cashback = $prc;
    }

    /**
     * wager diminution of the bonuses created by admin
     * @return int
     * @deprecated
     */
    public function getWagerDiminution(): int
    {
        return $this->wager_diminution;
    }

    /**
     * Возвращает является ли статус VIP
     * vip events allowed
     * @return bool
     */
    public function isVip(): bool
    {
        return $this->vip_events;
    }

    public function setVip(bool $vip): void
    {
        $this->vip_events = $vip;
    }

    /**
     * Возвращает награды ранга
     * @return Collection<SlcItem>
     */
    public function getRewards(): Collection
    {
        return UserRankReward::where('rank_level', $this->getLevel())->get();
    }

    /**
     * Возвращает получена ли награда за ранг
     * @param MUser $user
     * @param int $currency
     * @return bool
     */
    public function isRewarded(MUser $user, int $currency): bool
    {
        return UserRankRewarded::where('user_id', $user->getId())
            ->where('currency', $currency)
            ->where('level', $this->getLevel())
            ->first() !== null;
    }

    /**
     * @return bool
     * @deprecated
     */
    public function isSecretPrize(): bool
    {
        return $this->secret_prize;
    }

    public function setSecretPrize(bool $secret_prize): void
    {
        $this->secret_prize = $secret_prize;
    }

    /**
     * Возвращает уровень ранга для указанного значение
     * @param float $amount
     * @param int $currency
     * @return int
     */
    public static function getAmountLevel(float $amount, int $currency): int
    {
        return self::getAllRanks($currency)->where('start_value', '<=', $amount)->max('rank_level');
    }

    /**
     * Возвращает ранг для указанных уровня и валюты
     * @param int $level
     * @param int $currency_id
     * @return UserRank|null
     */
    public static function getForLvl(int $level, int $currency_id): ?UserRank
    {
        return self::getAllRanks($currency_id)
            ->where('rank_level', $level)
            ->first();
    }
}
