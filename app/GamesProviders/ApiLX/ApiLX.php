<?php

namespace App\GamesProviders\ApiLX;

use App\GamesProviders\GameData;
use App\GamesProviders\GamesManager;
use App\GamesProviders\GamesProvider;
use App\Helpers\Hlog;
use App\Jobs\ExitGoGameJob;
use App\MUser;
use App\Services\Users\UserManager;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class ApiLX
{
    private string $agentId = '';
    private string $token = '';
    private string $secretKey = '';
    private string $url = '';

    private array $urls = [
        'PragmaticRUB' => 'https://apipk.slotgamesapi.com',
        'Pragmatic' => 'https://apipm.slotgamesapi.com',
        'PGSoft' => 'https://apigg.slotgamesapi.com',
        'Amatic' => 'https://api.amaticgame.net',
        'Amusnet' => 'https://apiang.gitamus.net',
    ];

    public function getGameList(string $lx_provider, string $currency = 'EUR'): array
    {
        if (!$this->setKeys($lx_provider, $currency)) return [];
        $url = $this->url.'/gamelist';
        return json_decode(Http::withToken($this->token)->get($url)->body(), true)['data']?? [];
    }

    public function getAllGames(): array
    {
        $games = [];
        foreach ($this->urls as $lx_provider => $url) {
            if ($lx_provider === 'PragmaticRUB') continue;
            $games = array_merge($games, $this->getGameList($lx_provider));
        }
        return $games;
    }

    public function setKeys(string $lx_provider, string $currency = 'EUR'): bool
    {
        $agentID = config("keys.apilx.{$lx_provider}.{$currency}.agentId");
        if (empty($agentID)) {
            return false;
        }
        $this->agentId = config("keys.apilx.{$lx_provider}.{$currency}.agentId");
        $this->token = config("keys.apilx.{$lx_provider}.{$currency}.token");
        $this->secretKey = config("keys.apilx.{$lx_provider}.{$currency}.key");
        if ($currency === 'RUB' && $lx_provider === 'Pragmatic') {
            $this->url = $this->urls['PragmaticRUB'];
        } else {
            $this->url = $this->urls[$lx_provider];
        }
        return true;
    }

    public function closeSession(MUser $user, string $session_id = ''): bool
    {
        try {
            if ($session_id !== '') {
                $curr_session = UserManager::getGameSession($user);
                if ($curr_session === null || $curr_session['cd'] === 200 || $curr_session['gs']['ID'] !== $session_id) return false;
            }
            $closedSess = UserManager::closeGameSession($user);
            if (!empty($closedSess) && ($closedSess['cd'] ?? 0) === 200) {
                ExitGoGameJob::dispatch(
                    $user->getId(),
                    GamesProvider::GAME_SYSTEM_APILX,
                    $closedSess['gs']['Stats']['game_id'],
                    $closedSess['gs']['Stats']['currency'],
                    $closedSess['gs']['Stats']['total_bet'],
                    $closedSess['gs']['Stats']['total_win'],
                    $closedSess['gs']['Stats']['login_balance'],
                    $closedSess['gs']['Stats']['logout_balance'],
                    $closedSess['gs']['Stats']['bonus_id']
                );
                return true;
            }
        } catch (\Throwable $e) {
            Hlog::logException($e);
        }
        return false;
    }

    public function startSession(MUser $user, string $gameId, string $sessionId = ''): string
    {
        $player_id = '' . $user->getId() . $user->getCurrencyName();
        if ($user->hasActiveVegasBonusGame($gameId) && $user->getBalanceMode() != MUser::BALANCE_MODE_MAIN) $player_id = 'bonus' . $player_id;

        if ($sessionId === '') {
            $sessionId = Str::random(32);
        }
        try {
            $this->closeSession($user);
            $provider = GamesManager::getProvider(GamesProvider::GAME_SYSTEM_APILX);
            if ($provider === null) {
                return '';
            }

            $game = $provider->getGame('', $gameId, true);
            if ($game === null) {
                return '';
            }

            $gameData = new GameData($user);
            $gameData->loadDataForGame($game->getProviderId(), $game->getId(), $user->getCurrency());
            $resp = UserManager::startGameSession($user, $game, $gameData->toaArray(), $sessionId);
            if (($resp['cd'] ?? 0) !== 200) {
                throw new \Exception($resp['err'] ?? "can't start game session");
            }
        } catch (\Throwable $e) {
            Hlog::logException($e);
            return '';
        }

        $gid = explode('_', $game->getGroupId())[0];

        $this->setKeys($gid, $user->getCurrencyName());

        $params = [
            'agentID' => $this->agentId,
            'userID' => $player_id,
            'isaffiliate' => false,
            'lang' => App::getLocale(),
            'gameid' => $gameId,
            'lobbyUrl' => action('EmulatorController@closeGameFrame'),
        ];

        $resp = Http::withToken($this->token)->post($this->url.'/userAuth', $params)->body();
        $jsonResp = json_decode($resp, true);

        return $jsonResp['url']?? '';
    }
}
