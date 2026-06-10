<?php

namespace App\Models\Rank;

use App\MUser;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $user_id
 * @property int $currency
 * @property int $level
 */
class UserRankRewarded extends Model
{
    use HasFactory;

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function getUserId(): int
    {
        return $this->user_id;
    }

    public function setUserId(int $user_id): void
    {
        $this->user_id = $user_id;
    }

    public function getCurrency(): int
    {
        return $this->currency;
    }

    public function setCurrency(int $currency): void
    {
        $this->currency = $currency;
    }

    public function getLevel(): int
    {
        return $this->level;
    }

    public function setLevel(int $level): void
    {
        $this->level = $level;
    }

    public static function setRewarded(MUser $user, UserRank $rank): void
    {
        $r = new UserRankRewarded();
        $r->setUserId($user->getId());
        $r->setCurrency($rank->getCurrencyId());
        $r->setLevel($rank->getLevel());
        $r->save();
    }

    public static function takenRewardsCount(MUser $user, int $currency = 0): int
    {
        if ($currency === 0) $currency = $user->getCurrency();
        return UserRankRewarded::where('user_id', $user->getId())
            ->where('currency', $currency)
            ->count();
    }

}
