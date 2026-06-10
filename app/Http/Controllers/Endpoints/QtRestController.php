<?php

namespace App\Http\Controllers\Endpoints;

use App\Redis\Transaction\TransactionStorageInterface;
use App\GamesProviders\CurrentWin;
use App\GamesProviders\GameData;
use App\GamesProviders\GamesProvider;
use App\GamesProviders\Qt\Models\QtWallet;
use App\Helpers\Measure;
use App\Http\Controllers\Controller;
use App\Models\Bonuses\FilthyFsWashOffBonus;
use App\Models\Bonuses\NonDepositBonus;
use App\Models\Bonuses\UserBonus;
use App\Models\GameTransaction;
use App\Models\Tournaments\TournamentGames\TournamentGame;
use App\MUser;
use App\Redis\DeferredModelWriter;
use App\Redis\Transaction\RedisTransactionStorage;
use App\UserWallet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;

/**
 * @deprecated
 */
class QtRestController extends Controller
{
    const KEY = "";

    /**
     * auth
     * returns balance
     * @param $user_id
     * @param Request $request
     */
    public function verifySession($user_id, Request $request)
    {
//        $red_req = substr($request->getRequestUri(), 4);
//        Log::channel('qt')->info(var_export('http://127.0.0.1:23100'.$red_req, true));
        Log::channel('qt')->info('verifySession request:' . var_export($request->all(), true));
//        Log::channel('qt')->info('headers:' . var_export($request->headers->all(), true));
        Log::channel('qt')->info('Pass-Key header:' . var_export($request->header('Pass-Key'), true));

        Measure::logNow('qt request start');
        $headers = [
            'Pass-Key' => $request->header('Pass-Key'),
            'Wallet-Session' => $request->header('Wallet-Session'),
        ];
//        $data = $request->all();
        $resp = Http::withHeaders($headers)->get("http://127.0.0.1:23100/qt/accounts/$user_id/session?gameId=".$request->get("gameId"));
//        Log::channel('qt')->info('resp:' . var_export($resp, true));
        Log::channel('qt')->info('resp:' . var_export($resp->body(), true));
        Measure::logNow('qt request end');
        return $resp;

        if ($request->header('Pass-Key') !== self::KEY) {
            $resp = json_encode(['code' => 'LOGIN_FAILED', 'message' => 'The given pass-key is incorrect']);
            Log::channel('qt')->error($resp);
            return response($resp, 401);
        }

        if (preg_match('/^(bonus)?([0-9]+)([a-zA-z]{3,5})?$/', $user_id, $match)) {
            $is_bonus_game = !empty($match[1]);
            $user_id = $match[2];
            $currency_name = $match[3] ?? '';
        }
        /** @var MUser $user */
        $user = MUser::find($user_id);
        if ($user === null) {
            $resp = json_encode(['code' => 'INVALID_TOKEN', 'message' => 'User not found']);
            Log::channel('qt')->error($resp);
            return response($resp, 400);
        }

        $wallet = QtWallet::getBySession($user, $request->header('Wallet-Session'));
        if ($wallet === null) {
            $resp = json_encode(['code' => 'INVALID_TOKEN', 'message' => 'The given wallet session is not found or has expired.']);
            Log::channel('qt')->error($resp);
            return response($resp, 400);
        }

        $currency_name = $wallet->getCurrencyName();
        $d_bonus = null;

        if ($user->balance_mode == MUser::BALANCE_MODE_BONUS) {
            /** @var UserBonus $d_bonus */
            $d_bonus = UserBonus::findFirstActiveBonus($user->id, UserWallet::getCurrencyCode($currency_name));
            if ($d_bonus !== null) {
                $wallet->bonus_id = $d_bonus->getId();
            }
        }

        if ($currency_name === 'USDT') $currency_name = 'USD';

        if ($user->balance_mode == MUser::BALANCE_MODE_TOURNAMENT) {
            /** @var TournamentGame $trg */
            $trg = TournamentGame::findFirstActiveGame($user->id);
            if ($trg) {
                $d_bonus = $trg->castByType($trg);
                $currency_name = 'USD';
            }
        }

        if ($d_bonus !== null) {
            Log::channel('qt')->info('bonus balance');
            $d_bonus->refreshBonusStatus($wallet, 'qt', $request['gameId']??'');

            $resp = '{"balance":' . number_format($d_bonus->getBalanceCents($wallet) / 100, $wallet->getDigits(), '.', '') . ',"currency":"' . $currency_name . '"}';

            Log::channel('qt')->info($resp);
            return response($resp);
        }
        Redis::del('bonus-status-' . $user->id);

        Log::channel('qt')->info('balance:');
        $resp = '{"balance":' . number_format($wallet->getBalance(), $wallet->getDigits(), '.', '') . ',"currency":"' . $currency_name . '"}';

        Log::channel('qt')->info($resp);
        return response($resp);
    }

