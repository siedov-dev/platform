<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class BonusBlackList
 * @package App\Models
 * @property int $id
 * @property int $user_id
 */
class BonusBlackList extends Model
{
    protected $fillable = ['user_id'];
}
