<?php

namespace App\GamesProviders\Qt;

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
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Str;

class QtGamesProviderApi extends GameProviderApi
{
    private ?string $_token = null;
    private string $username = "";
    private string $password = "";

    public function __construct()
    {
        $this->username = config('keys.qt.username');
        $this->password = config('keys.qt.password');
    }


    public function getAllGames(): array
    {
        $items = Redis::get('qt_cached_games');
        if (!empty($items)) return json_decode($items, true);
        return self::cacheGames();
    }

    public static function cacheGames(): array
    {
        try {
            $token = @file_get_contents('https://api.qtplatform.com/v1/auth/token?grant_type=password&response_type=token&username='.config('keys.qt.username').'&password='.config('keys.qt.password'),
                false,
                stream_context_create(['ssl' => ['verify_peer' => false, 'verify_peer_name' => false]])
            );
            if ($token == null) return [];
            $token = json_decode($token);
            $token = $token->access_token;

            // getting QT game list
            $curl = curl_init();
            curl_setopt($curl, CURLOPT_URL, "https://api.qtplatform.com/v1/games");
            curl_setopt($curl, CURLOPT_HTTPHEADER, array(
                'Accept: application/json',
                'Authorization: Bearer ' . $token
            ));
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
            $html = curl_exec($curl);

            if (curl_errno($curl)) {
                $game_list = [];
            } else {
                $game_list = json_decode($html, true);

            }
            curl_close($curl);
            $games = $game_list['items'] ?? [];

            //store games
            if (!empty($games)) Redis::set('qt_cached_games', json_encode($games));
            else Redis::del('qt_cached_games');

            return $games;

        } catch (\Exception $e) {
            Log::channel('qt')->error($e->getMessage());
            return [];
        }
    }

    /**
     * @param MUser $user
     * @param string $session_id
     * @return void
     * @deprecated
     */
    public function transferMoney(MUser $user, string $session_id = ''): void
    {
//        try {
//            DB::beginTransaction();
//            $q_wallet = QtWallet::getForUser($user);
//            $wallet = $user->getWallet();
//            $money = $wallet->getBalance();
//            $wallet->remMoney($money);
//            $q_wallet->addMoney($money);
//            $q_wallet->token = $session_id;
//            $q_wallet->save();
//        } catch (\Exception $e) {
//            Log::channel('qt')->error($e->getMessage());
//            DB::rollBack();
//        }
//        DB::commit();
    }


    /**
     * @param MUser $user
     * @param float $amount
     * @return bool
     * @deprecated
     */
    public function transferMoneyInGame(MUser $user, float $amount): bool
    {
//        $token = $this->getToken();
//        if ($token == null) return false;
//
//        $token = json_decode($token);
//        $token = $token->access_token;
//
//        $curl = curl_init();
//        curl_setopt($curl, CURLOPT_URL, "https://api.qtplatform.com/v1/fund-transfers");
//        curl_setopt($curl, CURLOPT_POST, true);
//        $post = [
//            'playerId' => urlencode($user->id . $user->getCurrencyName()),
//            'currency' => $user->getCurrencyName(),
//            'type' => 'DEBIT',
//            'referenceId' => microtime(true),
//            'amount' => $amount
//        ];
//        curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($post));
//        curl_setopt($curl, CURLOPT_HTTPHEADER, array(
//            'Content-Type: application/json',
//            'Accept: application/json',
//            'Authorization: Bearer ' . $token
//        ));
//        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
//        $html = curl_exec($curl);
//        if (curl_errno($curl)) {
//            return false;
//        } else {
//            $response = json_decode($html, true);
//            if (!empty($response['id'])) {
//                return self::completeTransfer($response['id'], $token);
//            }
//        }
//        curl_close($curl);
        return false;
    }

    public function transferMoneyBack(MUser $user, string $session_id = ""): bool
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
//                        'operation' => 'Выход с QT'
//                    ]);

                Log::info("closing qt game data:", $closed_sess);

