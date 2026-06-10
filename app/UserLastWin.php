<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * Class UserLastWin
 * @package App
 *
 * @property int $id
 * @property string $nik
 * @property string $last_game
 * @property float $sum
 */

/**
 * @deprecated
 */
class UserLastWin extends Model
{
    protected $table = 'last_win_kraken';
    public $timestamps = false;
}
