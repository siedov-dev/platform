<?php


namespace App\GamesProviders\Drummatic;


use App\GamesProviders\DeviceType;
use App\GamesProviders\Game;
use App\GamesProviders\GameProviderApi;
use App\GamesProviders\GamesProvider;
use App\Models\GameConfig;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;


/**
 * @deprecated
 */
class DrummaticGamesProvider extends GamesProvider
{
    protected $_id = 'drummatic';

    protected function fetchAllGames()
    {
//        foreach (Config::get(app()->getLocale().'.boss_games.game_list') as $game) {
        foreach (Config::get('boss_games.game_list') as $game) {
            $g = new DrummaticGame($game, $this->getGameName($game), $this->_id, $this->_id);
            $this->addGame($g);
        }
    }

    public function createGame(string $id, string $name, string $groupId, string $category = 'slots', DeviceType $deviceType = DeviceType::All): Game
    {
        return new DrummaticGame($id, $name, $this->_id, $groupId, $category, $deviceType);
    }

    /**
     * @param $game_id
     * @return string
     */
    private function getGameName($game_id): string
    {
        $game = DB::table('sm_game_servers')->where('filename', $game_id)->first();
        return $game ? ucfirst(trim($game->name, '#')) : $game_id;
    }

    public function getApi(): GameProviderApi
    {
        if (!isset($this->_api)) {
            $this->_api = new DrummaticGamesProviderApi();
        }
        return $this->_api;
    }

    public function cacheGames()
    {
        GameConfig::where('system', $this->getId())
            ->update(['deleted' => true]);

        foreach (Config::get('boss_games.game_list') as $game) {
            $game_conf = GameConfig::where('system', $this->getId())->where('game_id', $game)->first();
            if ($game_conf === null) {
                $game_conf = new GameConfig();
                $game_conf->system = $this->_id;
                $game_conf->game_id = $game;
                $game_conf->group = $this->_id;
                $game_conf->cid = $game_conf->system . '/' . $game_conf->group . '/' . $game_conf->game_id;
                $game_conf->name = $this->getGameName($game);
                $game_conf->origin_data = json_encode($game);
                $game_conf->setDeleted(false);
                $game_conf->save();
            }
        }
    }
}