                //if bonus game
                if ($closed_sess['gs']['Stats']['bonus_id'] > 0) {
                    ExitGoGameJob::dispatch($user->getId(),
                        GamesProvider::GAME_SYSTEM_QT, $closed_sess['gs']['Stats']['game_id'],
                        $closed_sess['gs']['Stats']['currency'],
                        $closed_sess['gs']['Stats']['real_total_bet'],
                        $closed_sess['gs']['Stats']['real_total_win'],
                        $closed_sess['gs']['Stats']['login_balance'],
                        $closed_sess['gs']['Stats']['logout_balance'],
                        $closed_sess['gs']['Stats']['bonus_id'],
                    );

                } else {
                    ExitGoGameJob::dispatch($user->getId(),
                        GamesProvider::GAME_SYSTEM_QT, $closed_sess['gs']['Stats']['game_id'],
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
     * @param MUser $user
     * @param string|null $currency
     * @return float
     * @deprecated
     */
    public function getBalance(MUser $user, string &$currency = null): float
    {
        $token = @file_get_contents('https://api.qtplatform.com/v1/auth/token?grant_type=password&response_type=token&username='.$this->username.'&password='.$this->password,
            false,
            stream_context_create(['ssl' => ['verify_peer' => false, 'verify_peer_name' => false]])
        );
        $token = json_decode($token);
        $token = $token->access_token;

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, "https://api.qtplatform.com/v1/wallet/ext/" . $user->id . $user->getCurrencyName());

        curl_setopt($curl, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
            'Accept: application/json',
            'Authorization: Bearer ' . $token
        ));
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        $jsonString = curl_exec($curl);
        if (curl_errno($curl)) {
            Log::channel('qt')->error('Error on fetching QT balance for user ' . $user->id . ' : ' . $jsonString);
            return 0;
        } else {
            $response = json_decode($jsonString, true);
        }
        curl_close($curl);
        if ($currency !== null && isset($response['currency'])) {
            $currency = $response['currency'];
        }
        if (!isset($response['amount']) || empty($response['amount'])) {
            return 0;
        }
        return floatval($response['amount']);
    }


    /**
     * @param MUser $user
     * @return bool
     */
    public function closeSession(MUser $user, string $session_id = ""): bool
    {
        return $this->transferMoneyBack($user, $session_id);
    }

