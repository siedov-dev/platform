<?php

namespace App\Models;

use App\GamesProviders\GamesManager;
use App\MUser;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

/**
 * Class TopWin
 * Сохраняет топовые выигрыши в играх
 *
 * @property int id
 * @property int user_id
 * @property int hall_id
 * @property string email
 * @property string currency
 * @property string nickname
 * @property string game_id
 * @property string game_name
 * @property float win
 * @property string provider
 * @property string system
 */
class TopWin extends Model
{
    //
    public static function userWin(MUser $user, float $sum, string $game_id, $currency, $system, $provider = '')
    {
        Log::info('user win ' . $sum);

        try {
            $threshold = 1000 * BonusCurrencyConfig::getMoneyK($currency);

            if ($sum >= $threshold) {

                /** @var TopWin $oldest_win */
                $oldest_win = TopWin::orderBy('updated_at')->first();

                $game_name = $game_id;
                $game_system = GamesManager::getProvider($system);
                if ($game_system !== null) {
                    $game = $game_system->getGame('', $game_id);
                    if ($game !== null) {
                        $game_name = $game->getName();
                        $provider = $game->getGroupId();
                    }
                }

                if ($oldest_win) {
                    $oldest_win->user_id = $user->id;
                    $oldest_win->hall_id = $user->hall_id;
                    $oldest_win->email = $user->email;
                    $oldest_win->currency = $user->getCurrencyName($currency);
                    $oldest_win->nickname = $user->login;
                    $oldest_win->game_id = $game_id;
                    $oldest_win->game_name = $game_name;
                    $oldest_win->win = $sum;
                    $oldest_win->provider = $provider?? '';
                    $oldest_win->system = $system;
                    $oldest_win->save();
                }

                GloryWin::win($user, $system, $game_id, 0, $sum, $currency);
            }
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            Log::error($e->getLine());
            Log::error($e->getFile());
            Log::error($e->getTraceAsString());
        }
    }
}
