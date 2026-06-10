<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class GameTransaction
 * Игровые транзакции (ставки, выигрыши...).
 *
 * @property int $id
 * @property string $gen_id
 * @property string $system
 * @property int $user_id
 * @property int $bonus
 * @property string $action
 * @property float $amount
 * @property string $currency
 * @property string $game_id
 * @property string $session_id
 * @property string $transaction_id
 * @property string $type
 * @property Carbon $date
 * @property float $last_bet_value
 * @property ?int $last_bet_id
 * @property ?string $round_id
 */
class GameTransaction extends Model
{
    //
    protected $dates = ['date'];
}
