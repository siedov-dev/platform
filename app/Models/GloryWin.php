<?php

namespace App\Models;

use App\MUser;
use Illuminate\Database\Eloquent\Model;

/**
 * Class GloryWin.
 * Выигрыши для зала славы.
 * @package App\Models
 * @property int id
 * @property int user_id
 * @property string system
 * @property string game_id
 * @property float bet
 * @property float win
 * @property int currency
 */
class GloryWin extends Model
{
    public static function win(MUser $user, string $system, string $game_id, float $bet, float $win, int $currency)
    {
        $gwin = new static();
        $gwin->user_id = $user->id;
        $gwin->system = $system;
        $gwin->game_id = $game_id;
        $gwin->bet = $bet;
        $gwin->win = $win;
        $gwin->currency = $currency;
        $gwin->save();
    }
}
