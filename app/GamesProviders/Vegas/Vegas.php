<?php

namespace App\GamesProviders\Vegas;

use App\GamesProviders\GamesProvider;
use App\Http\Controllers\Endpoints\VegasEndpointController;
use App\Jobs\ExitGameJob;
use App\Models\Bonuses\UserBonus;
use App\Models\Tournaments\TournamentGames\TournamentGame;
use App\Models\VegasGameSession;
use App\MUser;
use App\UserWallet;
use App\VegasWallet;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;


/**
 * @deprecated
 */
class Vegas
{
    private static $client = null;

    public static function getClient()
    {
        return null;
    }

    public static function getListGames()
    {
        $games = Redis::get('vegas_cached_games');
        if (!empty($games)) {
            return json_decode($games, true);
        }
        return [];
    }

    public static function cacheGames()
    {
        Redis::del('vegas_cached_games');
        return [];
    }

    public static function findGame(string $game_id)
    {
        foreach (self::getListGames() as $game) {
            if ($game['Id'] == $game_id) return $game;
        }
        return false;
    }

    public static function getGameImg(string $game_id)
    {
        $game = self::findGame($game_id);
        if (!empty($game)) {
            $img = '/vegasimg/' . $game['SectionId'] . '/' . $game['Id'] . '.jpg';
            if (!file_exists(public_path() . $img)) {
                $img = '/images/noimage.jpg';
            };
            return $img;
        }
        return '/images/noimage.jpg';
    }

    public static function getGameSections()
    {
        try {
            $all_games = self::getListGames();
        } catch (\Exception $e) {
            $all_games = [];
        }
        $sorted_games = [];

        foreach ($all_games as $game) {
            if (!isset($sorted_games[$game['SectionId']])) {
                $sorted_games[$game['SectionId']] = [];
            }
            $sorted_games[$game['SectionId']][] = $game;
        }
        return $sorted_games;
    }

    public static function getDemoUrl($game_id)
    {
        return self::getClient()->createDemoSession(['GameId' => $game_id, 'BankGroupId' => 'bonus', 'StartBalance' => 10000, 'Language' => App::getLocale()])['SessionUrl'];
    }

    public static function getRealUrl($game_id, $bonus_id)
    {
        Log::info('Creating vegas game, user_id:' . '' . Auth::user()->id . ' game_id:' . $game_id);

        /** @var MUser $user */
        $user = MUser::find(Auth::id());

        $currency_name = $user->getCurrencyName();

        $player_id = '' . Auth::id() . $currency_name;

        $new_session = false;
        $lang = App::getLocale();

        if ($user->hasActiveVegasBonusGame($game_id) && $user->balance_mode != MUser::BALANCE_MODE_MAIN) $player_id = 'bonus' . $player_id;

        if ($user->isDemoUser()) {
            $player_id = 'bonus' . $player_id;
        }

        $trg = $user->getActiveTournamentGame();
        if ($trg && $user->balance_mode === MUser::BALANCE_MODE_TOURNAMENT) {
            $currency_name = 'USD';
            $player_id = 'bonus' . Auth::id() . $currency_name;
        }

        $params = [
            'PlayerId' => $player_id,
            'GameId' => $game_id,
            'RestorePolicy' => 'Last',
            'Params' => [
                'language' => $lang
            ],
            'BonusId' => $bonus_id,
        ];

        Log::info('Vegas user ID: ' . $player_id);
        Log::info('lang: ' . $lang);
        Log::info('vegas start params: ' . var_export($params, true));

        $wallet = VegasWallet::getForUser($user);
        /** @var VegasGameSession $last_game */
        $last_game = VegasGameSession::where('user_id', Auth::id())->orderByDesc('last_active_date')->skip(1)->take(1)->first();

        //new session is user plays on main balance after bonus or tournament balance
        if ($wallet->bonus && $user->balance_mode == MUser::BALANCE_MODE_MAIN) {
            $wallet->bonus = 0;
            $wallet->save();
            $params['RestorePolicy'] = 'Create';
            return self::getClient()->createSession($params)['SessionUrl'];
        }

        //always start a new session when user has completed bonus
        if ($wallet->bonus && !UserBonus::findFirstActiveBonus(Auth::id()) && !TournamentGame::findFirstActiveGame(Auth::id())) {

            $wallet->bonus = 0;
            $wallet->save();

            $params['RestorePolicy'] = 'Create';
            return self::getClient()->createSession($params)['SessionUrl'];
        }

        if ($wallet->bonus) {
            //continue session for same bonus
            if ($wallet->bonus && UserBonus::findFirstActiveBonus(Auth::id()) && $wallet->bonus == UserBonus::findFirstActiveBonus(Auth::id())->id) {
                //clear session when start new game
                if ($last_game && $last_game->game_id != $game_id) {
                    $params['RestorePolicy'] = 'Create';
                    return self::getClient()->createSession($params)['SessionUrl'];
                }
                $params['RestorePolicy'] = 'Last';
                return self::getClient()->createSession($params)['SessionUrl'];
            }
            //or tournament game
            if ($wallet->bonus && TournamentGame::findFirstActiveGame(Auth::id()) && $wallet->bonus == TournamentGame::findFirstActiveGame(Auth::id())->id) {
                //clear session when start new game
                if ($last_game && $last_game->game_id != $game_id) {
                    $params['RestorePolicy'] = 'Create';
                    return self::getClient()->createSession($params)['SessionUrl'];
                }
                $params['RestorePolicy'] = 'Last';
                return self::getClient()->createSession($params)['SessionUrl'];
            }
            //else clear
            $wallet->bonus = 0;
            $wallet->save();

            $params['RestorePolicy'] = 'Create';
            return self::getClient()->createSession($params)['SessionUrl'];
        }

        $params['RestorePolicy'] = 'Last';
        return self::getClient()->createSession($params)['SessionUrl'];
    }

