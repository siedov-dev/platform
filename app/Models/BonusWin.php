<?php

namespace App\Models;

use App\GamesProviders\GamesManager;
use App\MUser;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

/**
 * Class BonusWin
 * Сохраняет информацию о последних выигрышах на бонусах.
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
 * @deprecated
 */
class BonusWin extends Model
{
    //
    public static function userWin(MUser $user, float $sum, string $game_id, $currency, $system, $provider = '')
    {
        Log::info('user bonus win ' . $sum . ' game' . $game_id);
        if (empty($game_id)) return;

        if ($sum > 0) {

            /** @var TopWin $oldest_win */
            $oldest_win = BonusWin::orderBy('updated_at')->first();
            $provider = '';

            $game_name = $game_id;

            $g_provider = GamesManager::getProvider($system);
            if ($g_provider !== null) {
                $game = $g_provider->getGame('', $game_id, true);
                if ($game !== null) $game_name = $game->getName();
            }

            if ($oldest_win) {
                $oldest_win->user_id = $user->id;
                $oldest_win->hall_id = $user->hall_id;
                $oldest_win->email = $user->email??'';
                $oldest_win->currency = $currency;
                $oldest_win->nickname = $user->login;
                $oldest_win->game_id = $game_id;
                $oldest_win->game_name = $game_name;
                $oldest_win->win = $sum;
                $oldest_win->provider = $provider;
                $oldest_win->system = $system;
                $oldest_win->save();
            }
        }
    }

    public function getImageUrl(): string
    {
        $provider = GamesManager::getProvider($this->system);
        if ($provider !== null) {
            $game = $provider->getGame('', $this->game_id, true);
            if ($game !== null) return $game->getImageUrl();
        }
        return '';
    }
}
