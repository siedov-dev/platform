<?php


namespace App\GamesProviders\Vegas;


use App\GamesProviders\DeviceType;
use App\GamesProviders\Game;
use App\GamesProviders\GameProviderApi;
use App\GamesProviders\GamesProvider;
use App\Models\GameConfig;
use App\GamesProviders\Vegas\Vegas;
use Illuminate\Support\Facades\Log;

/**
 * @deprecated
 */
class VegasGamesProvider extends GamesProvider
{
    protected $_id = 'vegas';

    protected function fetchAllGames()
    {
        try {
            $games = GameConfig::where('system', $this->getId())->get();
            /** @var GameConfig $game */
            foreach ($games as $game) {
                $g = new VegasGame(
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
    }


    public function createGame(string $id, string $name, string $groupId, string $category = 'slots', DeviceType $deviceType = DeviceType::All): Game
    {
        return new VegasGame($id, $name, $this->_id, $groupId, $category, $deviceType);
    }

    public function getApi(): GameProviderApi
    {
        if (!isset($this->_api)) {
            $this->_api = new VegasGamesProviderApi();
        }
        return $this->_api;
    }

    public function cacheGames()
    {
        Vegas::cacheGames();

        try {
            $sections = Vegas::getGameSections();
        } catch (\Exception $e) {
            Log::error('Unable to fetch vegas games. ' . $e->getMessage());
            return;
        }

        GameConfig::where('system', $this->getId())
            ->update(['deleted' => true]);

        foreach ($sections as $groupId => $games) {
            foreach ($games as $game) {
                $game_conf = GameConfig::where('system', $this->getId())->where('game_id', $game['Id'])->first();
                if ($game_conf === null) {
                    $game_conf = new GameConfig();
                }
                $game_conf->system = $this->_id;
                $game_conf->game_id = $game['Id'];
                $game_conf->group = $groupId;
                $game_conf->cid = $game_conf->system.'/'.$game_conf->group.'/'.$game_conf->game_id;
                $game_conf->name = $game['Name'];
                $game_conf->origin_data = json_encode($game);
                $game_conf->setDeleted(false);
                $game_conf->save();
            }
        }
    }
}
