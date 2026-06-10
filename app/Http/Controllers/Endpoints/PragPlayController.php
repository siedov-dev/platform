<?php

namespace App\Http\Controllers\Endpoints;

use App\GamesProviders\GamesProvider;
use App\GamesProviders\PragmaticPlay\PragmaticPlay;
use App\Helpers\Measure;
use App\Http\Controllers\Controller;
use App\Models\Bonuses\FilthyFsWashOffBonus;
use App\Models\Bonuses\Foreign\PragPlayBonus;
use App\Models\Bonuses\NonDepositBonus;
use App\Models\Bonuses\UserBonus;
use App\Models\GameTransaction;
use App\Models\Tournaments\TournamentGames\TournamentGame;
use App\MUser;
use App\ProvidersApi\CurrentWin;
use App\ProvidersApi\GameData;
use App\ProvidersApi\PragmaticPlay\Models\PragWallet;
use App\ProvidersApi\Transaction\RedisTransactionStorage;
use App\Redis\DeferredModelWriter;
use App\Redis\Transaction\TransactionStorageInterface;
use App\UserWallet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;

/**
 * @deprecated
 */
class PragPlayController extends Controller
{
    //
    public function endpoint(Request $request, $method)
    {
        Log::channel('pragmatik')->info('request:' . var_export($request->all(), true));
        Measure::logNow('pragmatik request start');
        $headers = $request->headers->all();
        $data = $request->all();
        $resp = Http::asForm()->withHeaders($headers)->post('http://127.0.0.1:23100/pragplay/'.$method, $data);
        Measure::logNow('pragmatik request end');
        return $resp->body();

        return match (mb_strtolower($method)) {
            'authenticate.html' => $this->auth($request),
            'balance.html' => $this->balance($request),
            'bet.html' => $this->bet($request),
            'refund.html' => $this->refund($request),
            'result.html' => $this->result($request),
            'bonusWin.html' => $this->bonusWin($request),
            'jackpotWin.html' => $this->jackpotWin($request),
            'promoWin.html' => $this->promoWin($request),
            'endRound.html' => $this->endRound($request),
            'sessionExpired.html' => $this->sessionExpired($request),
            default => "unknown method",
        };

    }

    public function auth(Request $request)
    {
        Log::channel('pragmatik')->info('auth request:' . var_export($request->all(), true));

        $data = $request->all();
        $api = new PragmaticPlay();

        if (!$api->checkHash($data)) {
            Log::channel('pragmatik')->error('hash check fail');
            return;
        }

        $token = $request->get('token');
        if (!$token) {
            Log::channel('pragmatik')->error('no token in request');
            return;
        }

        Log::channel('pragmatik')->info('token:' . $token);

        $wallet = PragWallet::findByToken($token);
        if (!$wallet) {
            Log::channel('pragmatik')->error("no user with token $token found");
            return;
        }

        /** @var MUser $player */
        $player = MUser::find($wallet->user_id);
        if (!$player) {
            Log::channel('pragmatik')->error('no user #' . $wallet->user_id);
            return;
        }

        $currency_name = UserWallet::currencyName($wallet->currency);

        $player_id = '' . $player->id . $currency_name;

        $d_bonus = null;

        if ($player->balance_mode == MUser::BALANCE_MODE_BONUS) {
            /** @var UserBonus $d_bonus */
            $d_bonus = UserBonus::findFirstActiveBonus($player_id, $wallet->currency);
            $wallet->bonus = $d_bonus?->getId();
            $wallet->save();
        }

        if ($currency_name === 'USDT') $currency_name = 'USD';

        if ($player->balance_mode == MUser::BALANCE_MODE_TOURNAMENT) {
            /** @var TournamentGame $trg */
            $trg = TournamentGame::findFirstActiveGame($player->id);
            if ($trg) {
                $d_bonus = $trg->castByType($trg);
                $currency_name = 'USD';
            }
        }

        $response = [
            'userId' => $player_id,
            'currency' => $currency_name,
            'cash' => number_format(($d_bonus ? 0 : $wallet->amount), 4, '.', ''),
            'bonus' => number_format(($d_bonus ? $d_bonus->getBalanceCents($wallet) / 100 : 0), 4, '.', ''),
            'token' => $token,
        ];

        $jresp = $this->encode_resp($response);
        Log::channel('pragmatik')->info('response: ' . $jresp);
        return $jresp;
    }

