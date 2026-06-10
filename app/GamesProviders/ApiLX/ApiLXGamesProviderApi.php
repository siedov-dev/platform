<?php

namespace App\GamesProviders\ApiLX;

use App\GamesProviders\GameProviderApi;
use App\GamesProviders\Mobule\MobuleApi;
use App\MUser;

class ApiLXGamesProviderApi extends GameProviderApi
{
    public function getAllGames(): array
    {
        $api = new ApiLX();
        return $api->getAllGames();
    }

    public function startSession(MUser $user, string $game_id, string $session_id = ''): string
    {
        $api = new ApiLX();
        return $api->startSession($user, $game_id, $session_id);
    }

    public function closeSession(MUser $user, string $session_id = ""): bool
    {
        $api = new ApiLX();
        return $api->closeSession($user, $session_id);
    }
}
