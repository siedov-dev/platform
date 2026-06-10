<?php

namespace App\Models\Auth;

use Illuminate\Database\Eloquent\Model;

/**
 * Class NewPassword
 * @package App\Models\Auth
 * @property int id
 * @property int user_id
 * @property string new_pass
 * @property string view_pass
 * @property string old_pass
 * @property string old_view_pass
 * @property string token
 */
class NewPassword extends Model
{
    //
}
