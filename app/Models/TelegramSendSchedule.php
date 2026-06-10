<?php

namespace App\Models;

use App\Models\Crm\TelegramClient;
use App\Telegram\Models\TelegramUser;
use App\Telegram\TelegramBot;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

/**
 * Class TelegramSendSchedule
 * Планировщик отправки сообщений телеграмм.
 *
 * @property int $id
 * @property int $status
 * @property Carbon $send_at
 * @property string $text
 * @property string $buttons
 * @property string $media_type
 * @property string $file_data
 * @property string $to_channels
 * @property string $to_users
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property int $tg_type
 */
class TelegramSendSchedule extends Model
{
    const STATUS_PENDING = 0;
    const STATUS_SENDING = 1;
    const STATUS_SENT = 2;
    const STATUS_ERROR = 3;

    const TG_TYPE_DEFAULT = 0;
    const TG_TYPE_MANAGERS = 1;

    //
    protected $dates = [
        'send_at',
        'created_at',
        'updated_at',
    ];

    public static function sendMessages()
    {
        set_time_limit(600);
        $schedules = self::where('status', self::STATUS_PENDING)
            ->where('send_at', '<', now()->format('Y-m-d H:i:s'))
            ->limit(3)
            ->get();


        /** @var TelegramSendSchedule $schedule */
        foreach ($schedules as $schedule) {

            $schedule->refresh();
            if ($schedule->status !== self::STATUS_PENDING) continue;

            $schedule->status = self::STATUS_SENDING;
            $schedule->save();

            $token = $schedule->tg_type == self::TG_TYPE_DEFAULT ? config('telegram_bot.token') : config('manager_bot.token');
            $bot = new TelegramBot($token);

            $channels = json_decode($schedule->to_channels, true);
            $users = json_decode($schedule->to_users, true);

            $send_ids = array_merge($channels ?? [], $users ?? []);

            $buttons = json_decode($schedule->buttons, true) ?? [];

            foreach ($send_ids as $t_id) {
                try {
                    if ($t_id === 'all') {
                        if ( $schedule->tg_type == self::TG_TYPE_DEFAULT) {
                            $qr = TelegramUser::query();
                            $tgid = 'telegram_id';
                        } else {
                            $qr = TelegramClient::query();
                            $tgid = 'tg_id';
                        }
                        $qr->chunk(500, function ($users) use ($tgid, $bot, $schedule, $buttons, $token) {
                            $ids = $users->pluck($tgid)->toArray();
                            if (!empty($ids)) {
                                foreach ($ids as $id) {
                                    if (empty($id)) continue;
                                    if ($schedule->media_type === 'image') {
                                        $bot::sendChatPhotoData($schedule->file_data, $schedule->text, $id, $buttons, $token);
                                    } elseif ($schedule->media_type === 'image_url') {
                                        $bot::sendChatPhoto($schedule->file_data, $schedule->text, $id, $buttons, $token);
                                    } else {
                                        $bot::sendChatMessage($schedule->text, $id, $buttons, $token);
                                    }
                                }
                            }
                        });
                    } else {
                        if ($schedule->media_type === 'image') {
                            $bot::sendChatPhotoData($schedule->file_data, $schedule->text, $t_id, $buttons, $token);
                        } elseif ($schedule->media_type === 'image_url') {
                            $bot::sendChatPhoto($schedule->file_data, $schedule->text, $t_id, $buttons, $token);
                        } else {
                            $bot::sendChatMessage($schedule->text, $t_id, $buttons, $token);
                        }
                    }
                } catch (\Exception $e) {
                    Log::channel('telegram')->error($e->getMessage());
                }
            }

            $schedule->status = self::STATUS_SENT;
            $schedule->save();
        }
    }

    public function getStatusName()
    {
        switch ($this->status) {
            case self::STATUS_PENDING:
                return 'ожидание';
            case self::STATUS_SENDING:
                return 'отправка';
            case self::STATUS_SENT:
                return 'отправлено';
            case self::STATUS_ERROR:
                return 'ошибка';
        }
        return 'unknown';
    }

    public function getTypeName()
    {
        return match($this->tg_type) {
            self::TG_TYPE_DEFAULT => 'телеграм',
            self::TG_TYPE_MANAGERS => 'Менеджер чат',
        };
    }
}