    /**
     * @param string $id
     * @param string $token
     * @return bool
     * @deprecated
     */
    private static function completeTransfer(string $id, string $token): bool
    {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, "https://api.qtplatform.com/v1/fund-transfers/$id/status");
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "PUT");
        $post = ['status' => "COMPLETED"];
        curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($post));
        curl_setopt($curl, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
            'Accept: application/json',
            'Authorization: Bearer ' . $token
        ));
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        $html = curl_exec($curl);
        if (curl_errno($curl)) {
            return false;
        } else {
            $data = json_decode($html, true);
        }
        curl_close($curl);
        return true;
    }


    private function getToken(): string
    {
        if ($this->_token === null) {
            $resp = @file_get_contents('https://api.qtplatform.com/v1/auth/token?grant_type=password&response_type=token&username='.$this->username.'&password='.$this->password,
                false,
                stream_context_create(['ssl' => ['verify_peer' => false, 'verify_peer_name' => false]])
            );
            $data = json_decode($resp ?? '{}', true);
            if (empty($data['access_token'])) {
                Log::channel('qt')->error('Get roken error');
                return '';
            }
            $this->_token = $data['access_token'];
        }
        Log::channel('qt')->info('token:' . $this->_token);
        return $this->_token;
    }

    public function shouldStartNewSession(MUser $user, UserGameStat $last_game, string $game_id): bool
    {
        $new_session = false;

        //new session is user plays on main balance after bonus or tournament balance
        if ($last_game->getBonusId() > 0 && $user->balance_mode == MUser::BALANCE_MODE_MAIN) {
            $new_session = true;
        } //always start a new session when user has completed bonus
        elseif ($last_game->getBonusId() > 0 && !KUserBonus::findFirstActiveBonus(Auth::id())) {
            $new_session = true;
        } elseif ($last_game->getBonusId() > 0) {
            $curr_bonus_id = KUserBonus::findFirstActiveBonus(Auth::id())->getId();
            //continue session for same bonus
            if ($last_game->getBonusId() === $curr_bonus_id) {
                //clear session when start new game
                if ($last_game->game_id != $game_id) {
                    $new_session = true;
                } else {
                    $new_session = false;
                }
            } else {
                $new_session = true;
            }
        }
        return $new_session;
    }

    public function startSession(MUser $user, string $game_id, string $session_id = ""): string
    {
        return $this->startGame($game_id, $user);
    }

    public function startGame($game_id, MUser $user): string
    {
        try {
            $this->transferMoneyBack($user);
            /** @var UserGameStat $last_game */
            $last_game = UserGameStat::where('user_id', $user->getId())->where('system', GamesProvider::GAME_SYSTEM_QT)->orderByDesc('login_date')->take(1)->first();

//            DB::beginTransaction();
            Log::channel('qt')->info('Creating qt game, user_id:' . $user->id . ' game_id:' . $game_id);

            $currency_name = $user->getCurrencyName();
//            $wallet = QtWallet::getForUser($user, UserWallet::getCurrencyCode($currency_name));

            $player_id = '' . Auth::id() . $currency_name;

            if ($user->hasActiveVegasBonusGame($game_id) && $user->balance_mode != MUser::BALANCE_MODE_MAIN) $player_id = 'bonus' . $player_id;

//            $trg = $user->getActiveTournamentGame();
//
//            if ($trg && $user->balance_mode === MUser::BALANCE_MODE_TOURNAMENT) {
//                $currency_name = 'USD';
//                $player_id = 'bonus' . Auth::id() . $currency_name;
//            }

            $sess_id = vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex(random_bytes(16)), 4));
            DB::commit();

            $token = $this->getToken();
            if ($token == null) {
                Log::channel('qt')->error('token error!');
                return '';
            }

            $countries = [
                'ru' => 'RU',
                'ua' => 'UA',
                'en' => 'CA',
                'hi' => 'IN',
                'kz' => 'KZ',
            ];
            $locales = [
                'ru' => 'ru_RU',
                'ua' => 'uk_UA',
                'en' => 'en_CA',
                'hi' => 'en_CA',
                'kz' => 'kz_KZ',
            ];
            $country = array_key_exists(App::getLocale(), $countries) ? $countries[App::getLocale()] : 'CA';
            $locale = array_key_exists(App::getLocale(), $locales) ? $locales[App::getLocale()] : 'en_CA';

            $curl = curl_init();
            curl_setopt($curl, CURLOPT_URL, "https://api.qtplatform.com/v1/games/$game_id/launch-url");
            curl_setopt($curl, CURLOPT_POST, true);

            if ($currency_name === 'USDT') $currency_name = 'USD';
            $post = [
                'playerId' => urlencode($player_id),
                'currency' => $currency_name,
                'country' => $country,
                'lang' => $locale,
                'mode' => 'real',
                'device' => 'desktop',
                'returnUrl' => route('landing', [], true),
                'walletSessionId' => $sess_id,
            ];

            //start game session on users service
            $game_data = new GameData($user);
            $provider = GamesManager::getProvider(GamesProvider::GAME_SYSTEM_QT);
            $game = $provider->getGame('', $game_id, true);
            $game_data->loadDataForGame($game->getProviderId(), $game->getId(), $user->currency)->save();
            UserManager::startGameSession($user, $game, $game_data->toaArray(), $sess_id);

            Log::channel('qt')->info('Creating qt game data:' . var_export($post, true));

            curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($post));
            curl_setopt($curl, CURLOPT_HTTPHEADER, array(
                'Content-Type: application/json',
                'Accept: application/json',
                'Authorization: Bearer ' . $token
            ));
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            $html = curl_exec($curl);
            Log::channel('qt')->info($html);
            if (curl_errno($curl)) {
                $game_url = [];
            } else {
                $game_url = json_decode($html, true);
            }

            curl_close($curl);

            return $game_url['url'];
        } catch (\Throwable $e) {
            DB::commit();
            Hlog::logException($e, 'qt');
        }
        return '';
    }

    /** Только для демо
     * @param $type
     * @param $game_key
     * @param $user
     * @return mixed|string
     */
    public function getQTGameUrl($type, $game_key, $user)
    {
        $token = $this->getToken();
        if ($token == null) return '';

        $countries = [
            'ru' => 'RU',
            'ua' => 'UA',
            'en' => 'CA',
            'hi' => 'IN',
            'kz' => 'KZ',
        ];
        $locales = [
            'ru' => 'ru_RU',
            'ua' => 'uk_UA',
            'en' => 'en_CA',
            'hi' => 'en_CA',
            'kz' => 'kz_KZ',
        ];
        $country = array_key_exists(App::getLocale(), $countries) ? $countries[App::getLocale()] : 'CA';
        $locale = array_key_exists(App::getLocale(), $locales) ? $locales[App::getLocale()] : 'en_CA';

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, "https://api.qtplatform.com/v1/games/$game_key/launch-url");
        //curl_setopt($curl, CURLOPT_URL, "https://api.qtplatform.com/v1/games/lobby-url");
        curl_setopt($curl, CURLOPT_POST, true);
        $currency = $user?->getCurrencyName() ?? 'USD';
        if ($currency === 'USDT') $currency = 'USD';
        $post = [
            'playerId' => $user ? urlencode($user->id . $user->getCurrencyName()) : urlencode('demo'),
            'currency' => $currency,
            'country' => $country,
            'lang' => $locale,
            'mode' => $type,
            'device' => 'desktop',
            'returnUrl' => route('landing', null, true),
        ];

