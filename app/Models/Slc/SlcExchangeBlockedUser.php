<?php

namespace App\Models\Slc;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $user_id
 */
class SlcExchangeBlockedUser extends Model
{
    protected $guarded = ['id'];

    public static function addUserToList(int $user_id): bool
    {
        SlcExchangeBlockedUser::updateOrCreate(['user_id' => $user_id]);
        return true;
    }

    public static function removeUserFromList(int $user_id): bool
    {
        SlcExchangeBlockedUser::where('user_id', $user_id)->delete();
        return true;
    }

    public static function isUserBlocked(int $user_id): bool
    {
        return SlcExchangeBlockedUser::where('user_id', $user_id)->exists();
    }
}
