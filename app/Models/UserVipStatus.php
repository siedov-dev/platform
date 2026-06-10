<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Пользователя с VIP статусом.
 *
 * @property int $id
 * @property int $user_id
 * @property int  $status_id
 * @property Carbon $active_to
 * @property ?VipStatus $status
 */
class UserVipStatus extends Model
{
    use HasFactory;

    /**
     * set new status for user
     * @param int $user_id
     * @param int $status_id
     * @param int $days
     * @return self
     */
    public static function setForUser(int $user_id, int $status_id, int $days = 5): self
    {
        //close old statuses if exist
        UserVipStatus::where('user_id', $user_id)
            ->where('active_to', '>', now()->format('Y-m-d H:i:s'))
            ->update(['active_to' => now()->format('Y-m-d H:i:s')]);

        //create new status
        $status = new self();
        $status->user_id = $user_id;
        $status->status_id = $status_id;
        $status->active_to = now()->addDays($days);
        $status->save();
        return $status;
    }

    /**
     * status info
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function statusInfo()
    {
        return $this->hasOne(VipStatus::class, 'id', 'status_id');
    }
}
