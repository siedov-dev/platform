<?php

namespace App\GamesProviders\PragmaticPlay;

use App\GamesProviders\GameProviderApi;
use App\MUser;

class PragmaticPlayGamesProviderApi extends GameProviderApi
{

    public function startSession(MUser $user, string $game_id, string $session_id = ""): string
    {
        // TODO: Implement getAllGames() method.
    }

    public function closeSession(MUser $user, string $session_id = ""): bool
    {
        $pragmaticApi = new PragmaticPlay();
        return $pragmaticApi->closeSession($user, $session_id);
    }

    public function getAllGames(): array
    {
        // TODO: Implement getAllGames() method.
    }
}