    public function balance(Request $request)
    {
        Log::channel('pragmatik')->info('balance request:' . var_export($request->all(), true));

        $data = $request->all();
        $api = new PragmaticPlay();

        $resp = new \StdClass();
        $resp->currency = '';
        $resp->cash = 0;
        $resp->bonus = 0;
        $resp->error = 0;
        $resp->description = 'Success';

        if (!$api->checkHash($data)) {
            Log::channel('pragmatik')->error('hash check fail');
            $resp->error = 500;
            $resp->description = 'hash check fail';
            $jresp = $this->encode_resp($resp);
            Log::channel('pragmatik')->info('response: ' . $jresp);
            return $jresp;
        }

        $token = $request->get('token');

        $player_id = $data['userId'];
        $gameId = $request['gameId']??'';

        //if bonus game player id is bonus12345...
        $match = null;
        if (preg_match('/^(bonus)?([0-9]+)([a-zA-z]{3,5})?$/', $player_id, $match)) {
            $is_bonus_game = !empty($match[1]);
            $player_id = $match[2];
            $currency_name = $match[3]?? '';
        }

        /** @var MUser $user */
        $user = MUser::find($player_id);

        $currency = !empty($currency_name) ? UserWallet::getCurrencyCode($currency_name) : $user->currency;

        $resp->currency = $currency_name;
        $resp->bonus = 0;

        $d_bonus = null;
        $wallet = PragWallet::getForUser($user, $currency);

        if ($user->balance_mode == MUser::BALANCE_MODE_BONUS) {
            /** @var UserBonus $d_bonus */
            $d_bonus = UserBonus::findFirstActiveBonus($player_id, $currency);
        }

        if ($d_bonus) {
            $resp->bonus = $d_bonus->getBalanceCents($wallet) / 100;
            $jresp = $this->encode_resp($resp);
            $d_bonus->refreshBonusStatus($wallet, GamesProvider::GAME_SYSTEM_PRAGMATIC, $gameId);
            Log::channel('pragmatik')->info('response: ' . $jresp);
            return $jresp;
        }

        if ($user->balance_mode == MUser::BALANCE_MODE_TOURNAMENT) {
            /** @var TournamentGame $trg */
            $trg = TournamentGame::findFirstActiveGame($user->id);
            if ($trg) {
                $trg = $trg->castByType($trg);
                $resp->bonus = $trg->getBalanceCents() / 100;
                $resp->currency = 'USD';
                $jresp = $this->encode_resp($resp);
                Log::channel('pragmatik')->info('response: ' . $jresp);
                $trg->refreshBonusStatus($wallet, GamesProvider::GAME_SYSTEM_PRAGMATIC, $gameId);
                return $jresp;
            }
        }

        Redis::del('bonus-status-' . $user->id);

        if ($wallet) {
            $resp->cash = floatval($wallet->amount);
            $jresp = $this->encode_resp($resp);
            Log::channel('pragmatik')->info('response: ' . $jresp);
            return $jresp;
        }

        $resp->error = 2;
        $resp->description = 'no user found';
        $jresp = $this->encode_resp($resp);
        Log::channel('pragmatik')->info('response: ' . $jresp);
        return $jresp;
    }

    public function bet(Request $request)
    {
        Log::channel('pragmatik')->info('bet request:' . var_export($request->all(), true));

        $data = $request->all();
        $api = new PragmaticPlay();

        $resp = new \StdClass();
        $resp->currency = '';
        $resp->cash = 0;
        $resp->bonus = 0;
        $resp->error = 0;
        $resp->usedPromo = 0;
        $resp->description = 'Success';

        if (!$api->checkHash($data)) {
            Log::channel('pragmatik')->error('hash check fail');
            $resp->error = 100;
            $resp->description = "hash check fail";
            $jresp = $this->encode_resp($resp);
            Log::channel('pragmatik')->info('response: ' . $jresp);
            return $jresp;
        }


        $player_id = $request['userId'];
        $is_bonus_game = false;
        $currency_name = "";
        //parse player format [bonus],id,[currency]
        $match = null;
        if (preg_match('/^(bonus)?([0-9]+)([a-zA-z]{3,5})?$/', $player_id, $match)) {
            $is_bonus_game = !empty($match[1]);
            $player_id = $match[2];
            $currency_name = $match[3]?? '';
        }
        $gameId = $request['gameId'];
        /** @var MUser $user */
        $user = MUser::find($player_id);

//        $currency_name = $request['currency'];
        $currency = !empty($currency_name) ? UserWallet::getCurrencyCode($currency_name) : $user->currency;
        $resp->currency = $currency_name;

        $game_data = new GameData($user);
        $game_data->getDataFormGame(GamesProvider::GAME_SYSTEM_PRAGMATIC, $gameId, $currency);

        /** @var TransactionStorageInterface $transactions */
        $transactions = new RedisTransactionStorage($user->id, 'pragplay-bet', 200);

        $wallet = PragWallet::getForUser($user, $currency);

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

        $trans = $transactions->getByTransactionId($request['reference']);
        if (!empty($trans)) {
            $jresp = json_encode($trans['resp']);
            Log::channel('pragmatik')->info('repeat response: ' . $jresp);
            return $jresp;
        }

        if (!empty($request['bonusCode'])) {
            $bonusCode = $request['bonusCode'];
            if (str_contains($bonusCode,'d')) $bonusCode = explode('d', $bonusCode)[0];
            if ($d_bonus && $d_bonus->getId() == $bonusCode) {
                $balance = $d_bonus->externFreeSpinBet($wallet, GamesProvider::GAME_SYSTEM_PRAGMATIC, $gameId, $request['amount']);

                $transaction = [
                    'user_id' => $user->id,
                    'bonus' => $d_bonus->id,
                    'action' => 'bet',
                    'refunded' => false,
                    'amount' => $request['amount'],
                    'currency' => $currency_name,
                    'game_id' => $request['gameId'],
                    'session_id' => $request['reference'],
                    'transaction_id' => $request['reference'],
                    'type' => $request['roundId'],
                    'system' => GamesProvider::GAME_SYSTEM_PRAGMATIC,
                    'date' => now()->format('Y-m-d H:i:s'),
                ];

                $s_trans = $transactions->addGenId($transaction);
                $transactions->save();

                $transaction['gen_id'] = $s_trans['id'];

                DeferredModelWriter::getForModel(GameTransaction::class)->store($transaction);

                $resp->transactionId = $s_trans['id'];

                if ($d_bonus->isActive() && $balance >= 0) {
                    $resp->bonus = $balance;
                    $jresp = $this->encode_resp($resp);
                } else {
                    $resp->cash = $wallet->getBalance();
                    $jresp = $this->encode_resp($resp);
                }
                $s_trans['resp'] = $resp;
                $transactions->update(['id' => $s_trans['id']], $s_trans);
                Redis::set($user->id.':last_bet_value', $request['amount']);
                Redis::set($user->id.':last_bet_id', $s_trans['id']);
                Log::channel('pragmatik')->info('response: ' . $jresp);
                return $jresp;
            }
        }

        if ($d_bonus && $d_bonus->isVegasAllowed() && $wallet) {

            //set bonus id to prevent restoring prize games after bonus is complete
            $wallet->bonus = $d_bonus->id;
            $wallet->save();

            if (($d_bonus->getBalanceCents($wallet) / 100) < $request['amount']) {
                $resp->error = 1;
                $resp->description = "INSUFFICIENT_FUNDS";
                $jresp = $this->encode_resp($resp);
                Log::channel('pragmatik')->info('response: ' . $jresp);
                return $jresp;
            }

            if ($d_bonus->withdrawCents($request['amount'] * 100, $wallet, GamesProvider::GAME_SYSTEM_PRAGMATIC, $gameId) === false) {
                $resp->error = 1;
                $resp->description = "INSUFFICIENT_FUNDS";
                $jresp = $this->encode_resp($resp);
                Log::channel('pragmatik')->info('response: ' . $jresp);
                return $jresp;
            }

            $d_bonus->actualizeVegas($gameId, $wallet, GamesProvider::GAME_SYSTEM_PRAGMATIC);
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
                'session_id' => $request['reference'],
                'transaction_id' => $request['reference'],
                'type' => $request['roundId'],
                'system' => GamesProvider::GAME_SYSTEM_PRAGMATIC,
                'date' => now()->format('Y-m-d H:i:s'),
            ];

            $s_trans = $transactions->addGenId($transaction);
            $transactions->save();

            $transaction['gen_id'] = $s_trans['id'];

            DeferredModelWriter::getForModel(GameTransaction::class)->store($transaction);

            $resp->transactionId = $s_trans['id'];
            $resp->bonus = $d_bonus->getBalanceCents($wallet) / 100;
            $resp->usedPromo = $request['amount'];
            $jresp = $this->encode_resp($resp);
            $s_trans['resp'] = $resp;
            $transactions->update(['id' => $s_trans['id']], $s_trans);
            Redis::set($user->id.':last_bet_value', $request['amount']);
            Redis::set($user->id.':last_bet_id', $s_trans['id']);
            Log::channel('pragmatik')->info('response: ' . $jresp);
            return $jresp;
        }

