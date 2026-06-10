<?php

namespace App\GamesProviders\B2BSlots;

use App\GamesProviders\GameData;
use App\GamesProviders\GameProviderApi;
use App\GamesProviders\GamesManager;
use App\GamesProviders\GamesProvider;
use App\Helpers\Hlog;
use App\Jobs\ExitGoGameJob;
use App\Models\Bonuses\KUserBonus;
use App\Models\UserGameStat;
use App\MUser;
use App\Services\Users\UserManager;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class B2BSlotsApi extends GameProviderApi
{
    private string $provider_id = '';
    private string $pass = '';
    private string $url = '';

    public function __construct()
    {
        $this->provider_id = config('keys.b2bslots.provider');
        $this->pass = config('keys.b2bslots.pass');
        $this->url = config('keys.b2bslots.url');
    }

    public function getAllGames(): array
    {
        $req_data = [
            'api' => 'ls-games-by-operator-id-get',
            'operator_id' => $this->provider_id,
        ];
        $data = [
            'cmd' => json_encode($req_data),
        ];
        $url = 'https://int.apichannel.cloud/frontendsrv/apihandler.api?cmd='.json_encode($req_data);

        return Http::acceptJson()->asJson()->get($url, $data)->json();
    }

    public function startSession(MUser $user, string $game_id, string $session_id = ""): string
    {
        $token = Str::random(32);
        try {
            $this->closeSession($user);
            $game_data = new GameData($user);
            $provider = GamesManager::getProvider(GamesProvider::GAME_SYSTEM_B2B_SLOTS);
            $game = $provider->getGame('', $game_id, true);
            $game_data->loadDataForGame($game->getProviderId(), $game->getId(), $user->getCurrency());
            $resp = UserManager::startGameSession($user, $game, $game_data->toaArray(), $session_id);
            if ($resp['cd'] !== 200) {
                throw new \Exception($resp['err'] ?? "can't start game session");
            }
            $resp = UserManager::setPPlayToken($token, $user->getId());
            if ($resp['cd'] !== 200) {
                throw new \Exception($resp['err'] ?? "set auth token error");
            }
        } catch (\Throwable $e) {
            Hlog::logException($e);
            return '';
        }

        $method = "gamesbycode/$game_id.gamecode";
        $data = [
            'operator_id' => $this->provider_id,
            'user_id' => $user->getId(),
            'auth_token' => $token,
            'currency' => $user->getCurrencyName(),
            'home_url' => route('landing'),
        ];
        Log::info("b2bslots start session data:", $data);
        Log::info("b2bslots start session url:". $this->url . $method . '?' . http_build_query($data));
        return $this->url . $method . '?' . http_build_query($data);
    }

    public function closeSession(MUser $user, string $session_id = ""): bool
    {
        try {
            if ($session_id !== '') {
                $curr_session = UserManager::getGameSession($user);
                if ($curr_session === null || $curr_session['cd'] === 200 || $curr_session['gs']['ID'] !== $session_id) return false;
            }

            $closed_sess = UserManager::closeGameSession($user);
            if (!empty($closed_sess) && $closed_sess['cd'] === 200) {

                $money = $user->getBalance();
                $curr_name = $user->getCurrencyName();

//                DB::table('payout_transactions')
//                    ->insert([
//                        'user_id' => $user->id,
//                        'hall_id' => $user->hall_id,
//                        'date' => date('Y-m-d H:i:s'),
//                        'sum' => ($money),
//                        'status_id' => 68,
//                        'currency' => $curr_name,
//                        'operation' => 'Выход с b2bslots'
//                    ]);

                Log::info("closing b2bslots game data:", $closed_sess);

                //if bonus game
                if ($closed_sess['gs']['Stats']['bonus_id'] > 0) {
                    ExitGoGameJob::dispatch($user->getId(),
                        GamesProvider::GAME_SYSTEM_B2B_SLOTS, $closed_sess['gs']['Stats']['game_id'],
                        $closed_sess['gs']['Stats']['currency'],
                        $closed_sess['gs']['Stats']['real_total_bet'],
                        $closed_sess['gs']['Stats']['real_total_win'],
                        $closed_sess['gs']['Stats']['login_balance'],
                        $closed_sess['gs']['Stats']['logout_balance'],
                        $closed_sess['gs']['Stats']['bonus_id'],
                    );

                } else {
                    ExitGoGameJob::dispatch($user->getId(),
                        GamesProvider::GAME_SYSTEM_B2B_SLOTS, $closed_sess['gs']['Stats']['game_id'],
                        $closed_sess['gs']['Stats']['currency'],
                        $closed_sess['gs']['Stats']['total_bet'],
                        $closed_sess['gs']['Stats']['total_win'],
                        $closed_sess['gs']['Stats']['login_balance'],
                        $closed_sess['gs']['Stats']['logout_balance'],
                        $closed_sess['gs']['Stats']['bonus_id'],
                    );
                }
                return true;
            }
        } catch (\Throwable $e) {
            Hlog::logException($e);
        }
        return false;
    }

    public function shouldStartNewSession($game_id, MUser $player): bool
    {
        /** @var UserGameStat $last_game */
        $last_game = UserGameStat::where('user_id', $player->id)
            ->where('system', GamesProvider::GAME_SYSTEM_B2B_SLOTS)->orderByDesc('login_date')
            ->skip(1)
            ->take(1)
            ->first();

        if ($last_game === null) return true;

        // always new session on new game
        if ($last_game->game_id !== $game_id) {
            return true;
        }

        //new session if the user plays on the main balance after a bonus
        if ($player->getBalanceMode() === MUser::BALANCE_MODE_MAIN && $last_game->bonus_id > 0) {
            return true;
        }

        $current_bonus = KUserBonus::findFirstActiveBonus($player->getId());
        //clear session for different bonuses
        if ($current_bonus !== null && $last_game->getBonusId() !== $current_bonus->getId()) {
            return true;
        }

        return false;
    }

    public function getGameInfo(int $id, string $currency = 'RUB')
    {
        $req_data = [
            'api' => 'plugin',
            'data' => [
                'name' => 'info',
                'action' => 'getGameSettings',
                'data' => [
                    'gameId' => $id,
                    'currency' => $currency,
                    'provider' => intval($this->provider_id),
                    'pass' => hash('sha1', $this->pass),
                ],
            ],
        ];

        $url = 'https://int4new.datachannel.cloud/.gambleApi';

        return Http::acceptJson()->asJson()->get($url, json_encode($req_data))->json();
    }

}