    /**
     * player's balance
     * @param $user_id
     * @param Request $request
     */
    public function getBalance($user_id, Request $request)
    {
//        $red_req = substr($request->getRequestUri(), 4);
//        Log::channel('qt')->info(var_export('http://127.0.0.1:23100'.$red_req, true));
        Log::channel('qt')->info('verifySession request:' . var_export($request->all(), true));
//        Log::channel('qt')->info('headers:' . var_export($request->headers->all(), true));
        Log::channel('qt')->info('Pass-Key header:' . var_export($request->header('Pass-Key'), true));

        Measure::logNow('qt request start');
        $headers = [
            'Pass-Key' => $request->header('Pass-Key'),
            'Wallet-Session' => $request->header('Wallet-Session'),
        ];
        $data = $request->all();
        $resp = Http::withHeaders($headers)->get("http://127.0.0.1:23100/qt/accounts/$user_id/balance?gameId=".$request->get("gameId"));
//        Log::channel('qt')->info('resp:' . var_export($resp, true));
        Log::channel('qt')->info('resp:' . var_export($resp->body(), true));
        Measure::logNow('qt request end');
        return $resp;


        if ($request->header('Pass-Key') !== self::KEY) {
            $resp = json_encode(['code' => 'LOGIN_FAILED', 'message' => 'The given pass-key is incorrect']);
            Log::channel('qt')->error($resp);
            return response($resp, 401);
        }

        if (preg_match('/^(bonus)?([0-9]+)([a-zA-z]{3,5})?$/', $user_id, $match)) {
            $is_bonus_game = !empty($match[1]);
            $user_id = $match[2];
            $currency_name = $match[3] ?? '';
        }
        $user = MUser::find($user_id);
        if ($user === null) {
            $resp = json_encode(['code' => 'INVALID_TOKEN', 'message' => 'User not found']);
            Log::channel('qt')->error($resp);
            return response($resp, 400);
        }

//        $wallet = QtWallet::getBySession($user, $request->header('Wallet-Session'));
        $wallet = QtWallet::getForUser($user, UserWallet::getCurrencyCode($currency_name));
        if ($wallet === null) {
            $resp = json_encode(['code' => 'INVALID_TOKEN', 'message' => 'The given wallet session is not found or has expired.']);
            Log::channel('qt')->error($resp);
            return response($resp, 400);
        }

        $currency_name = $wallet->getCurrencyName();
        $d_bonus = null;

        if ($user->balance_mode == MUser::BALANCE_MODE_BONUS) {
            /** @var UserBonus $d_bonus */
            $d_bonus = UserBonus::find($wallet->bonus_id);
            if ($d_bonus !== null) $d_bonus = UserBonus::convertByType($d_bonus);
        }

        if ($currency_name === 'USDT') $currency_name = 'USD';

        if ($user->balance_mode == MUser::BALANCE_MODE_TOURNAMENT) {
            /** @var TournamentGame $trg */
            $trg = TournamentGame::findFirstActiveGame($user->id);
            if ($trg) {
                $d_bonus = $trg->castByType($trg);
                $currency_name = 'USD';
            }
        }

        if ($d_bonus !== null) {
            $d_bonus->refreshBonusStatus($wallet, 'qt', $request['gameId']??'');
            Log::channel('qt')->info('bonus balance');

            $resp = '{"balance":' . number_format($d_bonus->getBalanceCents($wallet) / 100, $wallet->getDigits(), '.', '') . ',"currency":"' . $currency_name . '"}';
            Log::channel('qt')->info($resp);
            return response($resp);
        }
        Redis::del('bonus-status-' . $user->id);

        Log::channel('qt')->info('balance:');
        $resp = '{"balance":' . number_format($wallet->getBalance(), $wallet->getDigits(), '.', '') . ',"currency":"' . $currency_name . '"}';
        Log::channel('qt')->info($resp);
        return response($resp);
    }

    /**
     * transaction methods (calls related method deposit, withdraw)
     * @param Request $request
     */
    public function transaction(Request $request)
    {
        $red_req = substr($request->getRequestUri(), 4);
        Log::channel('qt')->info(var_export('http://127.0.0.1:23100'.$red_req, true));

        Measure::logNow('qt request start');
        $headers = [
            'Pass-Key' => $request->header('Pass-Key'),
            'Wallet-Session' => $request->header('Wallet-Session'),
        ];
        $data = $request->all();
        $resp = Http::asForm()->withHeaders($headers)->post('http://127.0.0.1:23100/qt/transactions', $data);
//        Log::channel('qt')->info('resp:' . var_export($resp, true));
        Log::channel('qt')->info('resp:' . var_export($resp->body(), true));
        Measure::logNow('qt request end');
        return $resp;

//        Log::channel('qt')->info('transactions request:' . var_export($request->all(), true));
        if ($request->header('Pass-Key') !== self::KEY) {
            return response(json_encode(['code' => 'LOGIN_FAILED', 'message' => 'The given pass-key is incorrect']), 401);
        }
        if ($request->input('txnType') === 'DEBIT') return $this->withdrawal($request);
        if ($request->input('txnType') === 'CREDIT') return $this->deposit($request);

        Log::channel('qt')->info('unknown error');
        return response(json_encode(['code' => 'UNKNOWN_ERROR', 'message' => 'Wallet not found.']), 500);
    }

