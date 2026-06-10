<?php

namespace App\GamesProviders\TVBet;

use App\GamesProviders\DeviceType;
use App\GamesProviders\Game;
use App\GamesProviders\GameProviderApi;
use App\GamesProviders\GamesProvider;
use App\Models\GameConfig;
use Illuminate\Support\Facades\Log;

class TVBetGamesProvider extends GamesProvider
{
    protected $_id = 'tv-bet';

    private $_gamesIds = [2, 3, 5, 6, 7, 9, 10, 12, 19, 23, 22, 25];

    public function __construct($conf = [], bool $test = false)
    {
        parent::__construct($conf, $test);
        $this->_groupsNames = [
            'tv-bet' => 'TV Bet',
        ];
    }

    public function createGame(string $id, string $name, string $groupId, string $category = 'slots', DeviceType $deviceType = DeviceType::All): Game
    {
        return new TVBetGame($id, $name, $this->_id, $this->_id, $category, $deviceType);
    }

    protected function fetchAllGames()
    {
        try {
            $games = GameConfig::where('system', $this->getId())->get();
            foreach ($games as $game) {
                $g = new TVBetGame($game['game_id'], __('tvbet.gameName_'.$game['game_id']), $this->_id, $game['group'], $game['category']);
                $this->addGame($g);
            }
        } catch (\Exception $e) {
            Log::error('Unable to fetch pragplay games: '.$e->getMessage());
            return;
        }
//        foreach ($this->_gamesIds as $id) {
//            $game = new TVBetGame($id, __('tvbet.gameName_'.$id), $this->_id, $this->_id);
//            $this->addGame($game);
//        }
    }

    public function getApi(): GameProviderApi
    {
        if ($this->_api === null) {
            $this->_api = new TVBetGamesProviderApi();
        }
        return $this->_api;
    }

    public function cacheGames()
    {
        GameConfig::where('system', $this->getId())
            ->update(['deleted' => true]);

        foreach ($this->_gamesIds as $id) {
            $game_conf = GameConfig::where('system', $this->getId())->where('game_id', $id)->first();
            if ($game_conf === null) {
                $game_conf = new GameConfig();
            }
            $game_conf->system = $this->_id;
            $game_conf->game_id = $id;
            $game_conf->group = $this->_id;
            $game_conf->cid = $game_conf->system.'/'.$game_conf->group.'/'.$game_conf->game_id;
            $game_conf->name = __('tvbet.gameName_'.$id);
            $game_conf->setDeleted(false);
            $game_conf->save();
        }
    }
}
