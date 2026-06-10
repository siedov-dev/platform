<?php

namespace App\Models\Slc\Achievements;

use App\MUser;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $user_id
 * @property int $achievement_id
 * @property boolean $rewarded
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
class SlcUserAchievement extends Model
{
    use HasFactory;

    protected $dates = [
        'created_at',
        'updated_at',
    ];

    public function getId(): int
    {
        return $this->id;
    }

    public function getUserId(): int
    {
        return $this->user_id;
    }

    public function getAchievementId(): int
    {
        return $this->achievement_id;
    }

    public function isRewarded(): bool
    {
        return $this->rewarded;
    }

    public static function newAchievementsCount(MUser $user): int
    {
        return SlcUserAchievement::where('user_id', $user->getId())
            ->where('rewarded', false)
            ->join('slc_achievements', 'slc_user_achievements.achievement_id', '=', 'slc_achievements.id')
            ->where(function ($q) use ($user) {
                $q->where('slc_achievements.currency', $user->getCurrency())
                    ->orWhere('slc_achievements.currency', 0);
            })
            ->selectRaw('count(slc_user_achievements.id) as cnt')->first()?->cnt?? 0;
    }
}