    /**
     * bet
     * @param Request $request
     */
    public function withdrawal(Request $request)
    {
        Log::channel('qt')->info('bet request:' . var_export($request->all(), true));

        $data = $request->all();
        $currency_name = $data['currency'];
        $user_id = $request['playerId'];

        if (preg_match('/^(bonus)?([0-9]+)([a-zA-z]{3,5})?$/', $user_id, $match)) {
            $is_bonus_game = !empty($match[1]);
            $player_id = $match[2];
            $currency_name = $match[3] ?? '';
        }


        DB::beginTransaction();
        try {

            /** @var QtWallet $wallet */
            $wallet = QtWallet::where('session_id', $request->header('Wallet-Session'))->LockForUpdate()->first();
            if ($wallet === null) {
                DB::commit();
                return response(json_encode(['code' => 'INVALID_TOKEN', 'message' => 'The given wallet session token has expired.']), 400);
            }

//        $api = new QtGamesProviderApi();

            $player_id = $wallet->user_id;
            $gameId = $request['gameId'];
            /** @var MUser $user */
            $user = MUser::find($player_id);

            $currency = !empty($currency_name) ? UserWallet::getCurrencyCode($currency_name) : $user->currency;

            $game_data = new GameData($user);
            $game_data->getDataFormGame('qt', $gameId, $currency);

            /** @var TransactionStorageInterface $transactions */
            $transactions = new RedisTransactionStorage($user->id, 'qt-bet', 200);

            $trans = $transactions->getByTransactionId($request['txnId']);
            if (!empty($trans)) {
                $resp = $trans['resp'];
                Log::channel('qt')->info('repeat response: ' . var_export($resp, true));
                DB::commit();
                return response($resp);
            }

            if (!empty($request['bonusType']) && $request['bonusType'] === 'FREE_ROUND') {
                /** @var UserBonus $q_bonus */
                $q_bonus = null;
                if (!empty($request['bonusPromoCode'])) {
                    $q_bonus = UserBonus::find($request['bonusPromoCode']);
                    if ($q_bonus !== null) $q_bonus = UserBonus::convertByType($q_bonus);
                }
                if ($q_bonus) {
                    $balance = $q_bonus->externFreeSpinBet($wallet, GamesProvider::GAME_SYSTEM_QT, $gameId, $request['amount']?? 0);

                    $transaction = [
                        'user_id' => $user->id,
                        'bonus' => $q_bonus->getId(),
                        'action' => 'bet',
                        'refunded' => false,
                        'amount' => $request['bonusBetAmount']?? $request['amount'],
                        'currency' => $currency_name,
                        'game_id' => $request['gameId'],
                        'session_id' => $request->header('Wallet-Session'),
                        'transaction_id' => $request['txnId'],
                        'type' => $request['roundId'],
                        'system' => GamesProvider::GAME_SYSTEM_QT,
                        'date' => now()->format('Y-m-d H:i:s'),
                    ];

                    $s_trans = $transactions->addGenId($transaction);
                    $transactions->save();
                    $resp = '{"balance":' . number_format($balance, $wallet->getDigits(), '.', '') . ',"referenceId":"' . $s_trans['id'] . '"}';
                    $s_trans['resp'] = $resp;
                    $transactions->update(['id' => $s_trans['id']], $s_trans);

                    Redis::set($user->id.':last_bet_value', $request['bonusBetAmount']?? $request['amount']);
                    Redis::set($user->id.':last_bet_id', $s_trans['id']);

                    $transaction['gen_id'] = $s_trans['id'];

                    DeferredModelWriter::getForModel(GameTransaction::class)->store($transaction);
                    Log::channel('qt')->info('response: ' . var_export($resp, true));
                    DB::commit();
                    return response($resp);
                }
            }

            $d_bonus = null;

            if ($user->balance_mode == MUser::BALANCE_MODE_BONUS && $game_data->bonus_id) {
                /** @var UserBonus $d_bonus */
                $bonus = UserBonus::find($game_data->bonus_id);
                if ($bonus) $d_bonus = UserBonus::convertByType($bonus);
                if (!$d_bonus->isActive()) {
                    $d_bonus = null;
                    $game_data->bonus_id = 0;
                    $game_data->save();
                }
            }

            if ($user->balance_mode == MUser::BALANCE_MODE_TOURNAMENT && $game_data->tnt_game_id) {
                /** @var TournamentGame $t_game */
                $t_game = TournamentGame::find($game_data->tnt_game_id);
                if ($t_game) {
                    $d_bonus = $t_game->castByType($t_game);
                    if (!$d_bonus->isActive()) {
                        $d_bonus = null;
                        $game_data->bonus_id = 0;
                        $game_data->save();
                    }
                }
            }

            if ($d_bonus && $d_bonus->isVegasAllowed() && $wallet) {
                Log::channel('qt')->info('bonus_mode');

                //set bonus id to prevent restoring prize games after bonus is complete
                $wallet->bonus_id = $d_bonus->id;
                $wallet->save();

                if (($d_bonus->getBalanceCents($wallet) / 100) < $request['amount']) {
                    $resp = [
                        "code" => "INSUFFICIENT_FUNDS",
                        "message" => "Not enough funds for the debit operation",
                    ];
                    Log::channel('qt')->info('response: ' . var_export($resp, true));
                    DB::commit();
                    return response(json_encode($resp), 400);
                }

                if ($d_bonus->withdrawCents($request['amount'] * 100, $wallet, 'qt', $gameId) === false) {
                    $resp = [
                        "code" => "INSUFFICIENT_FUNDS",
                        "message" => "Not enough funds for the debit operation",
                    ];
                    Log::channel('qt')->info('response: ' . var_export($resp, true));
                    DB::commit();
                    return response(json_encode($resp), 400);
                }

                $d_bonus->actualizeVegas($gameId, $wallet, 'qt');
                $d_bonus->save();

                $status = $d_bonus->getStatusString($wallet);
                if (empty($status)) {
                    Redis::del('bonus-status-' . $user->id);
                } else {
                    Redis::set('bonus-status-' . $user->id, $status);
                }

                $transaction = [
                    'user_id' => $user->id,
                    'bonus' => $d_bonus->id,
                    'action' => 'bet',
                    'refunded' => false,
                    'amount' => $request['amount'],
                    'currency' => $currency_name,
                    'game_id' => $request['gameId'],
                    'session_id' => $request->header('Wallet-Session'),
                    'transaction_id' => $request['txnId'],
                    'type' => $request['roundId'],
                    'system' => 'qt',
                    'date' => now()->format('Y-m-d H:i:s'),
                ];

                $s_trans = $transactions->addGenId($transaction);
                $transactions->save();
//                $resp = [
//                    'balance' => (float)number_format($d_bonus->getBalanceCents($wallet) / 100, $wallet->getDigits(), '.', ''),
//                    'referenceId' => $s_trans['id'],
//                ];
                $resp = '{"balance":' . number_format($d_bonus->getBalanceCents($wallet) / 100, $wallet->getDigits(), '.', '') . ',"referenceId":"' . $s_trans['id'] . '"}';
//                $s_trans['resp'] = json_encode($resp);
                $s_trans['resp'] = $resp;
                $transactions->update(['id' => $s_trans['id']], $s_trans);

                Redis::set($user->id.':last_bet_value', $request['amount']);
                Redis::set($user->id.':last_bet_id', $s_trans['id']);

                $transaction['gen_id'] = $s_trans['id'];

                DeferredModelWriter::getForModel(GameTransaction::class)->store($transaction);
                Log::channel('qt')->info('response: ' . var_export($resp, true));
                DB::commit();
//                return response(json_encode($resp));
                return response($resp);
            }

            if ($wallet) {
                Log::channel('qt')->info('normal mode');
                Redis::del('bonus-status-' . $user->id);
                if ($wallet->bonus_id != 0) {
                    $wallet->bonus_id = 0;
                    $wallet->save();
                }

                if ($request['amount'] < 0) {
                    $resp = [
                        "code" => "REQUEST_DECLINED",
                        "message" => "amount < 0",
                    ];
                    Log::channel('qt')->info('response: ' . var_export($resp, true));
                    DB::commit();
                    return response(json_encode($resp), 400);
                }

                if ($wallet->getBalance() < $request['amount']) {
                    $resp = [
                        "code" => "INSUFFICIENT_FUNDS",
                        "message" => "Not enough funds for the debit operation",
                    ];
                    Log::channel('qt')->info('response: ' . var_export($resp, true));
                    DB::commit();
                    return response(json_encode($resp), 400);
                }

                $wallet->remMoney($request['amount']);

                if ($game_data->non_dep_bonus_id) {
                    /** @var NonDepositBonus $nd_bonus */
                    $nd_bonus = NonDepositBonus::getDone($player_id);
                    if ($nd_bonus) {
                        $nd_bonus->onUserBet($request['amount'], $gameId);
                    } else {
                        $game_data->non_dep_bonus_id = 0;
                    }
                }

                if ($game_data->filthy_bonus_id) {
                    /** @var FilthyFsWashOffBonus $nd_bonus */
                    $nd_bonus = FilthyFsWashOffBonus::getDone($player_id);
                    if ($nd_bonus) {
                        $nd_bonus->onUserBet($request['amount'], $gameId);
                    } else {
                        $game_data->filthy_bonus_id = 0;
                    }
                }

                /** @var TournamentGame $tnt_res_game */
                $tnt_res_game = null;
                if ($game_data->resident_tnt_game_id) {
                    /** @var TournamentGame $tnt_game */
                    $tnt_game = TournamentGame::find($game_data->resident_tnt_game_id);
                    if ($tnt_game) $tnt_res_game = $tnt_game->castByType($tnt_game);
                    if ($tnt_res_game && !$tnt_res_game->isResident()) {
                        $tnt_game = null;
                        $game_data->resident_tnt_game_id = 0;
                    }
                }

                if ($tnt_res_game) {
                    $tnt_res_game->onBet($request['amount'] * 100, $wallet, "qt", $gameId);
                    $tnt_res_game->save();
                }

                if ($user) {
                    if ($game_data->locked_money > 0 && $user->getWallet($currency)->getLockedMoney() > 0) {
                        $user->getWallet($currency)->remLockedMoney($request['amount'] * ($game_data->wager_prc / 100));
                    } else {
                        $game_data->locked_money = 0;
                        $game_data->save();
                    }
                    $user->save();
                    $user->getWallet($currency)->addBet($request['amount']);
                }
                $wallet->save();

                $transaction = [
                    'user_id' => $user->id,
                    'bonus' => null,
                    'action' => 'bet',
                    'refunded' => false,
                    'amount' => $request['amount'],
                    'currency' => $currency_name,
                    'game_id' => $request['gameId'],
                    'session_id' => $request->header('Wallet-Session'),
                    'transaction_id' => $request['txnId'],
                    'type' => $request['roundId'],
                    'system' => 'qt',
                    'date' => now()->format('Y-m-d H:i:s'),
                ];

                $s_trans = $transactions->addGenId($transaction);
                $transactions->save();
//                $resp = [
//                    'balance' => (float)number_format($wallet->getBalance(), $wallet->getDigits(), '.', ''),
//                    'referenceId' => $s_trans['id'],
//                ];
                $resp = '{"balance":' . number_format($wallet->getBalance(), $wallet->getDigits(), '.', '') . ',"referenceId":"' . $s_trans['id'] . '"}';
//                $s_trans['resp'] = json_encode($resp);
                $s_trans['resp'] = $resp;
                $transactions->update(['id' => $s_trans['id']], $s_trans);

                Redis::set($user->id.':last_bet_value', $request['amount']);
                Redis::set($user->id.':last_bet_id', $s_trans['id']);

                $transaction['gen_id'] = $s_trans['id'];

                DeferredModelWriter::getForModel(GameTransaction::class)->store($transaction);
                Log::channel('qt')->info('response: ' . var_export($resp, true));
                DB::commit();
//                return response(json_encode($resp));
                return response($resp);
            }

        } catch (\Exception $e) {
            DB::rollBack();
            Log::channel('qt')->error($e->getMessage());
            Log::channel('qt')->error($e->getFile());
            Log::channel('qt')->error($e->getLine());
            Log::channel('qt')->error($e->getTraceAsString());
        }

        DB::commit();

        Log::channel('qt')->info('unknown error');
        return response(json_encode(['code' => 'UNKNOWN_ERROR', 'message' => 'Wallet not found.']), 500);
    }

