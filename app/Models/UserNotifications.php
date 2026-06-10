<?php

namespace App\Models;

use App\Helpers\Localization;
use App\MUser;
use App\Telegram\Models\TelegramUser;
use App\Telegram\TelegramBot;
use App\Viber\Models\ViberUser;
use App\Viber\ViberApi;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;

/**
 * Class UserNotification
 * @package App\Models
 * @property int id
 * @property int status
 * @property int type
 * @property int user_id
 * @property string title
 * @property string message
 * @property string link_name
 * @property string link_url
 * @deprecated
 */
class UserNotifications extends Model
{
    const STATUS_NEW = 0;
    const STATUS_SENT = 1;
    const STATUS_SHOWN = 2;
    const STATUS_DELETED = 3;

    const TYPE_REGULAR = 0;
    //

    /**
     * @param int $user_id
     * @param string $title
     * @param string $message
     * @param string $link_title
     * @param string $link_url
     * @return static
     * @deprecated
     */
    public static function notify(int $user_id, string $title, string $message, string $link_title = '', string $link_url = '')
    {
        return;
        Redis::del($user_id . 'notified');
        $notification = new static();
        $notification->user_id = $user_id;
        $notification->title = $title;
        $notification->message = $message;

        if (!empty($link_title)) {
            $notification->link_name = $link_title;
            $notification->link_url = $link_url;
        }

        try {
            $t_user = TelegramUser::where('user_id', $user_id)->first();
            /** @var MUser $m_user */
            $m_user = MUser::find($user_id);
            if ($t_user !== null && $m_user !== null) {
                Localization::setAppLocale($m_user->getLocale());
                $bot = new TelegramBot();
                $bot->sendMessage($t_user, strip_tags($message));
            }
        } catch (\Exception $e) {
            Log::channel('telegram')->error($e->getMessage());
        }

        try {
            /** @var ViberUser $v_user */
            $v_user = ViberUser::where('user_id', $user_id)->first();
            /** @var MUser $m_user */
            $m_user = MUser::find($user_id);
            if ($v_user !== null && $m_user !== null) {
                Localization::setAppLocale($m_user->getLocale());
                $v_user->sendText(strip_tags($message));
            }
        } catch (\Exception $e) {
            Log::channel('viber')->error($e->getMessage());
        }

        $notification->save();

        return $notification;
    }

    public static function clearOld()
    {
        UserNotifications::where('created_at', '<', now()->subHours(12))->delete();
    }
}
