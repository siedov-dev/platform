<?php

namespace App\Telegram\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;

/**
 * Class TelegramStat
 * @package App\Telegram\Models
 * @property int $id
 * @property Carbon $stat_date
 * @property string $lang
 * @property int $join
 * @property int $connected
 * @property int $commands
 * @property int $messages
 * @property int $images
 * @property int $promos_created
 * @property int $blocked
 * @property int $errors
 * @property int $total_users
 */
class TelegramStat extends Model
{
    protected $dates = ['stat_date'];
    //
    public static function getCurrentStat($lang =  null): TelegramStat
    {
        $date = now()->format('Y-m-d');
        $stat = TelegramStat::where('stat_date', $date)
            ->where('lang', $lang?? App::getLocale())
            ->first();
        if ($stat === null) {
            $stat = new TelegramStat();
            $stat->stat_date = $date;
            $stat->total_users = TelegramUser::where('lang', App::getLocale())->count();
            $stat->lang = App::getLocale();
            $stat->save();
        }
        return $stat;
    }

    public static function incJoin()
    {
        try {
            $stat = TelegramStat::getCurrentStat('ru');
            $stat->increment('join');
            $stat->save();
        } catch (\Exception $e) {
            Log::channel('telegram')->error($e->getMessage());
        }
    }

    public static function incConnected()
    {
        try {
            TelegramStat::getCurrentStat()->increment('connected');
        } catch (\Exception $e) {
            Log::channel('telegram')->error($e->getMessage());
        }
    }

    public static function incCommands()
    {
        try {
            TelegramStat::getCurrentStat()->increment('commands');
        } catch (\Exception $e) {
            Log::channel('telegram')->error($e->getMessage());
        }
    }

    public static function incMessages()
    {
        try {
            TelegramStat::getCurrentStat()->increment('messages');
        } catch (\Exception $e) {
            Log::channel('telegram')->error($e->getMessage());
        }
    }

    public static function incImages()
    {
        try {
            TelegramStat::getCurrentStat()->increment('images');
        } catch (\Exception $e) {
            Log::channel('telegram')->error($e->getMessage());
        }
    }

    public static function incPromos()
    {
        try {
            TelegramStat::getCurrentStat()->increment('promos_created');
        } catch (\Exception $e) {
            Log::channel('telegram')->error($e->getMessage());
        }
    }

    public static function incBlocked()
    {
        try {
            TelegramStat::getCurrentStat()->increment('blocked');
        } catch (\Exception $e) {
            Log::channel('telegram')->error($e->getMessage());
        }
    }

    public static function incErrors()
    {
        try {
            TelegramStat::getCurrentStat()->increment('errors');
        } catch (\Exception $e) {
            Log::channel('telegram')->error($e->getMessage());
        }
    }

}
