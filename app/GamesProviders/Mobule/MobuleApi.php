<?php

namespace App\GamesProviders\Mobule;

use App\GamesProviders\GameData;
use App\GamesProviders\GamesManager;
use App\GamesProviders\GamesProvider;
use App\Helpers\Device;
use App\Helpers\Hlog;
use App\Jobs\ExitGoGameJob;
use App\Models\Bonuses\KUserBonus;
use App\MUser;
use App\Services\Users\UserManager;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class MobuleApi
{
    private string $partnerAlias = '';
    private string $secretKey = '';
    private string $baseUrl = '';

    public function __construct()
    {
        $this->partnerAlias = (string) config('keys.mobule.alias');
        $this->secretKey = (string) config('keys.mobule.secret');
        $this->baseUrl = rtrim((string) config('keys.mobule.base_url'), '/');
    }

    public function getAllGames(): array
    {
        $response = $this->post('games.list', [], true);
        if (!is_array($response)) {
            return [];
        }
        if (($response['status'] ?? 0) !== 200) {
            Log::warning('mobule games.list error', $response);
            return [];
        }
        return $response['response'] ?? [];
    }

    public function startDemoGame(string $provider, string $gameAlias, string $currency, string $lobbyUrl = ''): string
    {
        $params = [
            'partner.alias' => $this->partnerAlias,
            'game.provider' => $provider,
            'game.alias' => $gameAlias,
            'currency' => $currency,
            'mobile' => Device::detect() == Device::PHONE ? 'true' : 'false',
            'lang' => App::getLocale(),
        ];

        if ($lobbyUrl !== '') {
            $params['lobby_url'] = $lobbyUrl;
        }

        return $this->baseUrl . '/games.startDemo?' . http_build_query($params);
    }

    public function startSession(MUser $user, string $gameId, string $sessionId = ''): string
    {
        if ($sessionId === '') {
            $sessionId = Str::random(32);
        }
        try {
            $this->closeSession($user);
            $provider = GamesManager::getProvider(GamesProvider::GAME_SYSTEM_MOBULE);
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

        $freeroundsId = '';
        try {
            if ($user->balance_mode === MUser::BALANCE_MODE_BONUS) {
                $bonus = KUserBonus::findFirstActiveBonus($user->getId(), $user->currency);
                if ($bonus !== null && $bonus->isFreeSpins() && $bonus->getSpinsAvailable() > 0 && $bonus->isGameAllowedByProvider($game->getProviderId(), $game->getId())) {
                    $freeroundsId = (string) $bonus->getId();
                }
            }
        } catch (\Throwable $e) {
            Hlog::logException($e);
        }

        $params = [
            'partner.alias' => $this->partnerAlias,
            'partner.session' => $sessionId,
            'game.provider' => $game->getGroupId(),
            'game.alias' => $game->getId(),
            'mobile' => Device::detect() == Device::PHONE ? 'true' : 'false',
            'lang' => App::getLocale(),
            'lobby_url' => action('EmulatorController@closeGameFrame'),
            'currency' => $user->getCurrencyName(),
        ];
        if ($freeroundsId !== '') {
            $params['freerounds_id'] = $freeroundsId;
        }

        return $this->baseUrl . '/games.start?' . http_build_query($params);
    }

    public function closeSession(MUser $user, string $session_id = ""): bool
    {
        if ($session_id !== '') {
            $curr_session = UserManager::getGameSession($user);
            if ($curr_session === null || $curr_session['cd'] === 200 || $curr_session['gs']['ID'] !== $session_id) return false;
        }
        try {
            $closedSess = UserManager::closeGameSession($user);
            if (!empty($closedSess) && ($closedSess['cd'] ?? 0) === 200) {
                ExitGoGameJob::dispatch(
                    $user->getId(),
                    GamesProvider::GAME_SYSTEM_MOBULE,
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

    private function post(string $method, array $params, bool $withSign): array
    {
        $params['partner.alias'] = $this->partnerAlias;
        if ($withSign) {
            $params['partner.sign'] = $this->sign($method, $params);
        }

        $url = $this->baseUrl . '/' . $method;
        return Http::asForm()->post($url, $params)->json();
    }

    private function sign(string $method, array $params): string
    {
        $keys = array_keys($params);
        $keys = array_filter($keys, static function (string $key): bool {
            return !str_starts_with($key, 'partner.') && !str_starts_with($key, 'meta.');
        });
        sort($keys, SORT_STRING);

        $pairs = [];
        foreach ($keys as $key) {
            $pairs[] = $key . '=' . $params[$key];
        }
        $joined = implode('&', $pairs);

        $stringToSign = $joined . '&' . $method . '&' . $this->partnerAlias . '&' . $this->secretKey;
        return md5($stringToSign);
    }
}