    /**
     * use only without seamless mode
     * @return mixed
     * @deprecated
     */
    public static function getBalance()
    {
        return self::getClient()->getBalance(['PlayerId' => '' . Auth::id()])['Amount'];
    }

    public static function transferMoney($session_id = null)
    {
        //        self::getClient()->changeBalance(['PlayerId'=>''.Auth::user()->id, 'Amount'=>intval(floor(Auth::user()->money*100))]);
//        DB::raw('SET autocommit = 0');
        Log::info('start vegas session'.$session_id);
        self::closeSession();
        DB::beginTransaction();
        try {
            /** @var MUser $user */
            $user = MUser::find(Auth::id());

//            /** @var VegasWallet $wallet */
//            $wallet = VegasWallet::where('user_id', $user->id)->lockForUpdate()->first();

            /** @var VegasWallet $wallet */
            $wallet = VegasWallet::getForUser($user);

//            if ($wallet->session_id !== null) {
//                Log::info('start session '.$session_id);
//                Log::info('closing session on start '.$wallet->session_id);
//            }

            $k = 100;

            $money = $user->getWallet()->getBalance();
            if ($wallet->currency == UserWallet::CURRENCY_mBTC) $k = 10000;
            $wallet->money += intval(floor($money * $k));
            $wallet->session_id = $session_id;
            $wallet->save();
            Log::info('wallet  '.var_export($wallet, true));
            Log::info('wallet session '.$wallet->session_id);

            /** @var VegasGameSession $vegasSession */
            $vegasSession = VegasGameSession::where('session_id', $session_id)->lockForUpdate()->first();
            $vegasSession->session_id = $session_id;
            $vegasSession->login_balance = $money;
            Log::info('money   '.var_export($money, true));
            $user->getWallet()->remMoney($money)->save();
            $vegasSession->save();
        } catch (\Exception $exception) {
            Log::error($exception->getMessage());
            DB::rollBack();
        }

        DB::commit();
//        DB::raw('SET autocommit = 1');
    }

    public static function createPlayer($user_id = null)
    {
        /** @var MUser $user */
        $user = MUser::find($user_id?? Auth::id());
        if ($user === null) return;
        $wallet = VegasWallet::getForUser($user);
        $currency_name = $user->getCurrencyName();
        $currency = $user->currency;

        $trg = $user->getActiveTournamentGame();
        if ($trg && $user->balance_mode === MUser::BALANCE_MODE_TOURNAMENT) {
            $currency_name = 'USD';
            $currency = UserWallet::CURRENCY_USD;
            $player_id = 'bonus' . Auth::id() . $currency_name;
        }

        try {
            self::getClient()->setPlayer(['Id' => '' . Auth::user()->id . $currency_name, 'BankGroupId' => $user->getWallet($currency)->getVegasBankName()]);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            Log::error($e->getFile());
            Log::error($e->getLine());
            Log::error($e->getTraceAsString());
        }

        try {
            self::getClient()->setPlayer(['Id' => 'bonus' . Auth::user()->id . $currency_name, 'BankGroupId' => $user->getWallet($currency)->getVegasBonusBankName()]);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            Log::error($e->getFile());
            Log::error($e->getLine());
            Log::error($e->getTraceAsString());
        }
    }


