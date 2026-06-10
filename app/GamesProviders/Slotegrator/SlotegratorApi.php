<?php


namespace App\GamesProviders\Slotegrator;


use App\GamesProviders\GameData;
use App\GamesProviders\GamesManager;
use App\GamesProviders\GamesProvider;
use App\Helpers\Currency;
use App\Helpers\Hlog;
use App\Models\Bonuses\KUserBonus;
use App\Models\UserGameStat;
use App\Services\Users\UserManager;
use App\Jobs\ExitGoGameJob;
use App\Models\Bonuses\UserBonus;
use App\MUser;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Str;

class SlotegratorApi
{
    protected string $merchant_id = "";
    protected string $key = "";
    protected string $api_url = "https://556v1xba.com/api/index.php/v1";

    public $use_proxy = false;
    public $proxy = "vcasinos.xyz";
    public static array $stage_users = [];

    protected bool $stage = false;

    /**
     * @param bool $stage stage mode api
     */
    public function __construct(bool $stage = false)
    {
        $this->stage = $stage;
        if ($stage) {
            $this->merchant_id = "984565165ef77294f6c4beb948cf4fdb";
            $this->key = "a07320e02bd8d24762c1375333cefe705e2c2369";
            $this->api_url = "https://staging.slotegrator.com/api/index.php/v1";
        } else {
            $this->merchant_id = config('keys.slotegrator.merchant_id');
            $this->key = config('keys.slotegrator.key');
        }
    }

    /**
     * get array of all available games
     * @param bool $use_cache get games from cache
     * @return array
     */
    public function gameList(bool $use_cache = true): array
    {
        if ($use_cache) {
            $items = Redis::get('sleg_cached_games');
            if (!empty($items)) return json_decode($items, true);
            return self::cacheGames();
        }

        $method = '/games';
        $params = [
//            'perPage' => 9999,
            'page' => 1,
        ];
        $resp = $this->query($method, $params);

        if (empty($resp['items'])) return [];

        $items = $resp['items'];
        $page_count = $resp['_meta']['pageCount'] ?? 1;
        $per_page = $resp['_meta']['perPage'] ?? 999;
        if ($page_count > 1) {
            for ($f = 2; $f <= $page_count; $f++) {
                $params = [
//                    'perPage' => $per_page,
                    'page' => $f,
                ];
                $resp = $this->query($method, $params);

                if (!empty($resp['items'])) $items = array_merge($items, $resp['items']);
            }
        }

        if ($this->use_proxy) {
            foreach ($items as $game) {
                $game['image'] = str_replace('gis.slotegrator.com', $this->proxy, $game['image']);
            }
        }

        return $items;
    }

    /**
     * get all games
     * @return array
     */
    public static function cacheGames(): array
    {
        $api = new SlotegratorApi();

        $method = '/games';
        $params = [
//            'perPage' => 9999,
            'page' => 1,
        ];
        $resp = $api->query($method, $params);

        if (empty($resp['items'])) return [];

        $items = $resp['items'];
        $page_count = $resp['_meta']['pageCount'] ?? 1;
        $per_page = $resp['_meta']['perPage'] ?? 999;
        if ($page_count > 1) {
            for ($f = 2; $f <= $page_count; $f++) {
                $retry_time = 0;
                $params = [
//                    'perPage' => $per_page,
                    'page' => $f,
                ];
                $resp = $api->query($method, $params);
                sleep(1);

                if (!empty($resp['items']))  {
                    $items = array_merge($items, $resp['items']);
                } else {
                    //page is empty - retry
                    $retry_time++;
                    if ($retry_time > 10) {
                        Log::channel('slotegrator')->info('retry limit reached - skipping page ' . $f);
                    } else {
                        $f--;
                        Log::channel('slotegrator')->info('Sleg games page is empty, retrying... page:' . $f . ' retry_time:' . $retry_time);;
                    }
                }
            }
        }
        if ($api->use_proxy) {
            $replaced = [];
            foreach ($items as $game) {
                $game['image'] = str_replace('gis.slotegrator.com', $api->proxy, $game['image']);
                $replaced[] = $game;
            }
            $items = $replaced;
        }

        //store games
        if (!empty($items)) Redis::set('sleg_cached_games', json_encode($items));
        else Redis::del('sleg_cached_games');

        return $items;
    }

