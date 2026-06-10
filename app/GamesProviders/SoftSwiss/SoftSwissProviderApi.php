<?php

namespace App\GamesProviders\SoftSwiss;

use App\GamesProviders\GameProviderApi;
use App\GamesProviders\GamesProvider;
use App\GamesProviders\SoftSwiss\Models\FsStatus;
use App\GamesProviders\SoftSwiss\Models\SoftSwissFs;
use App\GamesProviders\SoftSwiss\Models\SoftSwissGameSession;
use App\GamesProviders\SoftSwiss\Models\SoftSwissWallet;
use App\Helpers\Device;
use App\Services\IpInfo\IpInfo;
use App\Jobs\ExitGameJob;
use App\Models\Bonuses\UserBonus;
use App\Models\Tournaments\TournamentGames\TournamentGame;
use App\MUser;
use App\PayoutTransaction;
use App\UserWallet;
use Carbon\Carbon;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;
use Symfony\Component\Yaml\Yaml;

class SoftSwissProviderApi extends GameProviderApi
{
    private string $_token = "bk399wap0184e0yg";
    private string $_casino_id = "kraken_stg";
    private string $_backend_url = "https://casino.int.a8r.games";
    private static string $_script_url = "https://casino.int.a8r.games/public/sg.js";

    private int $status_code = 0;

    public static function getScriptUrl(): string
    {
        return self::$_script_url;
    }

    public function getAllGames(): array
    {
        $items = Redis::get('softswiss_cached_games');
        if (!empty($items)) return json_decode($items, true);
        return self::cacheGames();
    }
    public static function cacheGames(): array
    {
        try {
            $games = [];
            foreach (glob(__DIR__.'/games/*.{yml}', GLOB_BRACE) as $filename) {
                $parse = Yaml::parse(file_get_contents($filename));
                $games = array_merge($games, $parse);
            }
            //store games
            if (!empty($games)) Redis::set('softswiss_cached_games', json_encode($games));
            else Redis::del('softswiss_cached_games');
            return $games;
        } catch (\Exception $e) {
            Log::error('Unable to fetch pragplay games: '.$e->getMessage());
            return [];
        }
    }

    public function startSession(MUser $user, string $game_id, string $session_id = ""): string
    {
        return $this->startGame($game_id, $user);
    }

    public function startGame(string $game_id, MUser $user)
    {
        try {
            $this->transferMoneyBack($user);
            DB::beginTransaction();
            Log::channel('softswiss')->info('Creating softswiss game, user_id:' . $user->id . ' game_id:' . $game_id);

            $currency_name = $user->getCurrencyName();
            $wallet = SoftSwissWallet::getForUser($user, UserWallet::getCurrencyCode($currency_name));

            $player_id = '' . Auth::id() . $currency_name;

            if ($user->hasActiveVegasBonusGame($game_id) && $user->balance_mode != MUser::BALANCE_MODE_MAIN) $player_id = 'bonus' . $player_id;

//            $trg = $user->getActiveTournamentGame();
//
//            if ($trg && $user->balance_mode === MUser::BALANCE_MODE_TOURNAMENT) {
//                $currency_name = 'USD';
//                $player_id = 'bonus' . Auth::id() . $currency_name;
//            }

            if (empty($wallet->session_id) || $this->shouldStartNewSession($user, $wallet, $game_id)) {
                $wallet->session_id = vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex(random_bytes(16)), 4));
                $wallet->save();
            }

            DB::commit();

            $softSwissGameSession = new SoftSwissGameSession();
            $softSwissGameSession->user_id = $user->id;
            $softSwissGameSession->hall_id = UserWallet::currencyHallId($user->currency);
            $softSwissGameSession->currency = $user->currency;
            $softSwissGameSession->login_date = date('Y-m-d H:i:s');
            $softSwissGameSession->login_balance = $user->getBalance();
            $softSwissGameSession->game_id = $game_id;
            $softSwissGameSession->session_id = $wallet->session_id;
            $softSwissGameSession->last_active_date = date('Y-m-d H:i:s');
            $softSwissGameSession->save();

            $this->transferMoney($user, $softSwissGameSession->id);

            $ip_inf = IpInfo::getInfo($user->last_ip);