    public static function createGame($game_id, $bonus_id = null, $session_id = null)
    {
        self::createPlayer();
        self::transferMoney($session_id);
        return self::getRealUrl($game_id, $bonus_id);
    }


    public static function closeSession()
    {
        DB::beginTransaction();
        try {
            /** @var MUser $user */
            $user = MUser::find(Auth::id());
            /** @var VegasWallet $wallet */
//            $wallet = VegasWallet::where('user_id', Auth::user()->id)->lockForUpdate()->first();
            $wallet = VegasWallet::getForUser($user);
            /** @var VegasGameSession $vegas */
            $vegas = VegasGameSession::where('user_id', Auth::id())
                ->where('session_id', $wallet->session_id)
                ->whereNull('logout_date')->lockForUpdate()->first();
            if ($vegas === null) {
                DB::commit();
                return;
            }

            $money = $wallet->money;
            $k = 100;
            if ($wallet->currency == UserWallet::CURRENCY_mBTC) $k = 10000;

            $amount = $money / $k;

//        if($money == 0) return;

            $wallet->money = 0;
            $wallet->session_id = null;
            $wallet->save();
//        DB::table('m_users')
//            ->where('id', Auth::id())
//            ->increment('money', ($money/100));
            $user->addMoney($money / $k, $wallet->currency);

            /** @var VegasGameSession $vegas */
//        $vegas = VegasGame::where('user_id', Auth::id())->whereNull('logout_date')->where('login_balance', '>', 0)->latest('last_active_date')->first();

        } catch (\Exception $exception) {
            Log::error($exception->getMessage());
            DB::rollBack();
            return;
        }

        if ($vegas) {

            try {

                $vegas->logout_date = date('Y-m-d H:i:s');
                $vegas->logout_balance = $money / 100;
                $vegas->last_active_date = date('Y-m-d H:i:s');
                $vegas->save();

//                Journal::logMoneyMovement(Auth::id(), substr($vegas->game_id, 0, 11), Auth::user()->money, 'logout', 0, 0);

                $currency = $user->getCurrencyName($wallet->currency);
//                $spentAmount = $vegas->login_balance - $vegas->logout_balance;

                DB::table('payout_transactions')
                    ->insert([
                        'user_id' => Auth::id(),
                        'hall_id' => Auth::user()->hall_id,
                        'date' => date('Y-m-d H:i:s'),
                        'sum' => ($money / 100),
                        'status_id' => 68,
                        'currency' => $currency,
                        'operation' => 'Выход с Vegas'
                    ]);

                if ($amount < 10) {

                    //cashback
                    /** @var CashbackEventBonus $cashback_bonus */
                    $cashback_bonus = CashbackEventBonus::where('user_id', $user->id)
                        ->where('hall_id', $user->hall_id)
                        ->where('type', UserBonus::TYPE_EVENT_CASH_BACK)
                        ->where('status', UserBonus::STATUS_PENDING)->first();

                    if ($cashback_bonus) {
                        $user->addMoney($cashback_bonus->money_available);
                        $cashback_bonus->status = UserBonus::STATUS_DONE;
                        $wallet->save();
                        $cashback_bonus->save();

                        DB::table('payout_transactions')
                            ->insert([
                                'user_id' => $user->id,
                                'hall_id' => $user->hall_id,
                                'date' => date('Y-m-d H:i:s'),
                                'sum' => $amount,
                                'status_id' => 35,
                                'currency' => UserWallet::currencyName($cashback_bonus->currency),
                                'operation' => 'CashBack bonus payed in vegas'
                            ]);

                    }

                    /** @var UserBonus $wl_bonus */
                    $wl_bonus = UserBonus::getWaitingLose($user);
                    if ($wl_bonus) {
                        $wl_bonus->onUserLose();
                    }
                }


                //close all old sessions
//                VegasGameSession::where('user_id', Auth::id())->whereNull('logout_date')
//                    ->update(['logout_date' => date('Y-m-d H:i:s')]);

//                TopWin::userWin($user, $spentAmount * -1, $vegas->game_id, $wallet->currency, 'vegas', '');
//
//                $provider = GamesManager::getProvider(GamesProvider::GAME_SYSTEM_VEGAS);
//                $game = $provider?->getGame('', $vegas->game_id, true);
//                event(new MoneySpent($user, $spentAmount, $currency, 'game', $game));

                ExitGameJob::dispatch(GamesProvider::GAME_SYSTEM_VEGAS, $vegas::class, $vegas->id, $currency);

            } catch (\Exception $exception) {
                Log::error($exception->getMessage());
                DB::rollBack();
                return;
            }
        }

        DB::commit();
    }

