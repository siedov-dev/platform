<?php


namespace App\GamesProviders\B2BSlots;

use App\GamesProviders\DeviceType;
use App\GamesProviders\Game;
use App\GamesProviders\GameProviderApi;
use App\GamesProviders\GamesManager;
use App\GamesProviders\GamesProvider;
use App\GamesProviders\Qt\QtGame;
use App\GamesProviders\Qt\QtGamesProviderApi;
use App\Helpers\Hlog;
use App\Models\GameConfig;
use App\Services\Games\GameManagerApi;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;


class B2BSlotsGamesProvider extends GamesProvider
{
    protected $_id = 'b2bslots';

    public function __construct($conf = [], bool $test = false)
    {
        parent::__construct($conf, $test);
//        $this->_groupsNames = [];
        $this->_groupsNames = GameManagerApi::getInstance()->systemGroupNamesOverloads($this->_id);
    }

    protected function fetchAllGames()
    {
        try {
            $games = GameConfig::where('system', $this->getId())->get();
            /** @var GameConfig $game */
            foreach ($games as $game) {
                $g = new B2BSlotsGame(
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
            Log::error('Unable to fetch qt games: '.$e->getMessage());
            return;
        }
    }

    public function createGame(string $id, string $name, string $groupId, string $category = 'slots', DeviceType $deviceType = DeviceType::All): Game
    {
        return new B2BSlotsGame($id, $name, $this->_id, $groupId, $category, $deviceType);
    }

    public function getApi(): GameProviderApi
    {
        if (!isset($this->_api)) {
            $this->_api = new B2BSlotsApi();
        }
        return $this->_api;
    }

    public function cacheGames()
    {
        try {
            $api = $this->getApi();
            $resp = $api->getAllGames();

            GameConfig::where('system', $this->getId())
                ->update(['deleted' => true]);

            if (!empty($resp)) {
                $ico_base_url = $resp['locator']['ico_baseurl'];
                foreach ($resp['locator']['groups'] as $group) {
                    foreach ($group['games'] as $game) {
                        /** @var GameConfig $game_conf */
                        $game_conf = GameConfig::where('system', $this->getId())->where('game_id', $game['gm_bk_id'])->first();
                        if ($game_conf === null) {
                            $game_conf = new GameConfig();
                        }
                        $game_conf->system = $this->_id;
                        $game_conf->game_id = $game['gm_bk_id'];
                        $game_conf->group = $group['gr_title'];
                        $game_conf->cid = $game_conf->system.'/'.$game_conf->group.'/'.$game_conf->game_id;
                        $game_conf->name = $game['gm_title'];
                        $game_conf->origin_data = json_encode($game);
                        $game_conf->img_url = $ico_base_url.$game['icons'][0]['ic_name']?? '';
                        $game_conf->setDeleted(false);
                        if (!$game['gm_is_mobile']) {
                            $game_conf->setDeviceType(DeviceType::Desktop);
                        } elseif (!$game['gm_is_pc']) {
                            $game_conf->setDeviceType(DeviceType::Phone);
                        } else {
                            $game_conf->setDeviceType(DeviceType::All);
                        }
                        $game_conf->save();
                    }
                }
            }
        } catch (\Throwable $e) {
            Hlog::logException($e);
        }
    }
}