    /**
     * win
     * @param Request $request
     */
    public function deposit(Request $request)
    {
        Log::channel('qt')->info('win request:' . var_export($request->all(), true));

        $data = $request->all();
        $currency_name = $data['currency'];
        $user_id = $request['playerId'];
        $player_id = $request['playerId'];
        $gameId = $request['gameId'];

        if (preg_match('/^(bonus)?([0-9]+)([a-zA-z]{3,5})?$/', $user_id, $match)) {
            $is_bonus_game = !empty($match[1]);
            $player_id = $match[2];
            $currency_name = $match[3] ?? '';
        }

        /** @var MUser $user */
        $user = MUser::find($player_id);

        DB::beginTransaction();
        try {
            /** @var QtWallet $wallet */
            $wallet = QtWallet::where('session_id', $request->header('Wallet-Session'))->LockForUpdate()->first();
            if ($wallet === null) {
                $wallet = QtWallet::getForUser($user, UserWallet::getCurrencyCode($currency_name));
//            DB::commit();
//            return response(json_encode(['code' => 'INVALID_TOKEN', 'message' => 'The given wallet session token has expired.']), 400);
            }

//        $api = new QtGamesProviderApi();


            $currency = !empty($currency_name) ? UserWallet::getCurrencyCode($currency_name) : $user->currency;

            $game_data = new GameData($user);
            $game_data->getDataFormGame('qt', $gameId, $currency);

            /** @var TransactionStorageInterface $transactions */
            $transactions = new RedisTransactionStorage($user->id, 'qt-win', 200);

            $trans = $transactions->getByTransactionId($request['txnId']);
            if (!empty($trans)) {
                $resp = $trans['resp'];
                Log::channel('qt')->info('repeat response: ' . var_export($resp, true));
                DB::commit();
                return response($resp);
            }

            if (!empty($request['bonusType']) && $request['bonusType'] === 'FREE_ROUND') {
                /** @var UserBonus $q_bonus */
                $q_bonus = null;
                if (!empty($request['bonusPromoCode'])) {
                    $q_bonus = UserBonus::find($request['bonusPromoCode']);
                    if ($q_bonus !== null) $q_bonus = UserBonus::convertByType($q_bonus);
                }
                if ($q_bonus) {
                    $balance = $q_bonus->externFreeSpinWin($wallet, GamesProvider::GAME_SYSTEM_QT, $gameId, $request['amount']?? 0);

                    $transaction = [
                        'user_id' => $user->id,
                        'bonus' => $q_bonus->getId(),
                        'action' => 'win',
                        'refunded' => false,
                        'amount' => $request['amount'],
                        'currency' => $currency_name,
                        'game_id' => $request['gameId'],
                        'session_id' => $request->header('Wallet-Session'),
                        'transaction_id' => $request['txnId'],
                        'type' => $request['roundId'],
                        'system' => GamesProvider::GAME_SYSTEM_QT,
                        'date' => now()->format('Y-m-d H:i:s'),
                        'last_bet_value' => Redis::get($user->id.':last_bet_value', 0),
                        'last_bet_id' => Redis::get($user->id.':last_bet_id'),
                    ];

                    $s_trans = $transactions->addGenId($transaction);
                    $transactions->save();
                    $resp = '{"balance":' . number_format($balance, $wallet->getDigits(), '.', '') . ',"referenceId":"' . $s_trans['id'] . '"}';
                    $s_trans['resp'] = $resp;
                    $transactions->update(['id' => $s_trans['id']], $s_trans);

                    $transaction['gen_id'] = $s_trans['id'];

                    DeferredModelWriter::getForModel(GameTransaction::class)->store($transaction);
                    Log::channel('qt')->info('response: ' . var_export($resp, true));
                    DB::commit();
                    return response($resp);
                }
            }

            $d_bonus = null;

            if ($user->balance_mode == MUser::BALANCE_MODE_BONUS && $game_data->bonus_id) {
                /** @var UserBonus $d_bonus */
                $bonus = UserBonus::find($game_data->bonus_id);
                if ($bonus) $d_bonus = UserBonus::convertByType($bonus);
                if (!$d_bonus->isActive()) {
                    $d_bonus = null;
                    $game_data->bonus_id = 0;
                    $game_data->save();
                }
            }

            if ($d_bonus && $d_bonus->isVegasAllowed() && $wallet) {

                $d_bonus->depositCents($request['amount'] * 100, $wallet, "qt", $gameId);

                //add to wins table
                if ($user && $request['amount'] > 0) {
                    CurrentWin::addWin($user, '', $request['amount'], $currency_name, $gameId, 'qt');
                }

                $d_bonus->actualizeVegas($gameId, $wallet, 'qt');
                $d_bonus->save();

                $status = $d_bonus->getStatusString($wallet);
                if (empty($status)) {
                    Redis::del('bonus-status-' . $user->id);
                } else {
                    Redis::set('bonus-status-' . $user->id, $status);
                }

                $transaction = [
                    'user_id' => $user->id,
                    'bonus' => $d_bonus->id,
                    'action' => 'win',
                    'refunded' => false,
                    'amount' => $request['amount'],
                    'currency' => $currency_name,
                    'game_id' => $request['gameId'],
                    'session_id' => $request->header('Wallet-Session'),
                    'transaction_id' => $request['txnId'],
                    'type' => $request['roundId'],
                    'system' => 'qt',
                    'date' => now()->format('Y-m-d H:i:s'),
                    'last_bet_value' => Redis::get($user->id.':last_bet_value', 0),
                    'last_bet_id' => Redis::get($user->id.':last_bet_id'),
                ];

                $s_trans = $transactions->addGenId($transaction);
                $transactions->save();
//                $resp = [
//                    'balance' => (float)number_format($d_bonus->getBalanceCents($wallet) / 100, $wallet->getDigits(), '.', ''),
//                    'referenceId' => $s_trans['id'],
//                ];
                $resp = '{"balance":' . number_format($d_bonus->getBalanceCents($wallet) / 100, $wallet->getDigits(), '.', '') . ',"referenceId":"' . $s_trans['id'] . '"}';

//                $s_trans['resp'] = json_encode($resp);
                $s_trans['resp'] = $resp;
                $transactions->update(['id' => $s_trans['id']], $s_trans);

                $transaction['gen_id'] = $s_trans['id'];

                DeferredModelWriter::getForModel(GameTransaction::class)->store($transaction);
                Log::channel('qt')->info('response: ' . var_export($resp, true));
                DB::commit();
//                return response(json_encode($resp));
                return response($resp);
            }

            if ($wallet) {
                Redis::del('bonus-status-' . $user->id);

                if ($request['amount'] < 0) {
                    $resp = [
                        "code" => "REQUEST_DECLINED",
                        "message" => "amount < 0",
                    ];
                    Log::channel('qt')->info('r1esponse: ' . var_export($resp, true));
                    DB::commit();
                    return response(json_encode($resp, 400));
                }

                if ($wallet->bonus_id == 0) {
                    $wallet->addMoney($request['amount']);
                    $wallet->save();
                    //add to wins table
                    if ($user && $request['amount'] > 0) {
//                        $user->TotalWin += $request['amount'];
//                        $user->save();
                        $user->getWallet($currency)->addWin($request['amount']);

                        //add to wins table
                        if ($user && $request['amount'] > 0) {
                            CurrentWin::addWin($user, '', $request['amount'], $currency_name, $gameId, 'qt');
                        }
                    }
                }
                $wallet->save();
                $wallet->refresh();

                $transaction = [
                    'user_id' => $user->id,
                    'bonus' => null,
                    'action' => 'win',
                    'refunded' => false,
                    'amount' => $request['amount'],
                    'currency' => $currency_name,
                    'game_id' => $request['gameId'],
                    'session_id' => $request->header('Wallet-Session'),
                    'transaction_id' => $request['txnId'],
                    'type' => $request['roundId'],
                    'system' => 'qt',
                    'date' => now()->format('Y-m-d H:i:s'),
                    'last_bet_value' => Redis::get($user->id.':last_bet_value', 0),
                    'last_bet_id' => Redis::get($user->id.':last_bet_id'),
                ];

                $s_trans = $transactions->addGenId($transaction);
                $transactions->save();
//                $resp = [
//                    'balance' => (float)number_format($wallet->getBalance(), $wallet->getDigits(), '.', ''),
//                    'referenceId' => $s_trans['id'],
//                ];
                $resp = '{"balance":' . number_format($wallet->getBalance(), $wallet->getDigits(), '.', '') . ',"referenceId":"' . $s_trans['id'] . '"}';

//                $s_trans['resp'] = json_encode($resp);
                $s_trans['resp'] = $resp;
                $transactions->update(['id' => $s_trans['id']], $s_trans);

                $transaction['gen_id'] = $s_trans['id'];

                DeferredModelWriter::getForModel(GameTransaction::class)->store($transaction);
                Log::channel('qt')->info('response: ' . var_export($resp, true));
                DB::commit();
//                return response(json_encode($resp));
                return response($resp);
            }

        } catch (\Exception $e) {
            DB::rollBack();
            Log::channel('qt')->error($e->getMessage());
            Log::channel('qt')->error($e->getFile());
            Log::channel('qt')->error($e->getLine());
            Log::channel('qt')->error($e->getTraceAsString());
        }

        DB::commit();

        Log::channel('qt')->info('unknown error');
        return response(json_encode(['code' => 'UNKNOWN_ERROR', 'message' => 'Wallet not found.']), 500);
    }

