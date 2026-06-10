<?php

namespace App\Models\TvBet;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $name
 * @property string $amounts
 * @property Carbon $expired_in
 * @property string $assign
 * @property integer $total_count
 * @property integer $activations
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
class TvBetPromocode extends Model
{
    //
    protected $dates = [
        'expired_in',
        'created_at',
        'updated_at',
    ];
}
