<?php

namespace App\GamesProviders;

use App\MUser;
use App\Redis\RedisArrayStorage;
use App\Redis\RedisArrayStorageInterface;
use Illuminate\Support\Facades\Log;

/**
 * Class CurrentWin
 * @package App\Gameproviders
 * @deprecated
 */
class CurrentWin
{

    /**
     * store win to show in current wins scroll line
     * 20 spots limit
     * @param MUser $user
     * @param string $last_game
     * @param float $amount amount
     * @param string $currency currency name
     * @param string $game_alias game name|id
     * @param string $system gamesystem name
     * @param string $provider game provider if needed
     * @param null|float $bet
     * @return RedisArrayStorageInterface
     * @deprecated
     */
    public static function addWin(MUser $user, $last_game, float $amount, string $currency, string $game_alias, string $system, string $provider = '', $bet = null): RedisArrayStorageInterface
    {
        try {
            $storage = new RedisArrayStorage(0, 'current_wins', 16);

            if ($user && $amount > 0) {
                $win = [
                    'user_id' => $user->id,
                    'hall_id' => $user->hall_id,
                    'nik' => MUser::hideLogin($user->login),
                    'last_game' => $last_game,
                    'sum' => $amount,
                    'currency' => $currency,
                    'date' => now()->format('Y-m-d H:i:s'),
                    'game_alias' => $game_alias,
                    'bet' => $bet,
                    'provider' => $provider,
                    'system' => $system,
                ];
                $storage->addGenId($win);
            }
            return $storage;
        } catch (\Exception $e) {
            Log::error($e->getMessage());
        }
    }

    /**
     * get wins storage
     * @return RedisArrayStorageInterface
     */
    public static function getWinStorage(): RedisArrayStorageInterface
    {
        return new RedisArrayStorage(0, 'current_wins', 20);
    }
}
