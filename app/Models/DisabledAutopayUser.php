<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Модель сохраняющая ид пользователей для которых запрещена автовыплата
 * @property int $user_id
 */
class DisabledAutopayUser extends Model
{
    //
    protected $fillable = ['user_id'];
}
