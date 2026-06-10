<?php


namespace App\GamesProviders\PragmaticPlay;


use App\GamesProviders\GamesManager;
use App\GamesProviders\GamesProvider;
use App\Helpers\Device;
use App\Helpers\Hlog;
use App\Jobs\ExitGoGameJob;
use App\Models\Bonuses\KUserBonus;
use App\Models\UserGameStat;
use App\MUser;
use App\GamesProviders\GameData;
use App\Services\IpInfo\IpInfo;
use App\Services\Users\UserManager;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Str;

class PragmaticPlay
{
    protected $login = "";
    protected $key = "";
    protected $ProviderId = "PragmaticPlay";

    protected $game_server_domain = "krakencasino-dk2.pragmaticplay.net";
    protected $demo_server_domain = "demogamesfree.pragmaticplay.net";
    protected $game_url = "https://krakencasino-dk2.pragmaticplay.net/gs2c/playGame.do?key=<KEY_VALUE>&stylename={stylename}";
    protected $api_url = "https://api-dk2.pragmaticplay.net/IntegrationService/v3/http";


    public function __construct()
    {
        $this->login = config('keys.pplay.login');
        $this->key = config('keys.pplay.key');
    }

    public function getGames()
    {
        $items = Redis::get('pragplay_cached_games');
        if (!empty($items)) return json_decode($items, true);
        return self::cacheGames();

        $method = '/CasinoGameAPI/getCasinoGames/';
        $params = [
            'secureLogin' => $this->login,
        ];

        $resp = $this->query($method, $params, true);
        if ($resp === false) return [];
        return $resp['gameList'];
    }

    public function getUncachedGames()
    {
        $method = '/CasinoGameAPI/getCasinoGames/';
        $params = [
            'secureLogin' => $this->login,
        ];

        $resp = $this->query($method, $params, true);
        if ($resp === false) return [];
        return $resp['gameList'];
    }

    public static function cacheGames()
    {
        $api = new PragmaticPlay();
        $items = $api->getUncachedGames();
        //store games cache
        if (!empty($items)) Redis::set('pragplay_cached_games', json_encode($items));
        else Redis::del('pragplay_cached_games');

        return $items;
    }

    public function findGame($id)
    {
        $games = $this->getGames();
        foreach ($games as $game) {
            if ($game['gameID'] == $id) return $game;
        }
        return false;
    }

    public static function getGameImg($game_id)
    {
        $img = "/pragimg/" . $game_id . ".png";
        if (!file_exists(public_path() . $img)) {
            $img = '/images/noimage.jpg';
        }
        return $img;
    }

    public function initDemoGame($game_id)
    {
        $language = App::getLocale();
        $currency_symbol = "RUB";
        $URL = action('EmulatorController@closeGameFrame');
        return "https://{$this->demo_server_domain}/gs2c/openGame.do?gameSymbol={$game_id}&lang={$language}&cur={$currency_symbol}&lobbyUrl={$URL}&stylename={$this->login}";
    }

    public function initGame($game_id, MUser $player, string $sess_id = ""): string
    {
        $token = Str::random(32);
        try {
            $this->closeSession($player);
            $game_data = new GameData($player);
            $provider = GamesManager::getProvider(GamesProvider::GAME_SYSTEM_PRAGMATIC);
            $game = $provider->getGame('', $game_id, true);
            $game_data->loadDataForGame($game->getProviderId(), $game->getId(), $player->getCurrency())->save();
            $resp = UserManager::startGameSession($player, $game, $game_data->toaArray(), $sess_id);
            if ($resp['cd'] !== 200) {
                throw new \Exception($resp['err'] ?? "can't start game session");
            }
            $resp = UserManager::setPPlayToken($token, $player->getId());
            if ($resp['cd'] !== 200) {
                throw new \Exception($resp['err'] ?? "set auth token error");
            }
        } catch (\Throwable $e) {
            Hlog::logException($e);
            return '';
        }
        $ip_info = IpInfo::getInfo($player->last_ip);
        $country = $ip_info?->country_code ?? 'RU';
        $country = strtoupper($country);

        $method = "/CasinoGameAPI/game/url";
        $data = [
            'secureLogin' => $this->login,
            'token' => $token,
            'symbol' => $game_id,
            'language' => App::getLocale(),
            'technology' => 'H5',
            'country' => $country,
            'platform' => Device::detect() == Device::PHONE ? 'mobile' : 'web',
            'cashierUrl' => action('EmulatorController@closeGameFrame'),
            'lobbyUrl' => action('EmulatorController@closeGameFrame'),
            'rcCloseUrl' => action('EmulatorController@closeGameFrame'),
        ];

        $resp = $this->query(method: $method, params: $data, post: true, verbose: false);
        return $resp['gameURL'] ?? '';
    }

