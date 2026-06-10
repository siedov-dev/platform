<?php

namespace App\GamesProviders\Mobule;

use App\GamesProviders\GameProviderApi;
use App\MUser;

class MobuleGamesProviderApi extends GameProviderApi
{
    public function getAllGames(): array
    {
        $api = new MobuleApi();
        return $api->getAllGames();
    }

    public function startSession(MUser $user, string $game_id, string $session_id = ''): string
    {
        $api = new MobuleApi();
        return $api->startSession($user, $game_id, $session_id);
    }

    public function closeSession(MUser $user, string $session_id = ""): bool
    {
        $api = new MobuleApi();
        return $api->closeSession($user);
    }
}
