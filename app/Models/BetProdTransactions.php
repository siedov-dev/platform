<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Транзакции Bet-production
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
class BetProdTransactions extends Model
{
    use HasFactory;

    const TYPE_BET = 1;
    const TYPE_WIN = 2;
    const TYPE_LOSE = 3;
    const TYPE_RETURN = 4;
    const TYPE_REFUND = 5;
    const TYPE_CASHOUT = 6;

    protected $dates = [
        'created_at',
        'updated_at',
    ];

    public static function getTypeName(int $type): string
    {
        return match ($type) {
            self::TYPE_BET => __('betprod.bet'),
            self::TYPE_WIN => __('betprod.win'),
            self::TYPE_LOSE => __('betprod.loose'),
            self::TYPE_REFUND => __('betprod.refund'),
            self::TYPE_RETURN => __('betprod.return'),
            self::TYPE_CASHOUT => __('betprod.cashout'),
            default => 'неизвестно',
        };
    }
}
