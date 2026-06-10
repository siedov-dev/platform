<?php

namespace App\GamesProviders\ApiLX;

use App\GamesProviders\DeviceType;
use App\GamesProviders\Game;
use App\GamesProviders\GameProviderApi;
use App\GamesProviders\GamesProvider;
use App\Models\ApiLxPragmaticGameBets;
use App\Models\GameConfig;
use App\Wallet;
use Illuminate\Support\Facades\Log;

class ApiLXProvider extends GamesProvider
{
    protected $_id = GamesProvider::GAME_SYSTEM_APILX;
    private array $_lx_providers = ['Pragmatic', 'PGSoft', 'Amatic', 'Amusnet'];

    public function createGame(string $id, string $name, string $groupId, string $category = 'slots', DeviceType $deviceType = DeviceType::All): Game
    {
        return new ApiLXGame($id, $name, $this->_id, $groupId, $category, $deviceType);
    }

    protected function fetchAllGames()
    {
        try {
            $games = GameConfig::where('system', $this->getId())->get();
            foreach ($games as $game) {
                $g = new ApiLXGame(
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

    public function getApi(): GameProviderApi
    {
        return new ApiLXGamesProviderApi();
    }

    public function cacheGames()
    {
        GameConfig::where('system', $this->getId())
            ->update(['deleted' => true]);
        $api = new ApiLX();
        foreach ($this->_lx_providers as $lx_provider) {
            $games = $api->getGameList($lx_provider);
            foreach ($games as $game) {
                if (!is_array($game)) {
                    continue;
                }
                $alias = $game['gameid'] ?? '';
                $provider = $lx_provider."_".($game['vendorid'] ?? 'unknown');
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
                $gameConf->name = $game['name'] ?? $alias;
                $gameConf->img_url = $game['iconurl2'] ?? $game['iconurl1']?? '';
                $gameConf->origin_data = json_encode($game);
                $gameConf->deleted = false;

                $gameConf->save();
            }
        }

        foreach (Wallet::activeCurrencies() as $currency) {
            $currName = Wallet::currencyName($currency);
            $games = $api->getGameList('Pragmatic', $currName);
            foreach ($games as $game) {
                if (!is_array($game)) continue;
                ApiLxPragmaticGameBets::updateOrCreate(
                    ['game_id' => $game['gameid'], 'currency' => $currName],
                    [
                        'game_id' => $game['gameid'],
                        'currency' => $currName,
                        'min_bet' => $game['miniBet']?? 1,
                        'max_bet_level' => $game['maxlevel']?? 1,
                    ]
                );
            }
        }
    }
}
