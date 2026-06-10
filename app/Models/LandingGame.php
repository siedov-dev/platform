<?php

namespace App\Models;

use App\Helpers\Localization;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Cache;

/**
 * Игры отображаемые на лендинге.
 * Используются games_manager сервисом.
 * @property int $id
 * @property string $type
 * @property string $lang
 * @property string $cid
 */
class LandingGame extends Model
{
    use HasFactory;

    private static array $_games = [];

    /**
     * get array with compound id's of disabled/test games
     * @param string $type
     * @return array
     * @deprecated
     */
    private static function getGames(string $category, $locale = ''): array
    {
        $locale = Localization::getConfigLocale($locale);
        if (!empty(self::$_games[$locale])) return self::$_games[$locale][$category]?? [];

//        $games = Cache::get('landing_games_'.$locale);
//        if ($games === null) {
            $games = self::where('lang', $locale)->get();
            self::$_games[$locale]['cached'] = true;
            foreach ($games as $game) {
                //to use isset as the fastest array search
                self::$_games[$locale][$game->type][$game->cid] = 1;
            }
//            Cache::put('landing_games_'.$locale, json_encode(self::$_games[$locale]), 300);
//        } else {
//            self::$_games[$locale] = json_decode($games, true);
//        }
        return self::$_games[$locale][$category]?? [];
    }

    /**
     * @param string $category
     * @param $locale
     * @return array
     * @deprecated
     */
    public static function getGamesByCategory(string $category, $locale = ''): array
    {
        $games = self::getGames($category, $locale);
        return array_keys($games);
    }

    /**
     * @param string $cid
     * @param string $category
     * @param $locale
     * @return bool
     * @deprecated
     */
    public static function isGameCategory(string $cid, string $category, $locale = ''): bool
    {
        $locale = Localization::getConfigLocale($locale);
        if (empty(self::$_games[$locale])) self::getGames($category);
        return isset(self::$_games[$locale][$category][$cid]);
    }
}
