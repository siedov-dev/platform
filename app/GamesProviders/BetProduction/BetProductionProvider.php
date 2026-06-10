<?php

namespace App\GamesProviders\BetProduction;

use App\GamesProviders\DeviceType;
use App\GamesProviders\Game;
use App\GamesProviders\GameProviderApi;
use App\GamesProviders\GamesProvider;

class BetProductionProvider extends GamesProvider
{
    protected $_id = GamesProvider::GAME_SYSTEM_BET_PRODUCTION;

    private array $_gamesIds = [];

    public function createGame(string $id, string $name, string $groupId, string $category = 'slots', DeviceType $deviceType = DeviceType::All): Game
    {
        return new BetProductionGame($id, $name, $this->_id, $this->_id, $category, $deviceType);
    }

    protected function fetchAllGames()
    {
        foreach ($this->_gamesIds as $id) {
            $game = new BetProductionGame($id, __('betprod.gameName_' . $id), $this->_id, $this->_id, Game::CATEGORY_SPORT);
            $this->addGame($game);
        }
    }

    public function getApi(): GameProviderApi
    {
        if ($this->_api === null) {
            $this->_api = new BetProductionApi();
        }
        return $this->_api;
    }

    public function cacheGames()
    {

    }
}
