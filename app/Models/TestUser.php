<?php

namespace App\Models;

use App\MUser;
use Illuminate\Database\Eloquent\Model;

/**
 * Тестеры.
 * @property int $id
 * @property int $user_id
 * @property MUser $user
 */
class TestUser extends Model
{
    //
    public function user()
    {
        return $this->hasOne(MUser::class, 'id', 'user_id');
    }

    public static function getUserIds(): array
    {
        return TestUser::all()->pluck('user_id')->toArray();
    }
}
