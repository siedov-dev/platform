<?php

namespace App\Viber\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

/**
 * Class ViberStat
 * @package App\Viber\Models
 * @property int $id
 * @property Carbon $stat_date
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
class ViberStat extends Model
{
    //
    protected $dates = ['stat_date'];
    //
    public static function getCurrentStat(): ViberStat
    {
        $date = now()->format('Y-m-d');
        $stat = ViberStat::where('stat_date', $date)->first();
        if ($stat === null) {
            $stat = new ViberStat();
            $stat->stat_date = $date;
            $stat->total_users = ViberUser::count();
            $stat->save();
        }
        return $stat;
    }

    public static function incJoin()
    {
        try {
            $stat = ViberStat::getCurrentStat();
            $stat->increment('join');
            $stat->total_users = ViberUser::count();
            $stat->save();

        } catch (\Exception $e) {
            Log::channel('telegram')->error($e->getMessage());
        }
    }

    public static function incConnected()
    {
        try {
            ViberStat::getCurrentStat()->increment('connected');
        } catch (\Exception $e) {
            Log::channel('telegram')->error($e->getMessage());
        }
    }

    public static function incCommands()
    {
        try {
            ViberStat::getCurrentStat()->increment('commands');
        } catch (\Exception $e) {
            Log::channel('telegram')->error($e->getMessage());
        }
    }

    public static function incMessages()
    {
        try {
            ViberStat::getCurrentStat()->increment('messages');
        } catch (\Exception $e) {
            Log::channel('telegram')->error($e->getMessage());
        }
    }

    public static function incImages()
    {
        try {
            ViberStat::getCurrentStat()->increment('images');
        } catch (\Exception $e) {
            Log::channel('telegram')->error($e->getMessage());
        }
    }

    public static function incPromos()
    {
        try {
            ViberStat::getCurrentStat()->increment('promos_created');
        } catch (\Exception $e) {
            Log::channel('telegram')->error($e->getMessage());
        }
    }

    public static function incBlocked()
    {
        try {
            ViberStat::getCurrentStat()->increment('blocked');
        } catch (\Exception $e) {
            Log::channel('telegram')->error($e->getMessage());
        }
    }

    public static function incErrors()
    {
        try {
            ViberStat::getCurrentStat()->increment('errors');
        } catch (\Exception $e) {
            Log::channel('telegram')->error($e->getMessage());
        }
    }
}