            $req_data = [
                'casino_id' => $this->_casino_id,
                'game' => $game_id,
                'currency' => $currency_name,
                'locale' => App::getLocale(),
                'ip' => request()->ip(),
                'client_type' => Device::detect() === Device::DESKTOP? 'desktop' : 'mobile/desktop',
                'balance' => $wallet->getBalanceCents(),
                'urls' => [
                    'return_url' => route('landing'),
                    'deposit_url' => route('deposit'),
                ],
                'user' => [
                    'id' => ''.$player_id,
                    'external_id' => ''.$user->getId(),
                    'email' => $user->email,
                    'firstname' => $user->personalData()?->name?? 'noname',
                    'lastname' => $user->personalData()?->surname?? 'nolastname',
                    'nickname' => $user->login,
                    'city' => $user->personalData()?->city?? 'nocity',
                    'date_of_birth' => $user->personalData()?->birthday?? '1980-01-01',
                    'registered_at' => $user->created_at->format('Y-m-d'),
                    'gender' => 'm',
                    'country' => $ip_inf?->country_code?? 'RU',
                ],
            ];

            return $this->query($this->_backend_url.'/sessions', 'post', $req_data);

        } catch (\Exception $e) {
            DB::commit();
            Log::channel('softswiss')->error($e->getMessage());
            Log::channel('softswiss')->error($e->getFile());
            Log::channel('softswiss')->error($e->getLine());
            Log::channel('softswiss')->error($e->getTraceAsString());
        }
    }

    public function startDemoGame(string $game_id)
    {
        $req_data = [
            'casino_id' => $this->_casino_id,
            'game' => $game_id,
            'locale' => App::getLocale(),
            'ip' => request()->ip(),
            'client_type' => Device::detect() === Device::DESKTOP? 'desktop' : 'mobile/desktop',
            'urls' => [
                'return_url' => route('landing'),
            ],
        ];

        return $this->query($this->_backend_url.'/demo', 'post', $req_data);
    }

    public function transferMoneyInGame(MUser $user, float $amount): bool
    {
       return false;
    }

    public function transferMoney(MUser $user, string $session_id = ''): void
    {
        try {
            DB::beginTransaction();
            $softswiss_wallet = SoftSwissWallet::getForUser($user);
            $wallet = $user->getWallet();
            $money = $wallet->getBalance();
            $wallet->remMoney($money);
            $softswiss_wallet->addMoney($money);
            $softswiss_wallet->token = $session_id;
            $softswiss_wallet->save();
        } catch (\Exception $e) {
            Log::channel('softswiss')->error($e->getMessage());
            DB::rollBack();
        }
        DB::commit();
    }

    public function shouldStartNewSession(MUser $user, SoftSwissWallet $wallet, string $game_id): bool
    {
        $new_session = false;

        /** @var SoftSwissGameSession $last_game */
        $last_game = SoftSwissGameSession::where('user_id', Auth::id())->orderByDesc('last_active_date')->take(1)->first();

        //new session is user plays on main balance after bonus or tournament balance
        if ($wallet->bonus_id && $user->balance_mode == MUser::BALANCE_MODE_MAIN) {
            $wallet->bonus_id = 0;
            $wallet->save();
            $new_session = true;
        } //always start a new session when user has completed bonus
        elseif ($wallet->bonus_id && !UserBonus::findFirstActiveBonus(Auth::id()) && !TournamentGame::findFirstActiveGame(Auth::id())) {

            $wallet->bonus_id = 0;
            $wallet->save();
            $new_session = true;
        } elseif ($wallet->bonus_id) {
            //continue session for same bonus
            if ($wallet->bonus_id && UserBonus::findFirstActiveBonus(Auth::id()) && $wallet->bonus_id == UserBonus::findFirstActiveBonus(Auth::id())->id) {
                //clear session when start new game
                if ($last_game && $last_game->game_id != $game_id) {
                    $new_session = true;
                } else {
                    $new_session = false;
                }
            } //or tournament game
            elseif ($wallet->bonus_id && TournamentGame::findFirstActiveGame(Auth::id()) && $wallet->bonus_id == TournamentGame::findFirstActiveGame(Auth::id())->id) {
                //clear session when start new game
                if ($last_game && $last_game->game_id != $game_id) {
                    $new_session = true;
                } else {
                    $new_session = false;
                }
            } else {
                $wallet->bonus_id = null;
                $wallet->save();
                $new_session = true;
            }
        }
        return $new_session;
    }

    public function transferMoneyBack(MUser $user, string $session_id = ""): bool
    {
        DB::beginTransaction();
        try {
            $wallet = SoftSwissWallet::getForUser($user);
            $money = $wallet->getBalance();

            /** @var SoftSwissGameSession $softswiss_session */
            $softswiss_session = SoftSwissGameSession::where('id', $wallet->token)
                ->whereNull('logout_date')->lockForUpdate()->first();

            $wallet->token = null;
            $wallet->amount = 0;
            $wallet->save();

            $user->addMoney($money, $wallet->currency);

        } catch (\Exception $exception) {
            DB::rollBack();
            Log::channel('softswiss')->error($exception->getMessage());
            return true;
        }

        if ($softswiss_session !== null) {

            try {

                $softswiss_session->logout_date = date('Y-m-d H:i:s');
                $softswiss_session->logout_balance = $money;
                $softswiss_session->last_active_date = date('Y-m-d H:i:s');

                $currency = $user->getCurrencyName($wallet->currency);

//                DB::table('payout_transactions')
//                    ->insert([
//                        'user_id' => $user->id,
//                        'hall_id' => $user->hall_id,
//                        'date' => date('Y-m-d H:i:s'),
//                        'sum' => ($money),
//                        'status_id' => PayoutTransaction::STATUS_SOFTSWISS_RETURN,
//                        'currency' => $currency,
//                        'operation' => 'Выход с SoftSwiss'
//                    ]);

                $softswiss_session->save();

//                if ($money < 10) {
//                    //cashback
//                    /** @var CashbackEventBonus $cashback_bonus */
//                    $cashback_bonus = CashbackEventBonus::where('user_id', $user->id)
//                        ->where('hall_id', $user->hall_id)
//                        ->where('type', UserBonus::TYPE_EVENT_CASH_BACK)
//                        ->where('status', UserBonus::STATUS_PENDING)->first();
//
//                    if ($cashback_bonus) {
//                        $user->addMoney($cashback_bonus->money_available);
//                        $cashback_bonus->status = UserBonus::STATUS_DONE;
//                        $wallet->save();
//                        $cashback_bonus->save();
//
//                        DB::table('payout_transactions')
//                            ->insert([
//                                'user_id' => $user->id,
//                                'hall_id' => $user->hall_id,
//                                'date' => date('Y-m-d H:i:s'),
//                                'sum' => $money,
//                                'status_id' => 35,
//                                'currency' => UserWallet::currencyName($cashback_bonus->currency),
//                                'operation' => 'CashBack bonus payed in qt'
//                            ]);
//
//                        $wallet->session_id = null;
//                    }
//
//                    /** @var UserBonus $wl_bonus */
//                    $wl_bonus = UserBonus::getWaitingLose($user);
//                    $wl_bonus?->onUserLose();
//                }

                ExitGameJob::dispatch(GamesProvider::GAME_SYSTEM_SOFT_SWISS, $softswiss_session::class, $softswiss_session->id, $currency);

            } catch (\Exception $exception) {
                DB::rollBack();
                Log::channel('softswiss')->error($exception->getMessage());
                return false;
            }
        }

        DB::commit();
        return true;
    }

    public function closeSession(MUser $user, string $session_id = ""): bool
    {
        return $this->transferMoneyBack($user, $session_id);
    }

    public function getBalance(MUser $user, string &$currency): float
    {
        return SoftSwissWallet::getForUser($user)?->getBalance()?? 0;
    }

    /**
     * create freespins
     * @param MUser $user
     * @param string $issue_id
     * @param string $currency
     * @param array $games
     * @param int $fs_quantity
     * @return array
     */
    public function freespinsCreate(string $player_id, MUser $user, string $issue_id, string $currency, array $games, int $fs_quantity, ?Carbon $active_to = null): array
    {
        try {
            $ip_inf = IpInfo::getInfo($user->last_ip);

            $data = [
                'casino_id' => $this->_casino_id,
                'issue_id' => $issue_id,
                'currency' => $currency,
                'games' => $games,
                "valid_until" => $active_to?->format('Y-m-d\TH:i:s\Z')?? now()->addDays(3)->format('Y-m-d\TH:i:s\Z'),
                'freespins_quantity' => $fs_quantity,
                'user' => [
                    'id' => $player_id,
                    'external_id' => ''.$user->getId(),
                    'email' => $user->email,
                    'firstname' => $user->personalData()?->name?? 'noname',
                    'lastname' => $user->personalData()?->surname?? 'nolastname',
                    'nickname' => $user->login,
                    'city' => $user->personalData()?->city?? 'nocity',
                    'date_of_birth' => $user->personalData()?->birthday?? '1980-01-01',
                    'registered_at' => $user->created_at->format('Y-m-d'),
                    'gender' => 'm',
                    'country' => $ip_inf?->country_code?? 'RU',
                ],
            ];

            $spins = new SoftSwissFs([
                'spins_id' => $issue_id,
                'user_id' => $user->getId(),
                'currency' => $currency,
                'status' => FsStatus::NotActivated,
                'amount' => $fs_quantity,
            ]);
            $spins->save();

            $resp = $this->query($this->_backend_url.'/freespins/issue', 'post', $data);

            if ($this->status_code === 200) {
                $spins->setStatus(FsStatus::Active);
                $spins->save();
            }

            return $resp?? [];

        } catch (\Exception $e) {
            Log::channel('softswiss')->error($e->getMessage());
        }
        return [];
    }

    /**
     * cancel freespins
     * @param string $bonus_id
     * @return array
     */
    public function freespinsCancel(string $bonus_id): array
    {
        try {
            $data = [
                'casino_id' => $this->_casino_id,
                'issue_id' => $bonus_id,
            ];

            /** @var SoftSwissFs $spins */
            $spins = SoftSwissFs::where('spins_id', $bonus_id)->first();

            $resp = $this->query($this->_backend_url.'/freespins/cancel', 'post', $data);

            if ($this->status_code === 200) {
                $spins?->setStatus(FsStatus::Canceled);
                $spins?->save();
            }

            return $resp?? [];
        } catch (\Exception $e) {
            Log::channel('softswiss')->error($e->getMessage());
        }
        return [];
    }

    public function sign($json_data): string
    {
        return hash_hmac('sha256', $json_data, $this->_token);
    }

    private function query($url, $http_method, array $data = [])
    {
        $json_data = json_encode($data);
        $sign = $this->sign($json_data);

        $ch = curl_init();


        if ( strtolower($http_method) === 'post') {

            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                "Content-Type: application/json",
                "X-REQUEST-SIGN: ". $sign,
            ]);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $json_data);

        } else {
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                "X-REQUEST-SIGN: ". $sign,
            ]);

            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
            if (!empty($data)) {
                $url .= '?' . http_build_query($data);
            }
        }

        Log::channel('softswiss')->info('softswiss request: '.$url);
        Log::channel('softswiss')->info(var_export($data, true));

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);

        curl_setopt($ch, CURLOPT_USERAGENT, 'KrakenCassino');
        curl_setopt($ch, CURLOPT_TIMEOUT, 90);
        curl_setopt($ch, CURLOPT_MAXREDIRS, 10);

        curl_setopt($ch, CURLOPT_VERBOSE, true);

        $verbose = fopen('php://temp', 'w+');
        curl_setopt($ch, CURLOPT_STDERR, $verbose);

        $text = curl_exec($ch);
        $err = curl_error($ch);
        $resp_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $this->status_code = $resp_code;
        $resp = json_decode($text, true);

        rewind($verbose);
        $verboseLog = stream_get_contents($verbose);

        Log::channel('softswiss')->info('curl verbose');
        Log::channel('softswiss')->info(htmlspecialchars($verboseLog));

        if ($err) {
            Log::channel('softswiss')->error($err);
        }

        Log::channel('softswiss')->info('softswiss resp:');
        Log::channel('softswiss')->info('code:'.$resp_code);
        Log::channel('softswiss')->info($text);
        Log::channel('softswiss')->info(var_export($resp, true));
        return $resp;
    }
}