    /**
     * crate shared bonus
     * @param $bonus_name
     * @param $fs_count
     * @return array
     */
    public static function createSharedBonus($bonus_name, $fs_count)
    {
        try {
            return self::getClient()->setBonus(
                [
                    'Id' => $bonus_name,
                    'FsType' => 'original',
                    'CounterType' => 'shared',
                    'SharedParams' => [
                        'FsCount' => $fs_count,
                    ],
                ],
            );
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return null;
        }
    }

    /**
     * create shared bonus
     * @param $bonus_name
     * @param $fs_count
     * @param $game_id
     * @return array
     */
    public static function createSeparateBonus($bonus_name, $fs_count, $game_id)
    {
        try {
            return self::getClient()->setBonus(
                [
                    'Id' => $bonus_name,
                    'FsType' => 'original',
                    'CounterType' => 'separate',
                    'SeparateParams' => [
                        'Games' => [
                            $game_id => ['FsCount' => $fs_count],
                        ],
                    ],
                ],
            );
        } catch (\Exception $e) {
            Log::channel('stack')->error($e->getMessage());
            return null;
        }
    }

    /**
     * create bonus account
     * @param $user_id
     * @param $bonus_id
     * @return array
     */
    public static function activateBonus($vegas_user_id, $bonus_id, $user_id = null)
    {
        Log::info('activating bonus '.$bonus_id.' for user '.$vegas_user_id. ' user_id#'.$user_id);
        try {
            @self::createPlayer($user_id);
        } catch (\Exception $e) {
            Log::channel('stack')->error($e->getMessage());
        }
        try {
            return self::getClient()->activatePlayerBonus([
                'PlayerId' => $vegas_user_id,
                'BonusId' => $bonus_id,
            ]);
        } catch (\Exception $e) {
            Log::channel('stack')->error($e->getMessage());
            return null;
        }
    }

    /**
     * add (negative remove) freespins and funds to separate bonus game
     * @param string $user_id
     * @param string $bonus_id
     * @param string $game_id
     * @param int $fs_count
     * @param int $balance
     * @return array
     */
    public static function changeFundsOnSeparateBonus(string $user_id, string $bonus_id, string $game_id, int $fs_count, int $balance = 0)
    {
        try {
            return self::getClient()->executeOperationsOnPlayerBonus([
                'BonusId' => $bonus_id,
                'PlayerId' => $user_id,
                'Operations' => [
                    [
                        'Type' => 'change',
                        'BonusAccount' => ['Type' => 'game', 'GameId' => $game_id],
                        'Change' => ['FsCount' => $fs_count, 'Balance' => $balance],
                    ],
                ],
            ]);
        } catch (\Exception $e) {
            Log::channel('stack')->error($e->getMessage());
            return null;
        }
    }

    /**
     * add (negative remove) freespins and funds to separate bonus game
     * @param string $user_id
     * @param string $bonus_id
     * @param string $game_id
     * @param int $fs_count
     * @param int $balance in cents
     * @return array
     */
    public static function changeFundsOnSharedBonus(string $user_id, string $bonus_id, int $fs_count, int $balance = 0)
    {
        try {
            return self::getClient()->executeOperationsOnPlayerBonus([
                'BonusId' => $bonus_id,
                'PlayerId' => $user_id,
                'Operations' => [
                    [
                        'Type' => 'change',
                        'BonusAccount' => ['Type' => 'shared'],
                        'Change' => ['FsCount' => $fs_count, 'Balance' => $balance],
                    ],
                ],
            ]);
        } catch (\Exception $e) {
            Log::channel('stack')->error($e->getMessage());
            return null;
        }
    }