    /**
     * refund
     * @param Request $request
     */
    public function rollback(Request $request)
    {
        $red_req = substr($request->getRequestUri(), 4);
        Log::channel('qt')->info(var_export('http://127.0.0.1:23100'.$red_req, true));

        Measure::logNow('qt request start');
        $headers = $request->headers->all();
        $data = $request->all();
        $resp = Http::asForm()->withHeaders($headers)->post('http://127.0.0.1:23100'.$red_req, $data);
        Measure::logNow('qt request end');
        return $resp;

        if ($request->header('Pass-Key') !== self::KEY) {
            return response(json_encode(['code' => 'LOGIN_FAILED', 'message' => 'The given pass-key is incorrect']), 401);
        }

        Log::channel('qt')->info('refund request:' . var_export($request->all(), true));

        $data = $request->all();
        $currency_name = $data['currency'];
        $player_id = $data['playerId'];

        if (preg_match('/^(bonus)?([0-9]+)([a-zA-z]{3,5})?$/', $player_id, $match)) {
            $is_bonus_game = !empty($match[1]);
            $player_id = $match[2];
            $currency_name = $match[3] ?? '';
        }
        /** @var MUser $user */
        $user = MUser::find($player_id);

        /** @var QtWallet $wallet */
        $wallet = QtWallet::where('session_id', $request->header('Wallet-Session', ''))->LockForUpdate()->first();
        if ($wallet === null) {
            $wallet = QtWallet::getForUser($user, UserWallet::getCurrencyCode($currency_name));
//            DB::commit();
//            return response(json_encode(['code' => 'INVALID_TOKEN', 'message' => 'The given wallet session token has expired.']), 400);
        }

//        $api = new QtGamesProviderApi();

//        $player_id = $wallet->user_id;
        $gameId = $request['gameId'];

        $currency = !empty($currency_name) ? UserWallet::getCurrencyCode($currency_name) : $user->currency;

        $game_data = new GameData($user);
        $game_data->getDataFormGame('qt', $gameId, $currency);

        /** @var TransactionStorageInterface $transactions */
        $transactions = new RedisTransactionStorage($user->id, 'qt-refund', 200);

        $trans = $transactions->getByTransactionId($request['txnId']);
        if (!empty($trans)) {
            $resp = $trans['resp'];
            Log::channel('qt')->info('repeat response: ' . var_export($resp, true));
            DB::commit();
            return response($resp);
        }

        DeferredModelWriter::getForModel(GameTransaction::class)->writeToDb();
//        $bet_transaction = new RedisTransactionStorage($user->id, 'qt-bet', 100);
//        $b_trans = $bet_transaction->getByTransactionId($request['betId']);
//        if (!empty($b_trans) && $b_trans['bonus'] === null && $b_trans['refunded'] === false) {
//            $b_trans['refunded'] = true;
//            $wallet->addMoney($b_trans['amount']);
//            $wallet->save();
//        }
        /** @var GameTransaction $tr */
        $tr = GameTransaction::where('transaction_id', $request['betId'])
            ->where('system', 'qt')
            ->where('action', 'bet')
            ->where('refunded', 'false')
            ->whereNull('bonus')->first();
        if ($tr) {
            $tr->refunded = true;
            $tr->save();
            $wallet->addMoney($tr->amount);
            $wallet->save();
        }

        $transaction = [
            'user_id' => $user->id,
            'bonus' => null,
            'action' => 'refund',
            'refunded' => false,
            'amount' => $request['amount'],
            'currency' => $currency_name,
            'game_id' => $request['gameId'],
            'session_id' => $request->header('Wallet-Session'),
            'transaction_id' => $request['txnId'],
            'type' => $request['roundId'],
            'system' => 'qt',
            'date' => now()->format('Y-m-d H:i:s'),
        ];

        $s_trans = $transactions->addGenId($transaction);
        $transactions->save();
//        $resp = [
//            'balance' => (float)number_format($wallet->getBalance(), $wallet->getDigits(), '.', ''),
//            'referenceId' => $s_trans['id'],
//        ];
        $resp = '{"balance":' . number_format($wallet->getBalance(), $wallet->getDigits(), '.', '') . ',"referenceId":"' . $s_trans['id'] . '"}';

//        $s_trans['resp'] = json_encode($resp);
        $s_trans['resp'] = $resp;
        $transactions->update(['id' => $s_trans['id']], $s_trans);

        DeferredModelWriter::getForModel(GameTransaction::class)->store($transaction);
        Log::channel('qt')->info('refund response: ' . var_export($resp, true));
        DB::commit();
//        return response(json_encode($resp));
        return response($resp);
    }