//        if ($type === 'real') $post['walletSessionId'] = QtWallet::getForUser($user)->session_id;

        curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($post));
        curl_setopt($curl, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
            'Accept: application/json',
            'Authorization: Bearer ' . $token
        ));
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        $html = curl_exec($curl);
        Log::channel('qt')->info($html);
        if (curl_errno($curl)) {
            $game_url = [];
        } else {
            $game_url = json_decode($html, true);
        }

        curl_close($curl);

        return $game_url['url'];
    }

    /**
     * @param $player_id
     * @param $game_id
     * @param $total_bet
     * @param int $validity_days
     * @param $txn_id
     * @return array|mixed
     */
    public function createFreeRounds(string $bonus_id, int $player_id, string $game_id, float $totalBetValue, int $validity_days = 7, $txn_id = null)
    {
        if (is_null($txn_id)) $txn_id = Str::random(16);

        if ($validity_days > 7 || $validity_days < 1)
            return ['code' => 'VALIDATION_ERROR', 'message' => 'Wrong validity days number(1 - 7)'];

        $user = MUser::find($player_id);
        $token = $this->getToken();

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, "https://api.qtplatform.com/v1/bonus/free-rounds");
        curl_setopt($curl, CURLOPT_POST, true);
        $post = [
            'txnId' => $txn_id,
            'playerId' => 'bonus' . $player_id . $user->getCurrencyName(),
            'currency' => $user->getCurrencyName(),
            'gameId' => $game_id,
            'roundOptions' => null,
            'totalBetValue' => $totalBetValue,
            'validityDays' => $validity_days,
            'promoCode' => $bonus_id,
            'rejectable' => true,
        ];
        curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($post));
        curl_setopt($curl, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
            'Accept: application/json',
            'Authorization: Bearer ' . $token
        ));
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        $html = curl_exec($curl);
        if (curl_errno($curl)) {
            $response = [];
        } else {
            $response = json_decode($html, true);
        }

        curl_close($curl);

        Log::channel('qt')->info('create fs resp:' . $html);
        return $response;
    }

    public function getFreeRounds($player_id, $bonus_id)
    {
        $token = $this->getToken();

        $user = MUser::find($player_id);
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, "https://api.qtplatform.com/v1/bonus/free-rounds/$bonus_id/players/$player_id" . $user->getCurrencyName());

        curl_setopt($curl, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
            'Accept: application/json',
            'Authorization: Bearer ' . $token
        ));
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        $html = curl_exec($curl);
        if (curl_errno($curl)) {
            $response = [];
        } else {
            $response = json_decode($html, true);
        }

        curl_close($curl);

        return $response;
    }

    public function deleteFreeRounds($player_id, $bonus_id)
    {
        $token = $this->getToken();

        $user = MUser::find($player_id);

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, "https://api.qtplatform.com/v1/bonus/free-rounds/$bonus_id/players/$player_id" . $user->getCurrencyName());
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "DELETE");
        curl_setopt($curl, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
            'Accept: application/json',
            'Authorization: Bearer ' . $token
        ));
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        $html = curl_exec($curl);
        if (curl_errno($curl)) {
            $response = [];
        } else {
            $response = json_decode($html, true);

        }

        return $response;
    }
}
