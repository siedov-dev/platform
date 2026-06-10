<?php


namespace App\GamesProviders\PragmaticPlay;

use App\GamesProviders\DeviceType;
use App\GamesProviders\Game;
use App\GamesProviders\GameProviderApi;
use App\GamesProviders\GamesProvider;
use App\Models\GameConfig;
use App\Services\Games\GameManagerApi;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;


class PragmaticPlayGamesProvider extends GamesProvider
{
    protected $_id = 'pragplay';

    public function __construct($conf = [], bool $test = false)
    {
        parent::__construct($conf, $test);
        $this->_groupsNames = GameManagerApi::getInstance()->systemGroupNamesOverloads($this->_id);
//        $this->_groupsNames = Redis::hGetAll('groupNames:'.$this->getId());
//        $this->_groupsNames = [
//            'pragmatic' => 'PragmaticPlay',
//        ];
    }

    protected function fetchAllGames()
    {
        try {
            $games = GameConfig::where('system', $this->getId())->get();
            /** @var GameConfig $game */
            foreach ($games as $game) {
                $g = new PragmaticPlayGame(
                    id: $game['game_id'],
                    name:  $game->getTitle(),
                    providerId: $this->_id,
                    groupId: $game['group'],
                    category: $game['category'],
                    deviceType: $game->getDeviceType(),
                );
                $this->addGame($g);
            }
        } catch (\Exception $e) {
            Log::error('Unable to fetch pragplay games: '.$e->getMessage());
            return;
        }

//        try {
//            $api = new PragmaticPlay();
//            $sections = $api->getGames();
//        } catch (Exception $e) {
//            Log::error('Unable to fetch pragplay games: '.$e->getMessage());
//            return;
//        }
//
//
//        foreach ($sections as $game) {
//            $g = new PragmaticPlayGame($game['gameID'], $game['gameName'], $this->_id, 'pragmatic', 'slots');
//            $this->addGame($g);
//        }
    }


    public function createGame(string $id, string $name, string $groupId, string $category = 'slots', DeviceType $deviceType = DeviceType::All): Game
    {
        return new PragmaticPlayGame($id, $name, $this->_id, $groupId, $category, $deviceType);
    }



    public function getApi(): GameProviderApi
    {
        if (!isset($this->_api)) {
            $this->_api = new PragmaticPlayGamesProviderApi();
        }
        return $this->_api;
    }

    public function cacheGames()
    {
        $games = PragmaticPlay::cacheGames();

        GameConfig::where('system', $this->getId())
            ->update(['deleted' => true]);

        foreach ($games as $game) {
            /** @var GameConfig $game_conf */
            $game_conf = GameConfig::where('system', $this->getId())->where('game_id', $game['gameID'])->first();
            if ($game_conf === null) {
                $game_conf = new GameConfig();
                $game_conf->system = $this->_id;
                $game_conf->game_id = $game['gameID'];
                $game_conf->group = $game['typeDescription'] === 'Live games'? 'pragmatic_live' : 'pragmatic';
                $game_conf->cid = $game_conf->system.'/'.$game_conf->group.'/'.$game_conf->game_id;
                $game_conf->name = $game['gameName'];
                $game_conf->origin_data = json_encode($game);
                $game_conf->setDeleted(false);
                $game_conf->save();
            } else {
                $game_conf->system = $this->_id;
                $game_conf->game_id = $game['gameID'];
                $game_conf->group = $game['typeDescription'] === 'Live games'? 'pragmatic_live' : 'pragmatic';
                $game_conf->cid = $game_conf->system.'/'.$game_conf->group.'/'.$game_conf->game_id;
                $game_conf->name = $game['gameName'];
                $game_conf->origin_data = json_encode($game);
                $game_conf->setDeleted(false);
                $game_conf->save();
            }
        }
    }
}