        try {
            DB::beginTransaction();

            if ($wallet) {
                Redis::del('bonus-status-' . $user->id);
                if ($wallet->bonus != 0) {
                    $wallet->bonus = 0;
                    $wallet->save();
                }

                if ($request['amount'] < 0) {
                    $resp->error = 7;
                    $resp->description = "amount < 0";
                    $jresp = $this->encode_resp($resp);
                    DB::commit();
                    Log::channel('pragmatik')->info('response: ' . $jresp);
                    return $jresp;
                }

                if ($wallet->amount < $request['amount']) {

                    if ($wallet->amount < $request['amount']) {
                        $resp->error = 1;
                        $resp->description = "INSUFFICIENT_FUNDS";
                        $jresp = $this->encode_resp($resp);
                        DB::commit();
                        Log::channel('pragmatik')->info('response: ' . $jresp);
                        return $jresp;
                    }
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
                    $tnt_res_game = $tnt_game?->castByType($tnt_game);
                    if ($tnt_res_game && !$tnt_res_game->isResident()) {
                        $tnt_game = null;
                        $game_data->resident_tnt_game_id = 0;
                    }
                }

                if ($tnt_res_game) {
                    $tnt_res_game->onBet($request['amount'] * 100, $wallet, "pragplay", $gameId);
                    $tnt_res_game->save();
                }

                if ($user) {
//                    $user->TotalBet += $request['amount'];
                    if ($user->getWallet($currency)->getLockedMoney() > 0) {
                        $user->getWallet($currency)->remLockedMoney($request['amount'] * ($game_data->wager_prc / 100));
                    }
                    $user->save();
                    $user->getWallet($currency)->addBet($request['amount']);
                }
                $wallet->save();
//            }

//            $wallet->refresh();


                $transaction = [
                    'user_id' => $user->id,
                    'bonus' => 0,
                    'action' => 'bet',
                    'refunded' => false,
                    'amount' => $request['amount'],
                    'currency' => $currency_name,
                    'game_id' => $request['gameId'],
                    'session_id' => $request['reference'],
                    'transaction_id' => $request['reference'],
                    'type' => $request['roundId'],
                    'system' => GamesProvider::GAME_SYSTEM_PRAGMATIC,
                    'date' => now()->format('Y-m-d H:i:s'),
                ];

                $s_trans = $transactions->addGenId($transaction);
                $transactions->save();

                $transaction['gen_id'] = $s_trans['id'];

                DeferredModelWriter::getForModel(GameTransaction::class)->store($transaction);

                $resp->transactionId = $s_trans['id'];
                $resp->cash = $wallet->amount;
                $jresp = $this->encode_resp($resp);
                DB::commit();
                $s_trans['resp'] = $resp;
                $transactions->update(['id' => $s_trans['id']], $s_trans);
                Redis::set($user->id.':last_bet_value', $request['amount']);
                Redis::set($user->id.':last_bet_id', $s_trans['id']);
                Log::channel('pragmatik')->info('response: ' . $jresp);
                return $jresp;
            }

        } catch (\Exception $e) {
            DB::rollBack();
            Log::channel('pragmatik')->error($e->getMessage());
        }

        DB::commit();

