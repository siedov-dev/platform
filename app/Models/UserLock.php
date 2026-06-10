<?php

namespace App\Models;

use App\MUser;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Class UserLock
 * Модель с данными о самоблокировке пользователей
 *
 * @property int $id
 * @property int $user_id
 * @property Carbon $locked_to
 * @property Carbon $updated_at
 * @property Carbon $created_at
 */
class UserLock extends Model
{
    use HasFactory;

    protected $dates = [
        'locked_to',
        'updated_at',
        'created_at',
    ];

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

    public function getLockedTo(): Carbon
    {
        return $this->locked_to;
    }

    public function setLockedTo(Carbon $locked_to): void
    {
        $this->locked_to = $locked_to;
    }

    public static function lockUser(MUser $user, int $days): void
    {
        $lock = UserLock::where('user_id', $user->getId())->first();
        if ($lock === null) {
            $lock = new UserLock();
            $lock->setUserId($user->getId());
        }
        $lock->setLockedTo(now()->addDays($days));
        $lock->save();
    }

    public static function getUserLock(MUser $user): ? UserLock
    {
        return UserLock::where('user_id', $user->getId())->first();
    }
}