    /**
     * get all game groups (providers)
     * @return array
     */
    public function getProviders(): array
    {
        $providers = [];
        foreach ($this->gameList() as $game) {
            $providers[$game['provider']][] = $game;
        }

        return $providers;
    }

    /**
     * get game data by id
     * @param $game_id
     * @return array|null
     */
    public function findGame($game_id): ?array
    {
        foreach ($this->gameList() as $game) {
            if ($game['uuid'] == $game_id) return $game;
        }
        return null;
    }

    /**
     * check if new session should be created for user
     * @param $game_id
     * @param MUser $player
     * @return bool
     * @throws \Exception
     */
    public function shouldStartNewSession($game_id, MUser $player): bool
    {
        /** @var UserGameStat $last_game */
        $last_game = UserGameStat::where('user_id', $player->id)
            ->where('system', GamesProvider::GAME_SYSTEM_SLOTEGRATOR)->orderByDesc('login_date')
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

    /**
     * start game
     * @param $game_id
     * @param MUser $player
     * @param string $url
     * @return array|null
     * @throws \Exception
     */
    public function initGame($game_id, MUser $player, $url = 'https://krakencassino.com'): ?array
    {
        Log::channel('slotegrator')->info('Creating slotegrator game, user_id:' . $player->id . ' game_id:' . $game_id);

        $currency_name = $player->getWallet()->getGameCurrencyName();

        $player_id = '' . $player->getId() . $player->getCurrencyName();

        if ($player->hasActiveVegasBonusGame($game_id) && $player->getBalanceMode() != MUser::BALANCE_MODE_MAIN) $player_id = 'bonus' . $player_id;

        if (empty($player->slotMachineToken) || $this->shouldStartNewSession($game_id, $player)) {
            $token = Str::random(32);
            $player->slotMachineToken = $token;
            $player->save();
        }

        $method = '/games/init';
        $params = [
            'game_uuid' => $game_id,
            'player_id' => $player_id,
            'player_name' => $player->login,
            'currency' => $currency_name,
            'session_id' => $player->slotMachineToken,
            'return_url' => action('EmulatorController@closeGameFrame'),
            'language' => App::getLocale(),
        ];

        $resp = $this->query($method, $params, true);

        Log::channel('slotegrator')->info('Session ID: ' . $player->slotMachineToken . ' Creating slotegrator game response:' . var_export($resp, true));

        if ($this->use_proxy) {
            $resp['url'] = str_replace('gis.slotegrator.com', $this->proxy, $resp['url']);
        }
        return $resp;
    }

    /**
     * start demo game
     * @param $game_id
     * @param string $url
     * @return array|null
     */
    public function initDemo($game_id): ?array
    {
        $method = '/games/init-demo';
        $params = [
            'game_uuid' => $game_id,
            'return_url' => action('EmulatorController@closeGameFrame'),
            'language' => App::getLocale(),
        ];

        $resp = $this->query($method, $params, true);
        if ($this->use_proxy) {
            $resp['url'] = str_replace('gis.slotegrator.com', $this->proxy, $resp['url']);
        }
        return $resp;
    }

    /**
     * get merchant limits
     * @return array|null
     */
    public function limits(): ?array
    {
        $method = '/limits';
        $params = [
        ];

        return $this->query($method, $params);
    }

    /**
     * sign a request
     * @param array $requestParams
     * @param $headers
     * @return string
     */
    protected function sign(array $requestParams, $headers): string
    {
        $mergedParams = array_merge($requestParams, $headers);
        ksort($mergedParams);
        $hashString = http_build_query($mergedParams);
        $sign = hash_hmac('sha1', $hashString, $this->key);

        return $sign;
    }

    /**
     * sends query to slotegrator api
     * @param $method
     * @param $params
     * @param bool $post
     * @return array|null
     */
    public function query($method, $params, bool $post = false): ?array
    {
        $url = $this->api_url . $method;
        if (!$post && !empty($params)) {
            $url .= '?' . http_build_query($params);
        }
        Log::channel('slotegrator')->info($url);
        Log::channel('slotegrator')->info('params:' . var_export($params, true));

        $headers = [
            'X-Merchant-Id' => $this->merchant_id,
            'X-Timestamp' => (string)time(),
            'X-Nonce' => md5(uniqid(mt_rand(), true)),
        ];

        $XSign = $this->sign($params, $headers);

        $headers['X-Sign'] = $XSign;

        $curl_headers = [];
        foreach ($headers as $k => $v) {
            $curl_headers[] = '' . $k . ': ' . $v;
        }

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        if ($post) {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));
        }
        curl_setopt($ch, CURLOPT_HTTPHEADER, $curl_headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
//        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $response = json_decode(curl_exec($ch), true);
        curl_close($ch);
        Log::channel('slotegrator')->info(var_export($response, true));
        return $response;
    }

