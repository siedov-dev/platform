<?php

namespace App\Models\Slc;

use App\MUser;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $user_id
 * @property int $day
 */
class SlcLoginRewardsLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'day',
    ];

    public static function add(MUser $user, int $day)
    {
        $log = new SlcLoginRewardsLog();
        $log->user_id = $user->getId();
        $log->day = $day;
        $log->save();
    }

    public static function getRewardedDaysCount(MUser $user): int
    {
        return SlcLoginRewardsLog::where('user_id', $user->getId())->count();
    }
}
