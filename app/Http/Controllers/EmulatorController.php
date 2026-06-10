<?php

namespace App\Http\Controllers;

use App\GamesProviders\ApiLX\ApiLX;
use App\GamesProviders\B2BSlots\B2BSlotsApi;
use App\GamesProviders\Mobule\MobuleApi;
use App\GamesProviders\PragmaticPlay\PragmaticPlay;
use App\GamesProviders\Slotegrator\SlotegratorApi;
use App\GamesProviders\GamesManager;
use App\GamesProviders\GamesProvider;
use App\GamesProviders\Qt\QtGamesProviderApi;
use App\Helpers\Localization;
use App\Helpers\Hlog;
use App\Models\Bonuses\KUserBonus;
use App\MUser;
use App\Services\Users\UserManager;
use App\UserWallet;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class EmulatorController extends Controller
{
    const GAME_TYPE_REAL = 'real';
    const GAME_TYPE_DEMO = 'demo';

    /**
     *  Запуск игры
     */
    public function index($locale, $system, $provider, $gameId, $type)
    {
        EmulatorController::transferMoneyBack();
        $layouts = request()->ajax() ? [] : ['layouts.main'];
        $p = GamesManager::getProvider($system);

        if ($p === null) {
            Log::error(__CLASS__ . " GamesManager::getProvider $system not found");
            return redirect(action('EmulatorController@closeGameFrame'));
        }

        if (Auth::guest()) $type = self::GAME_TYPE_DEMO;

        Log::info("start game $system $provider $gameId $type");

        $game = $p->getGame('', $gameId, true);
        if ($game === null) {
            Log::error(__CLASS__ . " GamesManager::getGame $system $provider $gameId not found");
        }
        $this->metaTags = $this->createDefaultMetaTags();
        $this->metaTags->title = $game->getName();

        if (Auth::check()) {

            /** @var KUserBonus $bonus */
            $bonus = KUserBonus::findFirstActiveBonus(Auth::id());
//            $bonus = KUserBonus::getFirstActiveGameBonus(Auth::id(), $system, $gameId);
//            if ($bonus === null) {
//                $bonus = KUserBonus::findFirstActiveBonus(Auth::id());
//            }
            /** @var MUser $user */
            $user = Auth::user();
            // Сохраняем текущий уровень игрока перед игрой
            $user->rank = $user->getRank()->rank_level;

//            // отменяем кешбэк бонусы для игр на лайве
//            if ($user && $type == self::GAME_TYPE_REAL
//                && $system != GamesProvider::GAME_SYSTEM_PRAGMATIC
//                && $system != GamesProvider::GAME_SYSTEM_QT
//                && $system != GamesProvider::GAME_SYSTEM_SLOTEGRATOR) {
//                KUserBonus::cancelCashBackBonuses($user);
//            }

            if ($user !== null) {
                $balance = request()->get('balance');
                if ($bonus !== null && $bonus->forceToPlay()) $balance = 'bonus';
                $user->balance_mode = MUser::BALANCE_MODE_MAIN;
                Log::info('Balance: ' . $balance);

                if ($balance === 'main') {
                    $user->balance_mode = MUser::BALANCE_MODE_MAIN;
                }

                if ($balance === 'bonus') {
                    //bonus can start disabled games
//                    $game = $p->getGame($provider, $gameId, true);
                    $game = $p->getGame("", $gameId, true);
                    if ($game !== null) {
                        $this->metaTags = $this->createDefaultMetaTags();
                        $this->metaTags->title = $game->getName();
                    }
                    if ($bonus !== null && $bonus->isGameAllowed($game)) {
                        $user->balance_mode = MUser::BALANCE_MODE_BONUS;
                    } else {
                        Log::info('bonus mode bonus not found or not allowed ' . var_export($bonus->getId(), true));
                        $user->balance_mode = MUser::BALANCE_MODE_MAIN;
                    }
                }

                $user->save();
            }
        }

        $gamesProvider = GamesManager::getProvider($system);
        if ($gamesProvider === null || !$gamesProvider->isEnabled()) {
            Log::error('Not allowed provider: ' . $system . '/' . $provider . '/' . $gameId);
            return redirect()->to('/');
        }


        // QT
        if ($system == GamesProvider::GAME_SYSTEM_QT) {
            $qt_api = new QtGamesProviderApi();

            if ($type == 'real') {
                /** @var MUser $user */
                $user = Auth::user();
                $connect_link = $qt_api->startGame($gameId, $user);
            } else {
                $connect_link = $qt_api->getQTGameUrl($type, $gameId, Auth::user());
            }

            if (filter_var($connect_link, FILTER_VALIDATE_URL) === false) {
                return $connect_link;
            }

            $this->renderAdaptive('games.goplay', ['game' => $game, 'url' => $connect_link . '&tag=' . $type], $layouts);
            return $this->sendPreparedResponse();
        }

        //Slotegrator
        if ($type == self::GAME_TYPE_DEMO and $system === GamesProvider::GAME_SYSTEM_SLOTEGRATOR) {
            $api = new SlotegratorApi();
            $this->renderAdaptive('games.goplay', ['game' => $game, 'url' => $api->initDemo($gameId)['url'] ?? ''], $layouts);
            return $this->sendPreparedResponse();
        }
        if ($type == self::GAME_TYPE_REAL and $system === GamesProvider::GAME_SYSTEM_SLOTEGRATOR) {
            /** @var MUser $user */
            $user = MUser::getAuth();
            $sess_id = Str::random();

//            $status_id = '67';
//            DB::table('payout_transactions')
//                ->insert([
//                    'user_id' => Auth::user()->id,
//                    'hall_id' => Auth::user()->hall_id,
//                    'date' => date('Y-m-d H:i:s'),
//                    'sum' => $user->getBalance(),
//                    'status_id' => $status_id,
//                    'currency' => $user->getCurrencyName(),
//                    'operation' => 'Перевод на Sleg'
//                ]);

            if (in_array($user->getId(), SlotegratorApi::$stage_users)) {
                $api = new SlotegratorApi(true);
                $user->currency = UserWallet::CURRENCY_EUR;
                $user->save();
            } else {
                $api = new SlotegratorApi();
            }
            $api->transferMoney($user, $gameId, $sess_id);
            $url = $api->initGame($gameId, $user)['url']?? '';

            $this->renderAdaptive('games.goplay', ['game' => $game, 'url' => $url, 'bonus' => $bonus], $layouts);
            return $this->sendPreparedResponse();
        }


        // Pragmatic Play
        if ($system == GamesProvider::GAME_SYSTEM_PRAGMATIC) {
            if ($type == self::GAME_TYPE_DEMO) {
                $api = new PragmaticPlay();
                $this->renderAdaptive('games.goplay', ['game' => $game, 'url' => $api->initDemoGame($gameId) ?? ''], $layouts);
                return $this->sendPreparedResponse();
            }

            if ($type == self::GAME_TYPE_REAL) {
                if (Auth::guest()) {
                    return '';
                }

                /** @var MUser $user */
                $user = Auth::user();

//                $status_id = '67';
//                DB::table('payout_transactions')
//                    ->insert([
//                        'user_id' => $user->id,
//                        'hall_id' => $user->hall_id,
//                        'date' => date('Y-m-d H:i:s'),
//                        'sum' => $user->getBalance(),
//                        'status_id' => $status_id,
//                        'currency' => $user->getCurrencyName(),
//                        'operation' => 'Перевод на PragmaticPlay'
//                    ]);

                $api = new PragmaticPlay();
                $api->closeLastGame($gameId, $user);

                $sess_id = Str::random();

                $url = $api->initGame($gameId, $user, $sess_id) ?? '';

                $this->renderAdaptive('games.goplay', ['game' => $game, 'url' => $url, 'bonus' => $bonus], $layouts);
                return $this->sendPreparedResponse();
            }
        }

        if ($system == GamesProvider::GAME_SYSTEM_B2B_SLOTS) {
            if ($type == self::GAME_TYPE_DEMO) {
                return "";
            }

            if ($type == self::GAME_TYPE_REAL) {
                if (Auth::guest()) {
                    return '';
                }

                /** @var MUser $user */
                $user = Auth::user();

                $api = new B2BSlotsApi();
                $api->closeSession($user);

                $sess_id = Str::random();

                $url = $api->startSession($user, $gameId, $sess_id) ?? '';

                $this->renderAdaptive('games.goplay', ['game' => $game, 'url' => $url], $layouts);
                return $this->sendPreparedResponse();
            }
        }

        if ($system == GamesProvider::GAME_SYSTEM_MOBULE) {
            $api = new MobuleApi();

            if ($type == self::GAME_TYPE_DEMO) {
                $currencyId = Localization::getLocaleCurrency(App::getLocale());
                $currency = UserWallet::currencyName($currencyId);
                $url = $api->startDemoGame($game->getGroupId(), $gameId, $currency, action('EmulatorController@closeGameFrame'));
                $this->renderAdaptive('games.goplay', ['game' => $game, 'url' => $url], $layouts);
                return $this->sendPreparedResponse();
            }

            if ($type == self::GAME_TYPE_REAL) {
                if (Auth::guest()) {
                    return '';
                }

                /** @var MUser $user */
                $user = Auth::user();
                $sess_id = Str::random();

                $url = $api->startSession($user, $gameId, $sess_id) ?? '';
                Log::info('Mobule url ' . $url);

                $this->renderAdaptive('games.goplay', ['game' => $game, 'url' => $url, 'bonus' => $bonus], $layouts);
                return $this->sendPreparedResponse();
            }
        }

        if ($system == GamesProvider::GAME_SYSTEM_APILX) {
            $api = new ApiLX();

            if ($type == self::GAME_TYPE_DEMO) {
               return '';
            }

            if ($type == self::GAME_TYPE_REAL) {
                if (Auth::guest()) {
                    return '';
                }

                /** @var MUser $user */
                $user = Auth::user();
                $sess_id = Str::random();

                $url = $api->startSession($user, $gameId, $sess_id) ?? '';
                Log::info('Mobule url ' . $url);

                $this->renderAdaptive('games.goplay', ['game' => $game, 'url' => $url, 'bonus' => $bonus], $layouts);
                return $this->sendPreparedResponse();
            }
        }
    }

    /**
     * Закрывает игровую сессию
     * @return void
     */
    public static function transferMoneyBack()
    {
        /** @var MUser $user */
        $user = Auth::user();
        if ($user === null) return;

        try {
            $gs = UserManager::getGameSession($user);
            if ($gs['cd'] === 200) {
                $system_name = $gs['gs']['GameStartData']['system'];
                $provider = GamesManager::getProvider($system_name);
                $closed_sess = $provider->getApi()->closeSession($user, $gs['gs']['ID']);
                if ($gs['gs']['Stats']['total_bet'] > 0 || $gs['gs']['Stats']['full_total_bet'] > 0) {
                    UserManager::addEvent($user->getId(), 'bets');
                }
            }
        } catch (\Exception $e) {
            Hlog::logException($e);
        }

    }

    public function closeGameFrame()
    {
        return $this->viewOrJson('common.close-game-frame');
    }
}
