<?php

namespace App\Models\TvBet;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $bid
 * @property int $tt
 * @property int $user_id
 * @property string $si
 * @property string $to
 * @property float $sm
 * @property string $cc
 * @property int $ti
 * @property string $pr
 * @property string $ed
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
class TvBetTransaction extends Model
{
    use HasFactory;

    const TYPE_BET = -1;
    const TYPE_PAYMENT = 1;
    const TYPE_REFUND = 2;
    const TYPE_CANCEL = -2;
    const TYPE_JACKPOT = 4;

    protected $dates = [
        'created_at',
        'updated_at',
    ];
}
