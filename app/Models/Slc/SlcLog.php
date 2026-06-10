<?php

namespace App\Models\Slc;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $wallet_id
 * @property int $amount
 * @property string $comment
 * @deprecated перемещено в сервис KSLC
 */
final class SlcLog extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public static function log(int $wallet_id, int $amount, string $log): void
    {
        $log = new SlcLog([
                'wallet_id' => $wallet_id,
                'amount' => $amount,
                'comment' => $log,
            ]);
        $log->save();
    }
}