    public function closeGame(string $player_id, string $game_id)
    {
        try {
            Log::channel('pragmatik')->info("closing last game $game_id for player $player_id");
            $method = '/CasinoGameAPI/closeSession/';
            $params = [
                'secureLogin' => $this->login,
                'externalPlayerId' => $player_id,
                'gameId' => $game_id,
                'clearHistory' => 1,
            ];

            $resp = $this->query($method, $params, true, true);
            Log::channel('pragmatik')->info("closing last game $game_id for player resp:" . var_export($resp, true));
            return $resp;
        } catch (\Exception $e) {
            Hlog::logException($e, 'pragmatik');
        }
        return null;
    }

    public function closeLastGame($game_id, MUser $player): bool
    {
        /** @var UserGameStat $last_game */
        $last_game = UserGameStat::where('user_id', $player->id)->where('system', GamesProvider::GAME_SYSTEM_PRAGMATIC)->orderByDesc('login_date')->take(1)->first();
        if ($last_game === null) return true;

        $currency_name = $last_game->getCurrency();

        if (!$last_game->getBonusId() > 0) {
            // if the last game wasn't a bonus game, don't close it
            return false;
        }
        $player_id = 'bonus' . $player->id . $currency_name;

        //new session if the user plays on the main balance after a bonus
        if ($player->getBalanceMode() === MUser::BALANCE_MODE_MAIN) {
            $this->closeGame($player_id, $last_game->game_id);
            return true;
        }

        //always start a new session when user has completed bonus
        if (!KUserBonus::findFirstActiveBonus($player->getId())) {
            $this->closeGame($player_id, $last_game->game_id);
            return true;
        }

        $current_bonus = KUserBonus::findFirstActiveBonus($player->getId());
        //continue session for same bonus
        if ($current_bonus && $last_game->getBonusId() == $current_bonus->getId()) {
            //clear session when start new game
            if ($last_game && $last_game->game_id != $game_id) {
                $this->closeGame($player_id, $last_game->game_id);
                return true;
            }
            return false;
        }

        $this->closeGame($player_id, $last_game->game_id);
        return true;
    }

    public function transferMoney(MUser $user, string $gameId, string $sess_id)
    {
    }

    public function getPlayersFRB($player_id)
    {
        if (!str_starts_with($player_id, "bonus")) {
            $player_id = 'bonus' . $player_id;
        }
        $method = '/FreeRoundsBonusAPI/getPlayersFRB/';
        $params = [
            'secureLogin' => $this->login,
            'playerId' => $player_id,
        ];

        $resp = $this->query($method, $params, true);
        return $resp;
    }

    public function createFRB(string $player_id, $currency_name, $games_list, $rounds, $bonus_id, $expiration_date)
    {
        if (!str_starts_with($player_id, "bonus")) {
            $player_id = 'bonus' . $player_id;
        }
        $method = '/FreeRoundsBonusAPI/createFRB';
        $params = [
            'secureLogin' => $this->login,
            'playerId' => $player_id,
            'currency' => $currency_name,
            'gameIDList' => $games_list,
            'rounds' => $rounds,
            'bonusCode' => $bonus_id,
            'expirationDate' => $expiration_date,
        ];

        $resp = $this->query($method, $params, false);
        return $resp;
    }

