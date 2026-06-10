<?php

namespace App\GamesProviders\Mobule;

use App\GamesProviders\DeviceType;
use App\GamesProviders\Game;
use App\GamesProviders\GameProviderApi;
use App\GamesProviders\GamesProvider;
use App\Models\GameConfig;
use App\Services\Games\GameManagerApi;
use Illuminate\Support\Facades\Log;

class MobuleGamesProvider extends GamesProvider
{
    protected $_id = GamesProvider::GAME_SYSTEM_MOBULE;

    public function __construct($conf = [], bool $test = false)
    {
        parent::__construct($conf, $test);
        $this->_groupsNames = GameManagerApi::getInstance()->systemGroupNamesOverloads($this->_id);
    }

    protected function fetchAllGames()
    {
        try {
            $games = GameConfig::where('system', $this->getId())->get();
            foreach ($games as $game) {
                $g = new MobuleGame(
                    id: $game['game_id'],
                    name: $game->getTitle(),
                    providerId: $this->_id,
                    groupId: $game['group'],
                    category: $game['category'],
                    deviceType: $game->getDeviceType(),
                );
                $g->setFallbackImageUrl($game['img_url'] ?? '');
                $this->addGame($g);
            }
        } catch (\Throwable $e) {
            Log::error('Unable to fetch mobule games: ' . $e->getMessage());
        }
    }

    public function createGame(string $id, string $name, string $groupId, string $category = 'slots', DeviceType $deviceType = DeviceType::All): Game
    {
        return new MobuleGame($id, $name, $this->_id, $groupId, $category, $deviceType);
    }

    public function getApi(): GameProviderApi
    {
        if (!isset($this->_api)) {
            $this->_api = new MobuleGamesProviderApi();
        }
        return $this->_api;
    }

    public function cacheGames()
    {
        $api = new MobuleApi();
        $items = $api->getAllGames();

        GameConfig::where('system', $this->getId())
            ->update(['deleted' => true]);

        foreach ($items as $game) {
            if (!is_array($game)) {
                continue;
            }
            $alias = $game['alias'] ?? '';
            $provider = $game['provider'] ?? 'unknown';
            if ($alias === '') {
                continue;
            }

            $gameConf = GameConfig::where('system', $this->getId())
                ->where('game_id', $alias)
                ->where('group', $provider)
                ->first();
            if ($gameConf === null) {
                $gameConf = new GameConfig();
            }

            $gameConf->system = $this->_id;
            $gameConf->game_id = $alias;
            $gameConf->group = $provider;
            $gameConf->cid = $gameConf->system . '/' . $gameConf->group . '/' . $gameConf->game_id;
            $gameConf->name = $game['title'] ?? $alias;
            $gameConf->img_url = $game['img_url'] ?? '';
//            $gameConf->category = $game['group_alias'] ?? Game::CATEGORY_SLOTS;
            $gameConf->origin_data = json_encode($game);
            $gameConf->deleted = !($game['is_enabled'] ?? true);

            $desktop = $game['desktop_enabled'] ?? true;
            $mobile = $game['mobile_enabled'] ?? true;
            if ($desktop && $mobile) {
                $gameConf->setDeviceType(DeviceType::All);
            } elseif ($desktop) {
                $gameConf->setDeviceType(DeviceType::Desktop);
            } elseif ($mobile) {
                $gameConf->setDeviceType(DeviceType::Phone);
            } else {
                $gameConf->setDeviceType(DeviceType::All);
                $gameConf->deleted = true;
            }

            $gameConf->save();
        }
    }
}
