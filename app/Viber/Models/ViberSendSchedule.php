<?php

namespace App\Viber\Models;

use App\Models\TelegramSendSchedule;
use App\Telegram\TelegramBot;
use App\Viber\ViberApi;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

/**
 * Class ViberSendSchedule
 * @package App\Viber\Models
 * @property int $id
 * @property int $status
 * @property Carbon $send_at
 * @property string $text
 * @property string $media_type
 * @property string $file_url
 * @property string $to_users
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
class ViberSendSchedule extends Model
{
    //
    const STATUS_PENDING = 0;
    const STATUS_SENDING = 1;
    const STATUS_SENT = 2;
    const STATUS_ERROR = 3;

    //
    protected $dates = [
        'send_at',
        'created_at',
        'updated_at',
    ];

    public static function sendMessages()
    {
        set_time_limit(300);
        $schedules = self::where('status', self::STATUS_PENDING)
            ->where('send_at', '<', now()->format('Y-m-d H:i:s'))
            ->get();

        /** @var ViberSendSchedule $schedule */
        foreach ($schedules as $schedule) {
            $schedule->status = self::STATUS_SENDING;
            $schedule->save();

            $user_ids = json_decode($schedule->to_users, true);
            $v_users = ViberUser::whereIn('user_id', $user_ids)->get();

            /** @var ViberUser $v_user */
            foreach ($v_users as $v_user) {
                try {
                    if ($schedule->media_type === 'image') {
                        $v_user->sendImage($schedule->file_url, $schedule->text);
                    } else {
                        $v_user->sendText($schedule->text);
                    }
                } catch (\Exception $e) {
                    Log::channel('viber')->error($e->getMessage());
                }
            }

            $schedule->status = self::STATUS_SENT;
            $schedule->save();
        }
    }
}
