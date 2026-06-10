<?php

namespace App\Models;

use App\GamesProviders\Game;
use App\Helpers\Device;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * Отключенные игры.
 * Используется сервисом kgames.
 * @property int $id
 * @property string $device
 * @property string $type
 * @property string $cid
 * @deprecated
 */
class DisabledGame extends Model
{
    use HasFactory;

    private static array $_games = [];

    /**
     * get array with compound id's of disabled/test games
     * @param string $type
     * @return array
     */
    public static function getGames(string $type = '', string $device = ''): array
    {
        if ($type === '') $type = App::getLocale();
        if ($device === '') $device = Device::detect();

        if (!empty(self::$_games[$device][$type])) return self::$_games[$device][$type];

        $games = Cache::get($device.'disabled_games_'.$type);
        if ($games === null) {
            $games = self::where('device', $device)->where('type', $type)->get()->pluck('cid')->toArray();
            self::$_games[$device][$type]['cached'] = true;
            foreach ($games as $game) {
                if (preg_match('#^([^/]*)/\*$#', $game, $system)) {
                    self::$_games[$device][$type]['systems'][$system[1]] = 1;
                } elseif (preg_match('#^([^/]*?)/([^/]*?)/\*$#', $game, $provider)) {
                    self::$_games[$device][$type]['providers'][$provider[1]][$provider[2]] = 1;
                } else {
                    self::$_games[$device][$type]['games'][$game] = 1;
                }
            }
            Cache::put('disabled_games_'.$type, json_encode(self::$_games[$device][$type]), 300);
        } else {
            self::$_games[$device][$type] = json_decode($games, true);
        }
        return self::$_games[$device][$type];
    }

    /**
     * is game system disabled/test
     * @param string $type
     * @param string $system_id
     * @return bool
     */
    public static function isSystemDisabled(string $system_id, string $type = '', string $device = ''): bool
    {
        if ($type === '') $type = App::getLocale();
        if ($device === '') $device = Device::detect();
        if (empty(self::$_games[$device][$type])) self::getGames($type, $device);
        return isset(self::$_games[$device][$type]['systems'][$system_id]);
    }

    /**
     * is game provider disabled/test
     * @param string $type
     * @param string $system_id
     * @param string $provider also check provider's game system
     * @return bool
     */
    public static function isProviderDisabled(string $system_id, string $provider, string $type = '', bool $full_check = false, string $device = ''): bool
    {
        if ($type === '') $type = App::getLocale();
        if ($device === '') $device = Device::detect();
        if (empty(self::$_games[$device][$type])) self::getGames($type, $device);
        if ($full_check) return self::isSystemDisabled($type, $system_id, $device)
            || isset(self::$_games[$device][$type]['providers'][$system_id][$provider]);

        return isset(self::$_games[$device][$type]['providers'][$system_id][$provider]);
    }

    /**
     * is Game disabled/test
     * @param string $type
     * @param Game $game
     * @param bool $full_check also check game's game system and provider
     * @return bool
     */
    public static function isGameDisabled(Game $game, string $type ='', bool $full_check = false, string $device = ''): bool
    {
        if ($type === '') $type = App::getLocale();
        if ($device === '') $device = Device::detect();
        if (empty(self::$_games[$device][$type])) self::getGames($type, $device);
        if ($full_check) {
            if (self::isSystemDisabled($type, $game->getProviderId(), $device)
                || self::isProviderDisabled($type, $game->getProviderId(), $game->getCategory(), device: $device)) return true;
        }
        return isset(self::$_games[$device][$type]['games'][$game->getCompoundId()]);
    }
}
