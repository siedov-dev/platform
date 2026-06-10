<?php

namespace App\Models;

use App\Helpers\Hlog;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class EmailSchedule
 * Планировщик отправки писем.
 *
 * @property int $id
 * @property string $subject
 * @property integer $status
 * @property string $emails
 * @property string $html
 * @property Carbon $send_at
 * @property string $mail_system
 */
class EmailSchedule extends Model
{
    //
    const STATUS_PENDING = 0;
    const STATUS_ACTIVE = 1;
    const STATUS_SENT = 2;
    const STATUS_ERROR = 3;

    public static function process()
    {
        /** @var EmailSchedule $schedule */
        $schedule = static::where('status', static::STATUS_PENDING)
            ->where('send_at', '<', date('Y-m-d H:i:s'))
            ->first();

        if ($schedule !== null) {
            $schedule->status = self::STATUS_ACTIVE;
            $schedule->save();

            try {
                $emails = json_decode($schedule->emails, true);


                if (!is_array($emails)) {
                    $schedule->status = self::STATUS_ERROR;
                    $schedule->save();
                    return;
                }

                $letter = new \App\Email([
                    'to' => $emails,
                    'subject' => $schedule->subject,
                    'text' => $schedule->html,
                    'html' => $schedule->html,
                ], $schedule->mail_system);

                $letter->send();

                $schedule->status = self::STATUS_SENT;
                $schedule->save();
            } catch (\Throwable $e) {
                Hlog::logException($e);
                $schedule->status = self::STATUS_ERROR;
                $schedule->save();
            }
        }
    }

    public function getStatusName()
    {
        switch ($this->status) {
            case self::STATUS_PENDING:
                return 'ожидание';
            case self::STATUS_ACTIVE:
                return 'отправка';
            case self::STATUS_SENT:
                return 'отправлено';
            case self::STATUS_ERROR:
                return 'ошибка';
        }
        return 'unknown';
    }
}
