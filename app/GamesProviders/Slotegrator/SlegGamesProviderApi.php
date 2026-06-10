<?php

namespace App\GamesProviders\Slotegrator;

use App\GamesProviders\GameProviderApi;
use App\MUser;

class SlegGamesProviderApi extends GameProviderApi
{

    public function startSession(MUser $user, string $game_id, string $session_id = ""): string
    {
        $slegApi = new SlotegratorApi();
        return $slegApi->initGame($game_id, $user)['url']?? '';
    }

    public function closeSession(MUser $user, string $session_id = ""): bool
    {
        $slegApi = new SlotegratorApi();
        return $slegApi->closeSession($user, $session_id);
    }

    public function getAllGames(): array
    {
        // TODO: Implement getAllGames() method.
    }

}
