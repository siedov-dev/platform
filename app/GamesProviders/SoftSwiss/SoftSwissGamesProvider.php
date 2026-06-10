<?php

namespace App\GamesProviders\SoftSwiss;

use App\GamesProviders\DeviceType;
use App\GamesProviders\Game;
use App\GamesProviders\GameProviderApi;
use App\GamesProviders\GamesProvider;
use App\Models\GameConfig;
use App\Services\Games\GameManagerApi;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;
use Symfony\Component\Yaml\Yaml;

/**
 * @deprecated
 */
class SoftSwissGamesProvider extends GamesProvider
{
    /**
     * just to force test mode, delete or comment on prod
     * @param $conf
     * @param bool $test
     */
    public function __construct($conf = [], bool $test = false)
    {
        parent::__construct($conf, $test);
        $this->_groupsNames = Redis::hGetAll('groupNames:'.$this->getId());
        $this->_groupsNames = GameManagerApi::getInstance()->systemGroupNamesOverloads($this->_id);
        $this->_isTestMode = true;
    }

    protected $_id = GamesProvider::GAME_SYSTEM_SOFT_SWISS;

    public function createGame(string $id, string $name, string $groupId, string $category = 'slots', DeviceType $deviceType = DeviceType::All): Game
    {
        return new SoftSwissGame($id, $name, $this->_id, $groupId, $category, $deviceType);
    }

    protected function fetchAllGames()
    {
        try {
            $games = GameConfig::where('system', $this->getId())->get();
            foreach ($games as $game) {
                $g = new SoftSwissGame(
                    id: $game['game_id'],
                    name: $game['name'],
                    providerId: $this->_id,
                    groupId: $game['group'],
                    category: $game['category'],
                    deviceType: $game->getDeviceType(),
                );
                $this->addGame($g);
            }
        } catch (\Exception $e) {
            Log::error('Unable to fetch softswiss games: ' . $e->getMessage());
            return;
        }
    }

    public function getApi(): GameProviderApi
    {
        if (!isset($this->_api)) {
            $this->_api = new SoftSwissProviderApi();
        }
        return $this->_api;
    }

    public function cacheGames()
    {
        $this->getApi()->cacheGames();

        try {
            $games = $this->getApi()->getAllGames();
            Log::info('softswiss games fetched: ' . count($games));
        } catch (Exception $e) {
            Log::error('Unable to fetch softswiss games: ' . $e->getMessage());
            return;
        }

        GameConfig::where('system', $this->getId())
            ->update(['deleted' => true]);

        //add new games
        foreach ($games as $game) {
            $game_conf = GameConfig::where('system', $this->getId())->where('game_id', $game['identifier'])->first();
            if ($game_conf === null) {
                $game_conf = new GameConfig();
            }
            $game_conf->system = $this->_id;
            $game_conf->game_id = $game['identifier'];
            $game_conf->group = $game['producer'];
            $game_conf->cid = $game_conf->system . '/' . $game_conf->group . '/' . $game_conf->game_id;
            $game_conf->name = $game['title'];
            $game_conf->origin_data = json_encode($game);
            $game_conf->setDeleted(false);
            $game_conf->save();
        }
    }
}
