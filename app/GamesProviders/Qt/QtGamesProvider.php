<?php


namespace App\GamesProviders\Qt;

use App\GamesProviders\DeviceType;
use App\GamesProviders\Game;
use App\GamesProviders\GameProviderApi;
use App\GamesProviders\GamesProvider;
use App\Models\GameConfig;
use App\Services\Games\GameManagerApi;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;


class QtGamesProvider extends GamesProvider
{
    protected $_id = 'qt';

    public function __construct($conf = [], bool $test = false)
    {
        parent::__construct($conf, $test);
        $this->_groupsNames = GameManagerApi::getInstance()->systemGroupNamesOverloads($this->_id);
//        $this->_groupsNames = Redis::hGetAll('groupNames:'.$this->getId());
//        $this->_groupsNames = [
//            'EVP' => 'EvoPlay',
//            'HAB' => 'Habanero',
//            'GA' => 'GameArt',
//            'WAZ' => 'Wazdan',
//            'OT' => 'OneTouch',
//            'VGL' => 'Vivogaming',
//            'NGE' => 'NetGame',
//            'ADL' => 'Ad Lunam',
//            'BTV' => 'BetGames.TV',
//            'PRG' => 'Prospect Gaming',
//            'SAG' => 'SA Gaming',
//            'MNP' => 'Manna Play',
//            'AUX' => 'Avatar UX',
//            'NE' => 'Netent',
//            'BNG' => 'Booongo',
//            'BTL' => 'BETER Live',
//            'RLG' => 'Reloaded Gaming',
//            'TPG' => 'Triple PG',
//            'FUG' => 'FUGASO',
//            'BPG' => 'Blueprint Gaming',
//            'GZX' => 'Gamzix',
//            'EBT' => 'eBET',
//            'MAV' => 'ELYSIUM',
//            'FNG' => 'Fantasma Games',
//            'EZU' => 'Ezugi',
//            'YGG' => 'Yggdrasil',
//            'TK' => 'Thunderkick',
//            'PP' => 'PlayPearls',
//            'DS' => 'Dragon soft',
//            'GFG' => 'Gamefish Global',
//            'KGL' => 'Kalamba Games',
//            'NLC' => 'Nolimit City',
//            'SHS' => 'Spearhead StudiosCity',
//            'PUG' => 'PushGaming',
//            'HAK' => 'Hacksaw Gaming',
//            'SPR' => 'Splitrock',
//            'MOB' => 'Mobilots',
//            'OMI' => 'OMI Gaming',
//            'SM' => 'SlotMill',
//            'BBG' => 'BB Games',
//            'LL' => 'Lady Luck',
//            'TRB' => 'Turbogames',
//        ];
    }

    protected function fetchAllGames()
    {
        try {
            $games = GameConfig::where('system', $this->getId())->get();
            /** @var GameConfig $game */
            foreach ($games as $game) {
                $g = new QtGame(
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
        return new QtGame($id, $name, $this->_id, $groupId, $category, $deviceType);
    }

    public function getApi(): GameProviderApi
    {
        if (!isset($this->_api)) {
            $this->_api = new QtGamesProviderApi();
        }
        return $this->_api;
    }

    public function cacheGames()
    {
        QtGamesProviderApi::cacheGames();

        try {
            $sections = $this->getApi()->getAllGames();
            Log::info('qt games fetched: '.count($sections));
        } catch (Exception $e) {
            Log::error('Unable to fetch QT games: '.$e->getMessage());
            return;
        }

        GameConfig::where('system', $this->getId())
            ->update(['deleted' => true]);

        foreach ($sections as $game) {
            $game_conf = GameConfig::where('system', $this->getId())->where('game_id', $game['id'])->first();
            if ($game_conf === null) {
                $game_conf = new GameConfig();
            }
            $game_conf->system = $this->_id;
            $game_conf->game_id = $game['id'];
            $game_conf->group = $game['provider'];
            $game_conf->cid = $game_conf->system.'/'.$game_conf->group.'/'.$game_conf->game_id;
            $game_conf->name = $game['name'];
            $game_conf->origin_data = json_encode($game);
            $game_conf->setDeleted(false);
            $game_conf->save();
        }
    }
}