    public function cancelFRB($bonus_id)
    {
        $method = '/FreeRoundsBonusAPI/cancelFRB/';
        $params = [
            'secureLogin' => $this->login,
            'bonusCode' => $bonus_id,
        ];

        $resp = $this->query($method, $params, false);
        return $resp;
    }

    public function createPlayerVFRB(string $player_id, $currency_name, $games_list, $rounds, $bonus_id, $expiration_date, float $max_bet = 0)
    {
//        if (!str_starts_with($player_id, "bonus")) {
//            $player_id = 'bonus' . $player_id;
//        }
        $method = '/FreeRoundsBonusAPI/v2/bonus/player/create/';
        $url_params = [
            'bonusCode' => $bonus_id,
            'expirationDate' => $expiration_date,
            'rounds' => $rounds,
            'secureLogin' => $this->login,
            'startDate' => now()->timestamp,
            'currency' => $currency_name,
            'playerId' => $player_id,
        ];

        $params = [];

        foreach (explode(',', $games_list) as $game_id) {
            $bets = $this->getBetScales($game_id, $currency_name);
            if (empty($bets) || empty($bets['gameList'][0]['betScaleList'][0]['totalBetScales'])) {
                Log::channel('pragmatik')->error('cant get bet list for game '.$game_id);
                return false;
            }
            $bets = $bets['gameList'][0]['betScaleList'][0]['totalBetScales'];
            Log::channel('pragmatik')->info("bets for game $game_id ".var_export($bets, true));
            $curr_bet = $bets[0];
            foreach ($bets as $bet_val) {
                if ($bet_val > $curr_bet && $bet_val < $max_bet) $curr_bet = $bet_val;
            }
            $params["gameList"][] = [
                'gameId' => $game_id,
                'betValues' => [
                    [
                        'totalBet' => $curr_bet,
                        'currency' => $currency_name,
                    ]
                ],
            ];
        }
        Log::channel('pragmatik')->info('game params', $params);

        $resp = $this->queryJson($method, $params, $url_params);
        return $resp;
    }

    public function cancelVFRB($bonus_id)
    {
        $method = '/FreeRoundsBonusAPI/v2/bonus/cancel/';
        $params = [
            'secureLogin' => $this->login,
            'bonusCode' => $bonus_id,
        ];

        $resp = $this->query($method, $params, true);
        return $resp;
    }

    public function getBetScales(string $game_id, string $currency)
    {
        $method = '/CasinoGameAPI/getBetScales';
        $params = [
            'secureLogin' => $this->login,
            'gameIDs' => $game_id,
            'currencies' => $currency,
        ];
        $resp = $this->query($method, $params, true, false);
        return $resp;
    }
    /*
     * [
    "error" => "0",
    "description" => "OK",
    "gameList" => [
      [
        "gameID" => "vs20olympx",
        "betScaleList" => [
          [
            "currency" => "RUB",
            "betPerLineScales" => [
              0.8,
              1.6,
              2.4,
              3.2,
              4.0,
              4.8,
              8.0,
              16.0,
              20.0,
            ],
            "totalBetScales" => [
              16.0,
              32.0,
              48.0,
              64.0,
              80.0,
              96.0,
              160.0,
              320.0,
              400.0,
            ],
          ],
        ],
      ],
    ],
  ]
     */


    public function checkHash($data)
    {
        $hash = $data['hash'];
        if ($hash !== $this->calcHash($data)) return false;
        return true;
    }

    /**
     * calc hash
     * should not html encode
     * @param array $data
     * @return string
     */
    public function calcHash(array $data = []): string
    {
        if (isset($data['hash'])) {
            unset($data['hash']);
        }
        ksort($data, SORT_STRING);
        $dstr = [];
        foreach ($data as $k => $v) {
            $dstr[] = '' . $k . '=' . $v;
        }
        return md5(implode('&', $dstr) . $this->key);
    }


