<?php

namespace App\Telegram\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class TelegramPromocodes
 * @package App\Telegram\Models
 *
 * @property int $id
 * @property int $status
 * @property int $type
 * @property string $promocode
 * @property int $bonus_type
 * @property string $bonus_params
 * @property Carbon $active_to
 * @property Carbon $sent_at
 * @property string $message
 * @property string $user_list
 * @property string $user_received
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
class TelegramPromocodes extends Model
{
    const STATUS_PENDING = 0;
    const STATUS_PROCESSING = 1;
    const STATUS_SENT = 2;
    //
    protected $dates = [
        'active_to',
        'send_at',
        'crated_at',
        'updated_at',
    ];

    public static function getStatusName(int $status): string
    {
        return match ($status) {
            self::STATUS_PENDING => 'ожидание',
            self::STATUS_PROCESSING => 'в обработке',
            self::STATUS_SENT => 'отправлен',
            default => 'unknown',
        };
    }
}