    /**
     * free rounds status
     * @param Request $request
     */
    public function promotionStatus(Request $request)
    {
        $red_req = substr($request->getRequestUri(), 4);
        Log::channel('qt')->info(var_export('http://127.0.0.1:23100'.$red_req, true));

        Measure::logNow('qt request start');
        $headers = $request->headers->all();
        $data = $request->all();
        $resp = Http::asForm()->withHeaders($headers)->post('http://127.0.0.1:23100'.$red_req, $data);
        Measure::logNow('qt request end');
        return $resp;


        if ($request->header('Pass-Key') !== self::KEY) {
            return response(json_encode(['code' => 'LOGIN_FAILED', 'message' => 'The given pass-key is incorrect']), 401);
        }

        Log::channel('qt')->info('promotionStatus request:' . var_export($request->all(), true));

        $data = $request->all();
        $currency_name = $data['currency'];
        $user_id = $data['playerId'];
        if (preg_match('/^(bonus)?([0-9]+)([a-zA-z]{3,5})?$/', $data['playerId'], $match)) {
            $is_bonus_game = !empty($match[1]);
            $user_id = $match[2];
            $currency_name = $match[3] ?? '';
        }
        /** @var MUser $user */
        $user = MUser::find($user_id);
        $gameId = $data['claimedGameId']?? 'bonus';

        /** @var UserBonus $q_bonus */
        $q_bonus = null;
        if (!empty($request['promoCode'])) {
            $q_bonus = UserBonus::find($request['promoCode']);
            if ($q_bonus !== null) $q_bonus = UserBonus::convertByType($q_bonus);
        }
        if ($q_bonus && $data['status'] === 'COMPLETED') {
            $wallet = QtWallet::getForUser($user);
            $balance = $q_bonus->externFreeSpinsCompleted($wallet, GamesProvider::GAME_SYSTEM_QT, $gameId, $request['totalPayout']?? 0);
        }
        if ($q_bonus && in_array($data['status'], ['DELETED', 'CANCELLED', 'FAILED', 'EXPIRED'])) {
            $q_bonus->cancelBonus();
        }

        return '';
    }

