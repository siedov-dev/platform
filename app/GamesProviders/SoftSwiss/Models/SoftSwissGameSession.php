<?php

namespace App\GamesProviders\SoftSwiss\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $user_id
 * @property int $hall_id
 * @property int $currency
 * @property string $session_id
 * @property string $game_id
 * @property float $login_balance
 * @property float $logout_balance
 * @property Carbon $login_date
 * @property Carbon $logout_date
 * @property Carbon $last_active_date
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
class SoftSwissGameSession extends Model
{
    use HasFactory;

    protected $dates = [
        'login_date',
        'logout_date',
        'last_active_date',
        'created_at',
        'updated_at',
    ];
}
