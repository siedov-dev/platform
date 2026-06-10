<?php

namespace App\GamesProviders\Vegas;

use App\GamesProviders\GameProviderApi;
use App\MUser;
use App\GamesProviders\Vegas\Vegas;

class VegasGamesProviderApi extends GameProviderApi
{

    public function startSession(MUser $user, string $game_id, string $session_id = ""): string
    {
        // TODO: Implement startSession() method.
    }

    public function transferMoneyInGame(MUser $user, float $amount): bool
    {
        // TODO: Implement transferMoneyInGame() method.
    }

    public function transferMoneyBack(MUser $user): bool
    {
        // TODO: Implement transferMoneyBack() method.
    }

    public function closeSession(MUser $user, string $session_id = ""): bool
    {
        Vegas::closeSession();
        return true;
    }

    public function getAllGames(): array
    {
        // TODO: Implement getAllGames() method.
    }

    public function getBalance(MUser $user, string &$currency): float
    {
        // TODO: Implement getBalance() method.
    }
}