    /**
     * transfer money to game wallet
     * @param MUser $user
     * @param null $sess_id
     */
    public function transferMoney(MUser $user, string $gameId, string $sess_id)
    {

        self::closeSession($user);

        $provider = GamesManager::getProvider("sleg");
        $game = $provider->getGame('', $gameId, true);
        $game_data = new GameData($user);
        $game_data->loadDataForGame($game->getProviderId(), $game->getId(), $user->currency)->save();
        UserManager::startGameSession($user, $game, $game_data->toaArray(), $sess_id);
    }

    /**
     * close game last session
     * @param MUser $user
     */
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
//                        'operation' => 'Выход с Sleg'
//                    ]);

                Log::info("closing sleg game data:", $closed_sess);

                //if bonus game
                if ($closed_sess['gs']['Stats']['bonus_id'] > 0) {
                    ExitGoGameJob::dispatch($user->getId(),
                        GamesProvider::GAME_SYSTEM_SLOTEGRATOR, $closed_sess['gs']['Stats']['game_id'],
                        $closed_sess['gs']['Stats']['currency'],
                        $closed_sess['gs']['Stats']['real_total_bet'],
                        $closed_sess['gs']['Stats']['real_total_win'],
                        $closed_sess['gs']['Stats']['login_balance'],
                        $closed_sess['gs']['Stats']['logout_balance'],
                        $closed_sess['gs']['Stats']['bonus_id'],
                    );

                } else {
                    ExitGoGameJob::dispatch($user->getId(),
                        GamesProvider::GAME_SYSTEM_SLOTEGRATOR, $closed_sess['gs']['Stats']['game_id'],
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

    /**
     * @param $game_id
     * @return string
     * @deprecated
     * get game img url
     * @deprecated
     */
    public static function getGameImg($game_id): string
    {
        $api = new SlotegratorApi();
        if ($api->use_proxy) {
            return "https://{$api->proxy}/api/index.php/image/get?hash=" . $game_id . ".png";
        }
        return "https://gis.slotegrator.com/api/index.php/image/get?hash=" . $game_id . ".png";
    }

    /**
     * api test
     * @return array|null
     */
    public function selfValidate(): ?array
    {
        $method = '/self-validate';
        $params = [

        ];

        return $this->query($method, $params, true);
    }

    /**
     * freepins limit for merchant
     * @return array|null
     */
    public function getFreespinsLimits(): ?array
    {
        $method = '/limits/freespin';
        $params = [
        ];

        return $this->query($method, $params);
    }

    /**
     * bet amounts and denominations available for a game
     * should be used in freespins
     * @param string $game_id
     * @param string $currency
     * @return array|null
     */
    public function freeSpinsBets(string $game_id, string $currency): ?array
    {
        $method = '/freespins/bets';
        $params = [
            'game_uuid' => $game_id,
            'currency' => $currency,
        ];

        return $this->query($method, $params);
    }

    /**
     * create freespins
     * @param string $id freespins id
     * @param string $game_id
     * @param string $player_id slotegrator player id
     * @param int $quantity freespins quantity
     * @param string $currency 3 letter currency name
     * @param int|null $active_to active to timestamp (3 days default)
     * @param int $max_bet
     * @return array|null
     * @throws \Exception
     */
    public function freeSpinsSet(string $id, string $game_id, string $player_id, int $quantity, string $currency, ?int $active_to = null, float $max_bet = 0): ?array
    {
        if ($currency === Currency::USDT) $currency = Currency::USD;
        Log::channel('slotegrator')->info("max_bet value $max_bet");
        // get bet data
        $bets = $this->freeSpinsBets($game_id, $currency);
        if (!empty($bets['total_bets']) && count($bets['total_bets']) > 0) {
            Log::channel('slotegrator')->info("using total bets");
            $curr_bet = $bets['total_bets'][0]['bet_id'];
            $curr_amount = $bets['total_bets'][0]['amount'];
            foreach ($bets['total_bets'] as $total_bet) {
                if ($total_bet['amount'] > $curr_amount && $total_bet['amount'] <= $max_bet ) {
                    $curr_bet = $total_bet['bet_id'];
                    $curr_amount = $total_bet['amount'];
                }
            }
            Log::channel('slotegrator')->info("chosen bet_id = $curr_bet ($curr_amount)");

            $method = '/freespins/set';
            $params = [
                'player_id' => $player_id,
                'player_name' => 'player',
                'currency' => $currency,
                'quantity' => $quantity,
                'valid_from' => now()->timestamp,
                'valid_until' => $active_to ?? now()->addDays(3)->timestamp,
                'freespin_id' => $id,
                'game_uuid' => $game_id,
                'total_bet_id' => $curr_bet,
            ];

        } else {
            if (empty($bets['bets']) || empty($bets["denominations"])) {
                throw new \Exception("No bets or denominations in response");
            }

            Log::channel('slotegrator')->info("max_bet value $max_bet");
            // find suitable bet and denomination
            $bet_id = null;
            $denom = '';
            $curr_bet = 0;
            foreach ($bets['bets'] as $bet) {
                foreach ($bets['denominations'] as $denomination) {
                    $bet_val = $bet['bet_per_line'] * $bet['lines'] * $denomination;
                    Log::channel('slotegrator')->info("{$bet['bet_id']} {$bet['bet_per_line']} * {$bet['lines']} * $denomination = $bet_val max $max_bet current = $curr_bet ($bet_id)");
                    // set first value
                    if ($curr_bet === 0) {
                        $curr_bet = $bet_val;
                        $bet_id = $bet['bet_id'];
                        $denom = $denomination;
                    }
                    // if bet is closer to $max_bet
                    if ($bet_val > $curr_bet && $bet_val <= $max_bet) {
                        $curr_bet = $bet_val;
                        $bet_id = $bet['bet_id'];
                        $denom = $denomination;
                    }
                }
            }

            Log::channel('slotegrator')->info("chosen bet_id = $bet_id ($curr_bet) denomination $denom");

            if ($bet_id === null) throw new \Exception("can't find appropriate bet/denomination max_bet: $max_bet");

            $method = '/freespins/set';
            $params = [
                'player_id' => $player_id,
                'player_name' => 'player',
                'currency' => $currency,
                'quantity' => $quantity,
                'valid_from' => now()->timestamp,
                'valid_until' => $active_to ?? now()->addDays(3)->timestamp,
                'freespin_id' => $id,
                'game_uuid' => $game_id,
                'bet_id' => $bet_id,
                'denomination' => $denom,
            ];
        }

        return $this->query($method, $params, true);

    }

    /**
     * get freespins info
     * @param string $id
     * @return array|null
     */
    public function freeSpinsGet(string $id): ?array
    {
        $method = '/freespins/get';
        $params = [
            'freespin_id' => $id,
        ];

        return $this->query($method, $params);
    }

    /**
     * cancel freespins
     * @param string $id
     * @return array|null
     */
    public function freeSpinsCancel(string $id): ?array
    {
        $method = '/freespins/cancel';
        $params = [
            'freespin_id' => $id,
        ];

        return $this->query($method, $params, true);
    }
}