    public function query($method, $params = [], bool $post = false, bool $verbose = false, array $url_params = [])
    {
        $url = $this->api_url . $method;

        if ($post && !empty($url_params)) {
            $url_params['hash'] = $this->calcHash($url_params);
            $url .= '?' . http_build_query($url_params);
        } else {
            $params['hash'] = $this->calcHash($params);
        }

        if (!$post && !empty($params)) {
            $url .= '?' . http_build_query($params);
        }
        Log::channel('pragmatik')->info('url: ' . var_export($url, true));
        Log::channel('pragmatik')->info('params: ' . var_export($params, true));
        Log::channel('pragmatik')->info('url_params: ' . var_export($url_params, true));

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        if ($post) {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));
        }
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        if ($verbose) {
            curl_setopt($ch, CURLOPT_VERBOSE, true);
            $verb = fopen('php://temp', 'w+');
            curl_setopt($ch, CURLOPT_STDERR, $verb);
        }

        $response = json_decode(curl_exec($ch), true);

        if ($verbose) {
            rewind($verb);
            $verboseLog = stream_get_contents($verb);

            Log::channel('pragmatik')->info('curl verbose');
            Log::channel('pragmatik')->info(htmlspecialchars($verboseLog));
        }
        curl_close($ch);

        if (empty($response)) {
            Log::channel('pragmatik')->error('empty response');
            return false;
        }

        if ($response['error'] != 0) {
            if ($response['error'] == 10) return false;
            Log::channel('pragmatik')->error($response['description'] ?? '');
            Log::channel('pragmatik')->error(var_export($response, true));
            return false;
        }

        return $response;
    }

    public function queryJson($method, $params = [], array $url_params = [])
    {
        $url = $this->api_url . $method;

        $url_params['hash'] = $this->calcHash($url_params);
        $url .= '?' . http_build_query($url_params);

        Log::channel('pragmatik')->info('url: ' . var_export($url, true));
        Log::channel('pragmatik')->info('params: ' . var_export($params, true));
        Log::channel('pragmatik')->info('params json: ' . json_encode($params));
        Log::channel('pragmatik')->info('url_params: ' . var_export($url_params, true));

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
            'Accept: application/json',
        ));
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($params));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_VERBOSE, true);
        $verb = fopen('php://temp', 'w+');
        curl_setopt($ch, CURLOPT_STDERR, $verb);

        $response = json_decode(curl_exec($ch), true);
        rewind($verb);
        $verboseLog = stream_get_contents($verb);

        Log::channel('pragmatik')->info('curl verbose');
        Log::channel('pragmatik')->info(htmlspecialchars($verboseLog));

        curl_close($ch);

        if (empty($response)) {
            Log::channel('pragmatik')->error('empty response');
            return false;
        }

        if ($response['error'] != 0) {
            if ($response['error'] == 10) return false;
            Log::channel('pragmatik')->error($response['description'] ?? '');
            Log::channel('pragmatik')->error(var_export($response, true));
            return false;
        }

        return $response;
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
//                        'operation' => 'Выход с PPlay'
//                    ]);


                Log::info("closing pplay game data:", $closed_sess);

                //if bonus game
                if ($closed_sess['gs']['Stats']['bonus_id'] > 0) {
                    ExitGoGameJob::dispatch($user->getId(),
                        GamesProvider::GAME_SYSTEM_PRAGMATIC, $closed_sess['gs']['Stats']['game_id'],
                        $closed_sess['gs']['Stats']['currency'],
                        $closed_sess['gs']['Stats']['real_total_bet'],
                        $closed_sess['gs']['Stats']['real_total_win'],
                        $closed_sess['gs']['Stats']['login_balance'],
                        $closed_sess['gs']['Stats']['logout_balance'],
                        $closed_sess['gs']['Stats']['bonus_id'],
                    );

                } else {
                    ExitGoGameJob::dispatch($user->getId(),
                        GamesProvider::GAME_SYSTEM_PRAGMATIC, $closed_sess['gs']['Stats']['game_id'],
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

}