    /**
     * transfer money from game bonus account to vegas wallet
     * @param string $user_id
     * @param string $bonus_id
     * @param string $game_id
     * @param bool $all
     * @param int $amount
     * @return array
     */
    public static function transferMoneyFromSeparateBonus(string $user_id, string $bonus_id, string $game_id, bool $all = true, int $amount = 0)
    {
        try {
            return self::getClient()->executeOperationsOnPlayerBonus([
                'BonusId' => $bonus_id,
                'PlayerId' => $user_id,
                'Operations' => [
                    [
                        'Type' => 'transfer',
                        'BonusAccount' => ['Type' => 'game', 'GameId' => $game_id],
                        'Transfer' => ['All' => $all, 'Balance' => $amount],
                    ],
                ],
            ]);
        } catch (\Exception $e) {
            Log::channel('stack')->error($e->getMessage());
            return null;
        }
    }

    /**
     * transfer money from shared bonus account to vegas wallet
     * @param string $user_id
     * @param string $bonus_id
     * @param string $game_id
     * @param bool $all
     * @param int $amount
     * @return array
     */
    public static function transferMoneyFromSharedBonus(string $user_id, string $bonus_id, bool $all = true, int $amount = 0)
    {
        try {
            return self::getClient()->executeOperationsOnPlayerBonus([
                'BonusId' => $bonus_id,
                'PlayerId' => $user_id,
                'Operations' => [
                    [
                        'Type' => 'transfer',
                        'BonusAccount' => ['Type' => 'shared'],
                        'Transfer' => ['All' => $all, 'Balance' => $amount],
                    ],
                ],
            ]);
        } catch (\Exception $e) {
            Log::channel('stack')->error($e->getMessage());
            return null;
        }
    }

    public static function setSeparateBonusFunds(string $user_id, string $bonus_id, string $game_id, int $fs_count = 0, int $balance = 0)
    {
        try {
            return self::getClient()->executeOperationsOnPlayerBonus([
                'BonusId' => $bonus_id,
                'PlayerId' => $user_id,
                'Operations' => [
                    [
                        'Type' => 'set',
                        'BonusAccount' => ['Type' => 'game', 'GameId' => $game_id],
                        'Set' => ['FsCount' => $fs_count, 'Balance' => $balance],
                    ],
                ],
            ]);
        } catch (\Exception $e) {
            Log::channel('stack')->error($e->getMessage());
            return null;
        }
    }

    public static function setSharedBonusFunds(string $user_id, string $bonus_id, int $fs_count = 0, int $balance = 0)
    {
        try {
            return self::getClient()->executeOperationsOnPlayerBonus([
                'BonusId' => $bonus_id,
                'PlayerId' => $user_id,
                'Operations' => [
                    [
                        'Type' => 'set',
                        'BonusAccount' => ['Type' => 'shared'],
                        'Set' => ['FsCount' => $fs_count, 'Balance' => $balance],
                    ],
                ],
            ]);
        } catch (\Exception $e) {
            Log::channel('stack')->error($e->getMessage());
            return null;
        }
    }

    public static function resetSeparateBonus(string $user_id, string $bonus_id, string $game_id)
    {
        try {
            $bonus_data = Vegas::getClient()->getPlayerBonus(['PlayerId' => $user_id, 'BonusId' => $bonus_id])['Separate']['Games'][$game_id];
            self::changeFundsOnSeparateBonus($user_id, $bonus_id, $game_id, -1 * $bonus_data['FsCount'], -1 * $bonus_data['Balance']);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
        }
    }

    public static function test()
    {
        $data = [
            'jsonrpc' => 2.0,
            "method" => "Balance.Change",
            "params" => [
                'OPID' => '123123',
                'CasinoId' => 333,
                'PlayerId' => '12RUB',
                'Operations' => [
                    ['Type' => 'Win', 'Amount' => 10000]
                ],
            ]
        ];

        $ctrl = new VegasEndpointController();

        $resp = new \StdClass();

        $ctrl->balanceChange($data, $resp);
        return $resp;
    }

}
