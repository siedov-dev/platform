<?php


namespace App\GamesProviders\Slotegrator;

use App\GamesProviders\DeviceType;
use App\GamesProviders\Game;
use App\GamesProviders\GameProviderApi;
use App\GamesProviders\GamesProvider;
use App\Models\GameConfig;
use App\Services\Games\GameManagerApi;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;


class SlegGamesProvider extends GamesProvider
{
    protected $_id = 'sleg';

    public function __construct($conf = [], bool $test = false)
    {
        parent::__construct($conf, $test);
//        $this->_groupsNames = [
//            'Evolution2' => 'Evolution',
//            'CasinoTechnology' => 'CT Interactive',
//        ];

//        $this->_groupsNames = Redis::hGetAll('groupNames:'.$this->getId());
        $this->_groupsNames = GameManagerApi::getInstance()->systemGroupNamesOverloads($this->_id);
    }

    protected function fetchAllGames()
    {
        try {
            $games = GameConfig::where('system', $this->getId())->get();
            foreach ($games as $game) {
                $g = new SlegGame(
                    id: $game['game_id'],
                    name:  $game->getTitle(),
                    providerId: $this->_id,
                    groupId: $game['group'],
                    category: $game['category'],
                    deviceType: $game->getDeviceType(),
                );
                $g->setFallbackImageUrl($game['img_url']);
                $this->addGame($g);
            }
        } catch (\Exception $e) {
            Log::error('Unable to fetch pragplay games: '.$e->getMessage());
            return;
        }

//        try {
//            $slegApi = new Api();
//            $sections = $slegApi->getProviders();
//        } catch (Exception $e) {
//            Log::error('Unable to fetch sleg games: '.$e->getMessage());
//            return;
//        }
//
//
//        foreach ($sections as $groupId => $games) {
//            foreach ($games as $game) {
//                $g = new SlegGame($game['uuid'], $game['name'], $this->_id, $groupId, $game);
//                $g->setFallbackImageUrl($game['image']);
//                $this->addGame($g);
//            }
//        }
    }

    public function createGame(string $id, string $name, string $groupId, string $category = 'slots', DeviceType $deviceType = DeviceType::All): Game
    {
        return new SlegGame($id, $name, $this->_id, $groupId, $category, $deviceType);
    }



    public function getApi(): GameProviderApi
    {
        if (!isset($this->_api)) {
            $this->_api = new SlegGamesProviderApi();
        }
        return $this->_api;
    }

    public function cacheGames()
    {
        SlotegratorApi::cacheGames();
        try {
            $slegApi = new SlotegratorApi();
            $sections = $slegApi->getProviders();
        } catch (Exception $e) {
            Log::error('Unable to fetch sleg games: '.$e->getMessage());
            return;
        }

        GameConfig::where('system', $this->getId())
            ->update(['deleted' => true]);

        foreach ($sections as $groupId => $games) {
            foreach ($games as $game) {
                $all_game_ids[$game['uuid']] = true;
                $game_conf = GameConfig::where('system', $this->getId())->where('game_id', $game['uuid'])->first();
                if ($game_conf === null) {
                    $game_conf = new GameConfig();
                }
                $game_conf->system = $this->_id;
                $game_conf->game_id = $game['uuid'];
                $game_conf->group = $groupId;
                $game_conf->cid = $game_conf->system.'/'.$game_conf->group.'/'.$game_conf->game_id;
                $game_conf->name = $game['name'];
                $game_conf->img_url = $game['image']?? '';
                $game_conf->origin_data = json_encode($game);
                $game_conf->deleted = false;
                if (preg_match('#.*Mobile.*$#', $game['name'])) {
                    $game_conf->setDeviceType(DeviceType::Phone);
                } elseif ($game['is_mobile'] === 0) {
                    $game_conf->setDeviceType(DeviceType::Desktop);
                } else {
                    $game_conf->setDeviceType(DeviceType::All);
                }
                $game_conf->save();
            }
        }
    }
}