        $resp->error = 120;
        $resp->description = "player not found";
        $jresp = $this->encode_resp($resp);
        Log::channel('pragmatik')->info('response: ' . $jresp);
        return $jresp;
    }

    public function refund(Request $request)
    {
        Log::channel('pragmatik')->info('refund request:' . var_export($request->all(), true));

        $data = $request->all();
        $api = new PragmaticPlay();

        $resp = new \StdClass();
        $resp->currency = '';
        $resp->cash = 0;
        $resp->bonus = 0;
        $resp->error = 0;
        $resp->description = 'Success';

        if (!$api->checkHash($data)) {
            Log::channel('pragmatik')->error('hash check fail');
            $resp->error = 100;
            $resp->description = "hash check fail";
            $jresp = $this->encode_resp($resp);
            Log::channel('pragmatik')->info('response: ' . $jresp);
            return $jresp;
        }

        $player_id = $request['userId'];
        $is_bonus_game = false;
        $currency_name = "";
        //parse player format [bonus],id,[currency]
        $match = null;
        if (preg_match('/^(bonus)?([0-9]+)([a-zA-z]{3,5})?$/', $player_id, $match)) {
            $is_bonus_game = !empty($match[1]);
            $player_id = $match[2];
            $currency_name = $match[3]?? '';
        }
        //$casino_id=$params['CasinoId'];
        $gameId = $request['gameId'];
        /** @var MUser $user */
        $user = MUser::find($player_id);

        /** @var TransactionStorageInterface $rfnd_transactions */
        $rfnd_transactions = new RedisTransactionStorage($user->id, 'pragplay-refunds', 100);

        /** @var TransactionStorageInterface $transactions */
        $transactions = new RedisTransactionStorage($user->id, 'pragplay-bet', 200);

//        $currency_name = $request['currency'];
        $currency = !empty($currency_name) ? UserWallet::getCurrencyCode($currency_name) : $user->currency;
        $resp->currency = $currency_name;

        $wallet = PragWallet::getForUser($user, $currency);

        $d_bonus = null;

        if ($user->balance_mode == MUser::BALANCE_MODE_BONUS) {
            /** @var UserBonus $d_bonus */
            $d_bonus = UserBonus::findFirstActiveBonus($player_id, $currency);
        }

        if ($user->balance_mode == MUser::BALANCE_MODE_TOURNAMENT) {
            /** @var TournamentGame $trg */
            $trgame = TournamentGame::findFirstActiveGame($user->id);
            if ($trgame && $trgame->isGameAllowedByProvider(GamesProvider::GAME_SYSTEM_PRAGMATIC, $gameId)) {
                $d_bonus = $trgame;
            }
        }

        if ($d_bonus && $d_bonus->isVegasAllowed() && $wallet) {
            //todo bonus refund!
            $resp->transactionId = $request['reference'] . 'rfnd';
            $resp->bonus = $d_bonus->getBalanceCents($wallet) / 100;
            $jresp = $this->encode_resp($resp);
            Log::channel('pragmatik')->info('response: ' . $jresp);
            return $jresp;
        }

        if ($wallet) {

            $refuned = $rfnd_transactions->getByTransactionId($request['reference']);
            if (!empty($refuned)) {
                $resp->transactionId = $refuned['id'];
                $resp->cash = $wallet->amount;
                $jresp = $this->encode_resp($resp);
                Log::channel('pragmatik')->info('response: ' . $jresp);
                return $jresp;
            }

            $tr = $transactions->getByTransactionId($request['reference']);

            if (!empty($tr)) {

                $transaction = [
                    'user_id' => $user->id,
                    'amount' => $request['amount'],
                    'currency' => $currency_name,
                    'game_id' => $request['gameId'],
                    'transaction_id' => $request['reference'],
                    'bet_transaction_id' => $request['reference'],
                    'type' => $request['type'],
                    'system' => GamesProvider::GAME_SYSTEM_PRAGMATIC,
                    'date' => now()->format('Y-m-d H:i:s'),
                ];

                $rf_trans = $rfnd_transactions->addGenId($transaction);
                $rfnd_transactions->save();

                DeferredModelWriter::getForModel(GameTransaction::class)->store($transaction);

                $resp->transactionId = $rf_trans['id'];

                if (!($tr['refunded'])) {
                    $transactions->update(['id' => $tr['id']], ['refunded' => true]);

//                    $wallet->amount += $tr['amount'];
                    $wallet->addMoney($tr['amount']);
                    $wallet->save();
                }
//                $wallet->refresh();

                $resp->cash = $wallet->amount;
                $jresp = $this->encode_resp($resp);
                Log::channel('pragmatik')->info('response: ' . $jresp);
                return $jresp;
            }

            $resp->error = 120;
            $resp->description = "transaction not found";
            $jresp = $this->encode_resp($resp);
            Log::channel('pragmatik')->info('response: ' . $jresp);
            return $jresp;
        }

        $resp->error = 120;
        $resp->description = "player not found";
        $jresp = $this->encode_resp($resp);
        Log::channel('pragmatik')->info('response: ' . $jresp);
        return $jresp;
    }

    // win
    public function result(Request $request)
    {
        Log::channel('pragmatik')->info('result (WIN) request:' . var_export($request->all(), true));

        $data = $request->all();
        $api = new PragmaticPlay();

        $resp = new \StdClass();
        $resp->currency = '';
        $resp->cash = 0;
        $resp->bonus = 0;
        $resp->error = 0;
        $resp->description = 'Success';

        if (!$api->checkHash($data)) {
            Log::channel('pragmatik')->error('hash check fail');
            $resp->error = 100;
            $resp->description = "hash check fail";
            $jresp = $this->encode_resp($resp);
            Log::channel('pragmatik')->info('response: ' . $jresp);
            return $jresp;
        }

        $player_id = $request['userId'];
        $is_bonus_game = false;
        $currency_name = "";
        //parse player format [bonus],id,[currency]
        $match = null;
        if (preg_match('/^(bonus)?([0-9]+)([a-zA-z]{3,5})?$/', $player_id, $match)) {
            $is_bonus_game = !empty($match[1]);
            $player_id = $match[2];
            $currency_name = $match[3]?? '';
        }
        $gameId = $request['gameId'];
        /** @var MUser $user */
        $user = MUser::find($player_id);

//        $currency_name = $request['currency'];
        $currency = !empty($currency_name) ? UserWallet::getCurrencyCode($currency_name) : $user->currency;
        $resp->currency = $currency_name;

        $game_data = new GameData($user);
        $game_data->getDataFormGame(GamesProvider::GAME_SYSTEM_PRAGMATIC, $gameId, $currency);

        /** @var TransactionStorageInterface $transactions */
        $transactions = new RedisTransactionStorage($user->id, 'pragplay-win', 200);

        $wallet = PragWallet::getForUser($user, $currency);

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

        $trans = $transactions->getByTransactionId($request['reference']);
        if (!empty($trans)) {
            $jresp = json_encode($trans['resp']);
            Log::channel('pragmatik')->info('repeat response: ' . $jresp);
            return $jresp;
        }

        if (!empty($request['bonusCode'])) {
            $bonusCode = $request['bonusCode'];
            if (str_contains($bonusCode,'d')) $bonusCode = explode('d', $bonusCode)[0];
            if ($d_bonus && $d_bonus->getId() == $bonusCode) {
                $balance = $d_bonus->externFreeSpinWin($wallet, GamesProvider::GAME_SYSTEM_PRAGMATIC, $gameId, $request['amount']);

                $transaction = [
                    'user_id' => $user->id,
                    'bonus' => $d_bonus->id,
                    'action' => 'win',
                    'refunded' => false,
                    'amount' => $request['amount'],
                    'currency' => $currency_name,
                    'game_id' => $request['gameId'],
                    'session_id' => $request['reference'],
                    'transaction_id' => $request['reference'],
                    'type' => $request['roundId'],
                    'system' => GamesProvider::GAME_SYSTEM_PRAGMATIC,
                    'date' => now()->format('Y-m-d H:i:s'),
                    'last_bet_value' => Redis::get($user->id.':last_bet_value', 0),
                    'last_bet_id' => Redis::get($user->id.':last_bet_id'),
                ];

                $s_trans = $transactions->addGenId($transaction);
                $transactions->save();

                $transaction['gen_id'] = $s_trans['id'];

                DeferredModelWriter::getForModel(GameTransaction::class)->store($transaction);

                $resp->transactionId = $s_trans['id'];

                if ($d_bonus->isActive() && $balance >= 0) {
                    $resp->bonus = $balance;
                    $jresp = $this->encode_resp($resp);
                } else {
                    $resp->cash = $wallet->getBalance();
                    $jresp = $this->encode_resp($resp);
                }
                $s_trans['resp'] = $resp;
                $transactions->update(['id' => $s_trans['id']], $s_trans);
                Log::channel('pragmatik')->info('response: ' . $jresp);
                return $jresp;
            }
        }

        if ($d_bonus && $d_bonus->isVegasAllowed() && $wallet) {

            $d_bonus->depositCents($request['amount'] * 100, $wallet, "pragplay", $gameId);
            $d_bonus->depositCents($request->get('promoWinAmount', 0) * 100, $wallet, "pragplay", $gameId);

            //add to wins table
            if ($user && $request['amount'] > 0) {
                CurrentWin::addWin($user, '', $request['amount'], $currency_name, $gameId, GamesProvider::GAME_SYSTEM_PRAGMATIC);
            }

            $d_bonus->actualizeVegas($gameId, $wallet, GamesProvider::GAME_SYSTEM_PRAGMATIC);
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
                'session_id' => $request['reference'],
                'transaction_id' => $request['reference'],
                'type' => $request['roundId'],
                'system' => GamesProvider::GAME_SYSTEM_PRAGMATIC,
                'date' => now()->format('Y-m-d H:i:s'),
                'last_bet_value' => Redis::get($user->id.':last_bet_value', 0),
                'last_bet_id' => Redis::get($user->id.':last_bet_id'),
            ];

            $s_trans = $transactions->addGenId($transaction);
            $transactions->save();

            $transaction['gen_id'] = $s_trans['id'];

            DeferredModelWriter::getForModel(GameTransaction::class)->store($transaction);

            $resp->transactionId = $s_trans['id'];
            $resp->bonus = $d_bonus->getBalanceCents($wallet) / 100;
            $jresp = $this->encode_resp($resp);
            $s_trans['resp'] = $resp;
            $transactions->update(['id' => $s_trans['id']], $s_trans);
            Log::channel('pragmatik')->info('response: ' . $jresp);
            return $jresp;
        }

        try {

            DB::beginTransaction();
            $wallet = PragWallet::getForUser($user, $currency);

            if ($wallet) {
                Redis::del('bonus-status-' . $user->id);

                if ($request['amount'] < 0) {
                    $resp->error = 7;
                    $resp->description = "amount < 0";
                    $jresp = $this->encode_resp($resp);
                    DB::commit();
                    Log::channel('pragmatik')->info('response: ' . $jresp);
                    return $jresp;
                }

                if ($wallet->bonus == 0) {
//                    $wallet->amount += $request['amount'] + $request->get('promoWinAmount', 0);
                    $wallet->addMoney($request['amount'] + $request->get('promoWinAmount', 0));
                    $wallet->save();
                    //add to wins table
                    if ($user && $request['amount'] > 0) {
                        $user->TotalWin += $request['amount'];
                        $user->save();
                        $user->getWallet($currency)->addWin($request['amount']);

                        //add to wins table
                        if ($user && $request['amount'] > 0) {
                            CurrentWin::addWin($user, '', $request['amount'], $currency_name, $gameId, GamesProvider::GAME_SYSTEM_PRAGMATIC);
                        }
                    }
                }

                $wallet->refresh();

                $transaction = [
                    'user_id' => $user->id,
                    'action' => 'win',
                    'refunded' => false,
                    'amount' => $request['amount'],
                    'currency' => $currency_name,
                    'game_id' => $request['gameId'],
                    'session_id' => $request['reference'],
                    'transaction_id' => $request['reference'],
                    'type' => $request['roundId'],
                    'system' => GamesProvider::GAME_SYSTEM_PRAGMATIC,
                    'date' => now()->format('Y-m-d H:i:s'),
                    'last_bet_value' => Redis::get($user->id.':last_bet_value', 0),
                    'last_bet_id' => Redis::get($user->id.':last_bet_id'),
                ];


                $s_trans = $transactions->addGenId($transaction);
                $transactions->save();

                $transaction['gen_id'] = $s_trans['id'];

                DeferredModelWriter::getForModel(GameTransaction::class)->store($transaction);

                $resp->transactionId = $s_trans['id'];
                $resp->cash = $wallet->amount;
                $jresp = $this->encode_resp($resp);
                DB::commit();
                $s_trans['resp'] = $resp;
                $transactions->update(['id' => $s_trans['id']], $s_trans);
                Log::channel('pragmatik')->info('response: ' . $jresp);
                return $jresp;
            }

        } catch (\Exception $e) {
            DB::rollBack();
            Log::channel('pragmatik')->error($e->getMessage());
        }

        DB::commit();

        $resp->error = 120;
        $resp->description = "player not found";
        $jresp = $this->encode_resp($resp);
        Log::channel('pragmatik')->info('response: ' . $jresp);
        return $jresp;
    }

    public function bonusWin(Request $request)
    {
        Log::channel('pragmatik')->info('bonusWin request:' . var_export($request->all(), true));

        $data = $request->all();
        $api = new PragmaticPlay();

        $resp = new \StdClass();
        $resp->currency = '';
        $resp->cash = 0;
        $resp->bonus = 0;
        $resp->error = 0;
        $resp->description = 'Success';

        if (!$api->checkHash($data)) {
            Log::channel('pragmatik')->error('hash check fail');
            $resp->error = 100;
            $resp->description = "hash check fail";
            $jresp = $this->encode_resp($resp);
            Log::channel('pragmatik')->info('response: ' . $jresp);
            return $jresp;
        }

        $player_id = $request['userId'];
        $is_bonus_game = false;
        $currency_name = "";
        //parse player format [bonus],id,[currency]
        $match = null;
        if (preg_match('/^(bonus)?([0-9]+)([a-zA-z]{3,5})?$/', $player_id, $match)) {
            $is_bonus_game = !empty($match[1]);
            $player_id = $match[2];
            $currency_name = $match[3]?? '';
        }
        /** @var MUser $user */
        $user = MUser::find($player_id);

//        $currency_name = $request['currency'];
        $currency = !empty($currency_name) ? UserWallet::getCurrencyCode($currency_name) : $user->currency;
        $resp->currency = $currency_name;


        /** @var TransactionStorageInterface $transactions */
        $transactions = new RedisTransactionStorage($user->id, 'pragplay-win', 200);

        $wallet = PragWallet::getForUser($user, $currency);

        if ($wallet) {

            if ($request['amount'] < 0) {
                $resp->error = 7;
                $resp->description = "amount < 0";
                $jresp = $this->encode_resp($resp);
                Log::channel('pragmatik')->info('response: ' . $jresp);
                return $jresp;
            }

            $trans = $transactions->getByTransactionId($request['reference']);
            if (!empty($trans)) {
                $jresp = json_encode($trans['resp']);
                Log::channel('pragmatik')->info('repeat response: ' . $jresp);
                return $jresp;
            }

            if (!empty($request['bonusCode'])) {

                $bonusCode = $request['bonusCode'];
                if (str_contains($bonusCode,'d')) $bonusCode = explode('d', $bonusCode)[0];

                /** @var UserBonus $d_bonus */
                $d_bonus = UserBonus::find($bonusCode);
                if ($d_bonus !== null) $d_bonus = UserBonus::convertByType($d_bonus);

                if ($d_bonus && $d_bonus->getId() == $bonusCode) {
                    $balance = $d_bonus->externFreeSpinsCompleted($wallet, GamesProvider::GAME_SYSTEM_PRAGMATIC, '', $request['amount']);

                    $transaction = [
                        'user_id' => $user->id,
                        'bonus' => $d_bonus->id,
                        'action' => 'fs-completed',
                        'refunded' => false,
                        'amount' => $request['amount'],
                        'currency' => $currency_name,
                        'game_id' => $request['gameId']??'',
                        'session_id' => $request['reference'],
                        'transaction_id' => $request['reference'],
                        'type' => $request['roundId']??'',
                        'system' => GamesProvider::GAME_SYSTEM_PRAGMATIC,
                        'date' => now()->format('Y-m-d H:i:s'),
                    ];

                    $s_trans = $transactions->addGenId($transaction);
                    $transactions->save();

                    DeferredModelWriter::getForModel(GameTransaction::class)->store($transaction);

                    $resp->transactionId = $s_trans['id'];

                    if ($d_bonus->isActive()) {
                        $resp->bonus = $balance;
                        $jresp = $this->encode_resp($resp);
                    } else {
                        $resp->cash = $wallet->getBalance();
                        $jresp = $this->encode_resp($resp);
                    }
                    $s_trans['resp'] = $resp;
                    $transactions->update(['id' => $s_trans['id']], $s_trans);
                    Log::channel('pragmatik')->info('response: ' . $jresp);
                    return $jresp;
                }
            }

//            $wallet->amount += $request['amount'] + $request->get('promoWinAmount', 0);
//            $wallet->save();
            //add to wins table
            if ($user && $request['amount'] > 0) {
//                $user->TotalWin += $request['amount'];
//                $user->save();

                //add to wins table
                if ($user && $request['amount'] > 0 && $request['gameId']) {
                    CurrentWin::addWin($user, '', $request['amount'], $currency_name, 'bonusWin', GamesProvider::GAME_SYSTEM_PRAGMATIC);
                }
            }

            $wallet->refresh();

            $transaction = [
                'user_id' => $user->id,
                'action' => 'bonusWin',
                'refunded' => false,
                'amount' => $request['amount'],
                'currency' => $currency_name,
                'game_id' => $request['gameId'] ?? 'bonusWin',
                'session_id' => $request['reference'],
                'transaction_id' => $request['reference'],
                'type' => $request['roundId'],
                'system' => GamesProvider::GAME_SYSTEM_PRAGMATIC,
                'date' => now()->format('Y-m-d H:i:s'),
            ];

            $s_trans = $transactions->addGenId($transaction);
            $transactions->save();

            DeferredModelWriter::getForModel(GameTransaction::class)->store($transaction);

            $resp->transactionId = $s_trans['id'];
            $resp->cash = $wallet->amount;
            $jresp = $this->encode_resp($resp);
            $s_trans['resp'] = $resp;
            $transactions->update(['id' => $s_trans['id']], $s_trans);
            Log::channel('pragmatik')->info('response: ' . $jresp);
            return $jresp;
        }

        $resp->error = 120;
        $resp->description = "player not found";
        $jresp = $this->encode_resp($resp);
        Log::channel('pragmatik')->info('response: ' . $jresp);
        return $jresp;
    }

    public function jackpotWin(Request $request)
    {
        Log::channel('pragmatik')->info('jackpotWin request:' . var_export($request->all(), true));

        $data = $request->all();
        $api = new PragmaticPlay();

        $resp = new \StdClass();
        $resp->currency = '';
        $resp->cash = 0;
        $resp->bonus = 0;
        $resp->error = 0;
        $resp->description = 'Success';

        if (!$api->checkHash($data)) {
            Log::channel('pragmatik')->error('hash check fail');
            $resp->error = 100;
            $resp->description = "hash check fail";
            $jresp = $this->encode_resp($resp);
            Log::channel('pragmatik')->info('response: ' . $jresp);
            return $jresp;
        }

        $player_id = $request['userId'];
        $is_bonus_game = false;
        $currency_name = "";
        //parse player format [bonus],id,[currency]
        $match = null;
        if (preg_match('/^(bonus)?([0-9]+)([a-zA-z]{3,5})?$/', $player_id, $match)) {
            $is_bonus_game = !empty($match[1]);
            $player_id = $match[2];
            $currency_name = $match[3]?? '';
        }
        $gameId = $request['gameId'];
        /** @var MUser $user */
        $user = MUser::find($player_id);

//        $currency_name = $request['currency'];
        $currency = !empty($currency_name) ? UserWallet::getCurrencyCode($currency_name) : $user->currency;
        $resp->currency = $currency_name;

        $game_data = new GameData($user);
        $game_data->getDataFormGame(GamesProvider::GAME_SYSTEM_PRAGMATIC, $gameId, $currency);

        /** @var TransactionStorageInterface $transactions */
        $transactions = new RedisTransactionStorage($user->id, 'pragplay-win', 200);

        $wallet = PragWallet::getForUser($user, $currency);

        if ($wallet) {

            if ($request['amount'] < 0) {
                $resp->error = 7;
                $resp->description = "amount < 0";
                $jresp = $this->encode_resp($resp);
                Log::channel('pragmatik')->info('response: ' . $jresp);
                return $jresp;
            }

            $trans = $transactions->getByTransactionId($request['reference']);
            if (!empty($trans)) {
                $jresp = json_encode($trans['resp']);
                Log::channel('pragmatik')->info('repeat response: ' . $jresp);
                return $jresp;
            }

//            $wallet->amount += $request['amount'];
            $wallet->addMoney($request['amount']);
            $wallet->save();
            //add to wins table
            if ($user && $request['amount'] > 0) {
                $user->TotalWin += $request['amount'];
                $user->save();

                //add to wins table
                if ($user && $request['amount'] > 0 && $request['gameId']) {
                    CurrentWin::addWin($user, '', $request['amount'], $currency_name, $gameId, GamesProvider::GAME_SYSTEM_PRAGMATIC);
                }
//                            VegasBet::win($user, $operation['Amount'], $params);
            }

            $wallet->refresh();

            $transaction = [
                'user_id' => $user->id,
                'action' => 'jackpotWin',
                'refunded' => false,
                'amount' => $request['amount'],
                'jackpot_id' => $request['jackpotId'],
                'currency' => $currency_name,
                'game_id' => $request['gameId'] ?? '',
                'session_id' => $request['reference'],
                'transaction_id' => $request['reference'],
                'type' => $request['roundId'],
                'system' => GamesProvider::GAME_SYSTEM_PRAGMATIC,
                'date' => now()->format('Y-m-d H:i:s'),
            ];

            $s_trans = $transactions->addGenId($transaction);
            $transactions->save();

            DeferredModelWriter::getForModel(GameTransaction::class)->store($transaction);

            $resp->transactionId = $s_trans['id'];
            $resp->cash = $wallet->amount;
            $jresp = $this->encode_resp($resp);
            $s_trans['resp'] = $resp;
            $transactions->update(['id' => $s_trans['id']], $s_trans);
            Log::channel('pragmatik')->info('response: ' . $jresp);
            return $jresp;
        }

        $resp->error = 120;
        $resp->description = "player not found";
        $jresp = $this->encode_resp($resp);
        Log::channel('pragmatik')->info('response: ' . $jresp);
        return $jresp;
    }

    public function promoWin(Request $request)
    {
        Log::channel('pragmatik')->info('promoWin request:' . var_export($request->all(), true));

        $data = $request->all();
        $api = new PragmaticPlay();

        $resp = new \StdClass();
        $resp->currency = '';
        $resp->cash = 0;
        $resp->bonus = 0;
        $resp->error = 0;
        $resp->description = 'Success';

        if (!$api->checkHash($data)) {
            Log::channel('pragmatik')->error('hash check fail');
            $resp->error = 100;
            $resp->description = "hash check fail";
            $jresp = $this->encode_resp($resp);
            Log::channel('pragmatik')->info('response: ' . $jresp);
            return $jresp;
        }

        $player_id = $request['userId'];
        $is_bonus_game = false;
        $currency_name = "";
        //parse player format [bonus],id,[currency]
        $match = null;
        if (preg_match('/^(bonus)?([0-9]+)([a-zA-z]{3,5})?$/', $player_id, $match)) {
            $is_bonus_game = !empty($match[1]);
            $player_id = $match[2];
            $currency_name = $match[3]?? '';
        }
        /** @var MUser $user */
        $user = MUser::find($player_id);

//        $currency_name = $request['currency'];
        $currency = !empty($currency_name) ? UserWallet::getCurrencyCode($currency_name) : $user->currency;
        $resp->currency = $currency_name;


        /** @var TransactionStorageInterface $transactions */
        $transactions = new RedisTransactionStorage($user->id, 'pragplay-win', 200);

        $wallet = PragWallet::getForUser($user, $currency);

        if ($wallet) {

            if ($request['amount'] < 0) {
                $resp->error = 7;
                $resp->description = "amount < 0";
                $jresp = $this->encode_resp($resp);
                Log::channel('pragmatik')->info('response: ' . $jresp);
                return $jresp;
            }

            $trans = $transactions->getByTransactionId($request['reference']);
            if (!empty($trans)) {
                $jresp = json_encode($trans['resp']);
                Log::channel('pragmatik')->info('repeat response: ' . $jresp);
                return $jresp;
            }

//            $wallet->amount += $request['amount'];
            $wallet->addMoney($request['amount']);
            $wallet->save();
            //add to wins table
            if ($user && $request['amount'] > 0) {
//                $user->TotalWin += $request['amount'];
//                $user->save();

                //add to wins table
                if ($user && $request['amount'] > 0 && $request['gameId']) {
                    CurrentWin::addWin($user, '', $request['amount'], $currency_name, 'promoWin', GamesProvider::GAME_SYSTEM_PRAGMATIC);
                }
//                            VegasBet::win($user, $operation['Amount'], $params);
            }

            $wallet->refresh();

            $transaction = [
                'user_id' => $user->id,
                'action' => 'promoWin',
                'refunded' => false,
                'amount' => $request['amount'],
                'currency' => $currency_name,
                'game_id' => $request['gameId'] ?? '',
                'session_id' => $request['reference'],
                'transaction_id' => $request['reference'],
                'type' => $request['roundId'],
                'system' => GamesProvider::GAME_SYSTEM_PRAGMATIC,
                'date' => now()->format('Y-m-d H:i:s'),
            ];

            $s_trans = $transactions->addGenId($transaction);
            $transactions->save();

            DeferredModelWriter::getForModel(GameTransaction::class)->store($transaction);

            $resp->transactionId = $s_trans['id'];
            $resp->cash = $wallet->amount;
            $jresp = $this->encode_resp($resp);
            $s_trans['resp'] = $resp;
            $transactions->update(['id' => $s_trans['id']], $s_trans);
            Log::channel('pragmatik')->info('response: ' . $jresp);
            return $jresp;
        }

        $resp->error = 120;
        $resp->description = "player not found";
        $jresp = $this->encode_resp($resp);
        Log::channel('pragmatik')->info('response: ' . $jresp);
        return $jresp;
    }

    public function endRound(Request $request)
    {
        Log::channel('pragmatik')->info('endround request:' . var_export($request->all(), true));

        $data = $request->all();
        $api = new PragmaticPlay();

        $resp = new \StdClass();
        $resp->currency = '';
        $resp->cash = 0;
        $resp->bonus = 0;
        $resp->error = 0;
        $resp->description = 'Success';

        if (!$api->checkHash($data)) {
            Log::channel('pragmatik')->error('hash check fail');
            $resp->error = 100;
            $resp->description = "hash check fail";
            $jresp = $this->encode_resp($resp);
            Log::channel('pragmatik')->info('response: ' . $jresp);
            return $jresp;
        }

        $player_id = $request['userId'];
        $is_bonus_game = false;
        $currency_name = "";
        //parse player format [bonus],id,[currency]
        $match = null;
        if (preg_match('/^(bonus)?([0-9]+)([a-zA-z]{3,5})?$/', $player_id, $match)) {
            $is_bonus_game = !empty($match[1]);
            $player_id = $match[2];
            $currency_name = $match[3]?? '';
        }
        $gameId = $request['gameId'];
        /** @var MUser $user */
        $user = MUser::find($player_id);

//        $currency_name = $request['currency'];
        $currency = !empty($currency_name) ? UserWallet::getCurrencyCode($currency_name) : $user->currency;
        $resp->currency = $currency_name;

        $wallet = PragWallet::getForUser($user, $currency);

        if ($wallet) {
            $resp->cash = $wallet->getBalance();
            $jresp = $this->encode_resp($resp);
            Log::channel('pragmatik')->info('response: ' . $jresp);
            return $jresp;
        }

        $resp->error = 120;
        $resp->description = "player not found";
        $jresp = $this->encode_resp($resp);
        Log::channel('pragmatik')->info('response: ' . $jresp);
        return $jresp;
    }

    public function sessionExpired(Request $request)
    {
        $resp = new \StdClass();
        $resp->currency = '';
        $resp->cash = 0;
        $resp->bonus = 0;
        $resp->error = 0;
        $resp->description = 'Success';

        $jresp = $this->encode_resp($resp);
        Log::channel('pragmatik')->info('response: ' . $jresp);
        return $jresp;
    }

    private function writeCashbackTransaction(int $player_id, float $amount, $currency = UserWallet::CURRENCY_RUB)
    {
        $user = MUser::find($player_id);
        if ($user) {
            DB::table('payout_transactions')
                ->insert([
                    'user_id' => $user->id,
                    'hall_id' => $user->hall_id,
                    'date' => date('Y-m-d H:i:s'),
                    'sum' => $amount,
                    'status_id' => 35,
                    'currency' => UserWallet::currencyName($currency),
                    'operation' => 'CashBack bonus payed in pragmatik'
                ]);
        }
    }

    private function encode_resp($resp)
    {
        if (isset($resp->cash)) $resp->cash = number_format($resp->cash, 2, '.', '');
        if (isset($resp->bonus)) $resp->bonus = number_format($resp->bonus, 2, '.', '');
        return json_encode($resp);
    }
}
