<?php

namespace App\GamesProviders;

use App\MUser;

abstract class GameProviderApi
{
    /**
     * Fetches games and returns it.
     * The returned array can contain different data structures for each provider.
     * @return array
     */
    abstract public function getAllGames(): array;

    /**
     * Starts game session for given user.
     * @param MUser $user
     * @param string $game_id
     * @param string $session_id
     * @return string url to game
     */
    abstract public function startSession(MUser $user, string $game_id, string $session_id = ""): string;
    /**
     * Closes session for given user.
     * @param MUser $user
     * @return bool
     */
    abstract public function closeSession(MUser $user, string $session_id = ''): bool;
}