    /**
     * other wins (tournaments, contests, etc)
     * @param Request $request
     */
    public function rewards(Request $request)
    {
        $red_req = substr($request->getRequestUri(), 4);
        Log::channel('qt')->info(var_export('http://127.0.0.1:23100'.$red_req, true));

        Measure::logNow('qt request start');
        $headers = $request->headers->all();
        $data = $request->all();
        $resp = Http::asForm()->withHeaders($headers)->post('http://127.0.0.1:23100'.$red_req, $data);
        Measure::logNow('qt request end');
        return $resp;

        Log::channel('qt')->info('reward request:' . var_export($request->all(), true));
        if ($request->header('Pass-Key') !== self::KEY) {
            return response(json_encode(['code' => 'LOGIN_FAILED', 'message' => 'The given pass-key is incorrect']), 401);
        }
        if (empty($request->input('rewardType'))) {
            return response(json_encode(['code' => 'REQUEST_DECLINED', 'message' => 'rewardType missing']), 400);
        }

        $data = $request->all();
        $currency_name = $data['currency'];
        $user_id = $data['playerId'];
        if (preg_match('/^(bonus)?([0-9]+)([a-zA-z]{3,5})?$/', $data['playerId'], $match)) {
            $is_bonus_game = !empty($match[1]);
            $user_id = $match[2];
            $currency_name = $match[3] ?? '';
        }
        /** @var MUser $user */
        $user = MUser::find($user_id);

        if ($user === null) {
            $resp = json_encode(['code' => 'INVALID_TOKEN', 'message' => 'User not found']);
            Log::channel('qt')->error($resp);
            return response($resp, 400);
        }

        /** @var TransactionStorageInterface $transactions */
        $transactions = new RedisTransactionStorage($user->id, 'qt-reward', 100);
        $trans = $transactions->getByTransactionId($request['txnId']);
        if (!empty($trans)) {
            $resp = $trans['resp'];
            Log::channel('qt')->info('repeat response: ' . var_export($resp, true));
            DB::commit();
            return response($resp);
        }

        try {
            DB::beginTransaction();
            $wallet = QtWallet::getForUser($user, UserWallet::getCurrencyCode($currency_name));
            $wallet->addMoney($data['amount'])->save();

            $transaction = [
                'user_id' => $user->id,
                'bonus' => null,
                'action' => 'reward',
                'refunded' => false,
                'amount' => $request['amount'],
                'currency' => $currency_name,
                'game_id' => $request['rewardType'],
                'session_id' => $request['rewardTitle'],
                'transaction_id' => $request['txnId'],
                'type' => $request['roundId'],
                'system' => 'qt',
                'date' => now()->format('Y-m-d H:i:s'),
            ];

            $s_trans = $transactions->addGenId($transaction);
            $transactions->save();
//            $resp = [
//                'balance' => (float)number_format($wallet->getBalance(), $wallet->getDigits(), '.', ''),
//                'referenceId' => $s_trans['id'],
//            ];
            $resp = '{"balance":' . number_format($wallet->getBalance(), $wallet->getDigits(), '.', '') . ',"referenceId":"' . $s_trans['id'] . '"}';

//            $s_trans['resp'] = json_encode($resp);
            $s_trans['resp'] = $resp;
            $transactions->update(['id' => $s_trans['id']], $s_trans);

            DB::commit();
            DeferredModelWriter::getForModel(GameTransaction::class)->store($transaction);
            Log::channel('qt')->info('reward response: ' . var_export($resp, true));
//            return response(json_encode($resp));
            return response($resp);

        } catch (\Exception $e) {
            Log::channel('qt')->error($e->getMessage());
            Log::channel('qt')->error($e->getFile());
            Log::channel('qt')->error($e->getLine());
            Log::channel('qt')->error($e->getTraceAsString());
        }

        DB::commit();
        Log::channel('qt')->info('unknown error');
        return response(json_encode(['code' => 'UNKNOWN_ERROR', 'message' => 'Wallet not found.']), 500);
    }
}
