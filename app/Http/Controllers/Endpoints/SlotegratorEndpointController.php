<?php

namespace App\Http\Controllers\Endpoints;

use App\Gameproviders\Slotegrator\Models\SlergWallet;
use App\Gameproviders\Transaction\TransactionStorageInterface;
use App\GamesProviders\CurrentWin;
use App\GamesProviders\GameData;
use App\GamesProviders\GamesProvider;
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
use Illuminate\Support\Str;

/**
 * @deprecated
 */
class SlotegratorEndpointController extends Controller
{
    protected $merchant_id = "f409d3517e094d60cac45462a9b347f0";
    protected $key = "702411089f962240d408b5d041baf806cc4747a3";

    protected $error = null;

    //
    public function endpoint(Request $request)
    {
//        Log::channel('slotegrator')->info('headers: '.var_export($request->headers->all(), true));
        Log::channel('slotegrator')->info('request: ' . var_export($request->all(), true));
        Measure::logNow('sleg request start');
        $headers = $request->headers->all();
        $data = $request->all();
        $resp = Http::asForm()->withHeaders($headers)->post('http://127.0.0.1:23100/sleg', $data);
        Measure::logNow('sleg request end');
        return $resp->body();
//        try {
//            $this->checkSign($request);
//        } catch (\Exception $e) {
//            Log::channel('slotegrator')->error($e->getMessage());
//            return response('rejected', 403);
//        }
//
//        try {
//            $json_req = $request->all();
//
//            $resp = new \StdClass();
//
//            if (isset($json_req['transaction_id'])) {
//                $resp->transaction_id = $json_req['transaction_id'];
//            }
//
//            if (!empty($json_req) && isset($json_req['action'])) {
//
//                try {
//                    switch ($json_req['action']) {
//                        case 'balance':
//                            $this->balance($json_req, $resp);
//                            break;
//                        case 'bet':
//                        case 'win':
//                            $this->betAndWin($json_req, $resp);
//                            break;
//                        case 'refund':
//                            $this->refund($json_req, $resp);
//                            break;
//                        default:
//                            $resp->error = $this->genError("INTERNAL_ERROR", "Method not found");
//                    }
//
//                } catch (\Exception $e) {
//                    Hlog::logException($e, 'slotegrator');
//                    $resp->error = $this->genError("INTERNAL_ERROR", $e->getMessage());
//                }
//                DB::commit();
//            }
//
//            if (!empty($this->error)) {
//                $resp = $this->error;
//            }
//
//            if (isset($resp->balance)) $resp->balance = number_format($resp->balance, 2, '.', '');
//
//            Log::channel('slotegrator')->info('to request: ' . var_export($request->all(), true) . ' resp: ' . json_encode($resp));
//        } catch (\Exception $e) {
//            Hlog::logException($e, 'slotegrator');
//        }
//
//        DB::commit();
//
//        return json_encode($resp);
    }

    public function balance(array $request, \StdClass $resp)
    {
        Log::channel('slotegrator')->info('balance ');

        $player_id = $request['player_id'];
        $gameId = $request['game_uuid']??'';

        //if bonus game player id is bonus12345...
        $match = null;
        if (preg_match('/^(bonus)?([0-9]+)([a-zA-z]{3,5})?$/', $player_id, $match)) {
            $is_bonus_game = !empty($match[1]);
            $player_id = $match[2];
            $currency_name = $match[3]?? $request['currency'];
        }

        /** @var MUser $user */
        $user = MUser::find($player_id);
//        $currency_name = $request['currency'];
        $currency = !empty($currency_name) ? UserWallet::getCurrencyCode($currency_name) : $user->currency;
        $wallet = SlergWallet::getForUser($user, $currency);

        $d_bonus = null;

        if ($user->balance_mode == MUser::BALANCE_MODE_BONUS) {
            /** @var UserBonus $d_bonus */
            $d_bonus = UserBonus::findFirstActiveBonus($player_id, $currency);
        }

        if ($d_bonus) {
            $resp->balance = $d_bonus->getBalanceCents($wallet) / 100;
            $d_bonus->refreshBonusStatus($wallet, 'sleg', $gameId);
            $wallet->bonus = $d_bonus->getId();
            $wallet->save();
            return;
        }

        if ($user->balance_mode == MUser::BALANCE_MODE_TOURNAMENT) {
            /** @var TournamentGame $trg */
            $trg = TournamentGame::findFirstActiveGame($user->id);
            if ($trg) {
                $trg = $trg->castByType($trg);
                $resp->balance = $trg->getBalanceCents($wallet) / 100;
                $trg->refreshBonusStatus($wallet, 'sleg', $gameId);
                $wallet->bonus = $trg->id;
                $wallet->save();
                return;
            }
        }

        Redis::del('bonus-status-' . $user->id);

        if ($wallet) {
            $resp->balance = floatval($wallet->amount);
            return;
        }

        $resp->error = $this->genError("INTERNAL_ERROR", "Player not found");
    }

    public function betAndWin(array $request, \StdClass $resp)
    {
        Log::channel('slotegrator')->info('bet or win ');

        $player_id = $request['player_id'];
        $is_bonus_game = false;
        $currency_name = "";
        //parse player format [bonus],id,[currency]
        $match = null;
        if (preg_match('/^(bonus)?([0-9]+)([A-z]{3,5})?$/', $player_id, $match)) {
            $is_bonus_game = !empty($match[1]);
            $player_id = $match[2];
            $currency_name = $match[3]?? $request['currency'];
        }
        $gameId = $request['game_uuid'];
        /** @var MUser $user */
        $user = MUser::find($player_id);

        /** @var TransactionStorageInterface $transactions */
        $transactions = new RedisTransactionStorage($user->id, 'slegtransaction', 300);

//        $currency_name = $request['currency'];
        $currency = !empty($currency_name) ? UserWallet::getCurrencyCode($currency_name) : $user->currency;

        $wallet = SlergWallet::getForUser($user, $currency);

        $game_data = new GameData($user);
        $game_data->getDataFormGame('sleg', $gameId, $currency);

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

        if ($request['type'] === 'freespin' && !empty($request['freespin_id'])) {
            /** @var UserBonus $q_bonus */
            $s_bonus = null;
            if (!empty($request['freespin_id'])) {
                $freespin_id = $request['freespin_id'];
                if (str_contains($freespin_id,'d')) $freespin_id = explode('d', $freespin_id)[0];
                $s_bonus = UserBonus::find($freespin_id);
                if ($s_bonus !== null) $s_bonus = UserBonus::convertByType($s_bonus);
            }
            if ($s_bonus) {
                if ($transactions->transactionExist($request['transaction_id'])) {
                    $resp->balance = floatval($s_bonus->getBalanceCents($wallet) / 100);
                    $transaction = $transactions->getByTransactionId($request['transaction_id']);
                    $resp->transaction_id = $transaction['gen_id']?? $request['transaction_id'];
                    return;
                }
                $balance = 0;
                $action = $request['action'];
                if ($action === 'bet') $balance = $s_bonus->externFreeSpinBet($wallet, GamesProvider::GAME_SYSTEM_SLOTEGRATOR, $gameId, $request['amount']?? 0);
                if ($action === 'win') $balance = $s_bonus->externFreeSpinWin($wallet, GamesProvider::GAME_SYSTEM_SLOTEGRATOR, $gameId, $request['amount']?? 0);

                if ($action === 'win' && $request['quantity'] == 0) {
                    $s_bonus->externFreeSpinsCompleted($wallet, GamesProvider::GAME_SYSTEM_SLOTEGRATOR, $gameId, -1);
                }

                $transaction = [
                    'user_id' => $user->id,
                    'bonus' => $s_bonus->id,
                    'action' => $request['action'],
                    'amount' => $request['amount'],
                    'currency' => $currency_name,
                    'game_id' => $request['game_uuid'],
                    'session_id' => $request['session_id'],
                    'transaction_id' => $request['transaction_id'],
                    'type' => $request['type'],
                    'system' => GamesProvider::GAME_SYSTEM_SLOTEGRATOR,
                    'date' => now()->format('Y-m-d H:i:s'),
                ];

                if ($action === 'win') {
                    $transaction['last_bet_value'] = Redis::get($user->id.':last_bet_value', 0)?? 0;
                    $transaction['last_bet_id'] = Redis::get($user->id.':last_bet_id');
                }

                $s_trans = $transactions->addGenId($transaction);
                $transactions->save();

                if ($action === 'bet') {
                    Redis::set($user->id . ':last_bet_value', $request['amount']);
                    Redis::set($user->id . ':last_bet_id', $s_trans['id']);
                }

                $resp->balance = floatval($balance);

                $transaction['gen_id'] = $s_trans['id'];
                $resp->transaction_id = $s_trans['id'];

                DeferredModelWriter::getForModel(GameTransaction::class)->store($transaction);
                Log::channel('slotegrator')->info('fs response: ' . var_export($resp, true));
                DB::commit();
                return;
            } else {
                throw new \Exception('no bonus for freespins campaign found');
            }
        }

        if ($d_bonus && $d_bonus->isVegasAllowed() && $wallet) {
            Log::channel('slotegrator')->info('bonus bet or win');

            if ($transactions->transactionExist($request['transaction_id'])) {
                $resp->balance = floatval($d_bonus->getBalanceCents($wallet) / 100);
                $transaction = $transactions->getByTransactionId($request['transaction_id']);
                $resp->transaction_id = $transaction['gen_id']?? $request['transaction_id'];
                return;
            }

            if ($request['action'] == "win") {
                $d_bonus->depositCents($request['amount'] * 100, $wallet, "sleg", $gameId);

                //add to wins table
                if ($user && $request['amount'] > 0) {

                    CurrentWin::addWin($user, '', $request['amount'], $currency_name, $gameId, 'sleg');
                }
            }

            if ($request['action'] == "bet") {
                //set bonus id to prevent restoring prize games after bonus is complete
                $wallet->bonus = $d_bonus->id;
                $wallet->save();

                if (($d_bonus->getBalanceCents($wallet) / 100) < $request['amount']) {
                    return $this->genError("INSUFFICIENT_FUNDS", "INSUFFICIENT_BALANCE");
                }

                if ($d_bonus->withdrawCents($request['amount'] * 100, $wallet, 'sleg', $gameId) === false) {
                    return $this->genError("INSUFFICIENT_FUNDS", "INSUFFICIENT_BALANCE");
                }
//                    session()->put('bonus_bet', $operation['Amount']);
            }
            $d_bonus->actualizeVegas($gameId, $wallet, 'sleg');
            $d_bonus->save();

            $status = $d_bonus->getStatusString($wallet);
            if (empty($status)) {
                Redis::del('bonus-status-' . $user->id);
            } else {
                Redis::set('bonus-status-' . $user->id, $status);
            }

//            $result->BalanceAfter = $d_bonus->getBalanceCents($wallet);
//            $resp->result = $result;
            $resp->balance = floatval($d_bonus->getBalanceCents($wallet) / 100);

            $transaction = [
                'user_id' => $user->id,
                'bonus' => $d_bonus->id,
                'action' => $request['action'],
                'amount' => $request['amount'],
                'currency' => $currency_name,
                'game_id' => $request['game_uuid'],
                'session_id' => $request['session_id'],
                'transaction_id' => $request['transaction_id'],
                'type' => $request['type'],
                'system' => 'sleg',
                'date' => now()->format('Y-m-d H:i:s'),
            ];

            if ($request['action'] === 'win') {
                $transaction['last_bet_value'] = Redis::get($user->id.':last_bet_value', 0);
                $transaction['last_bet_id'] = Redis::get($user->id.':last_bet_id');
            }

            $s_trans = $transactions->addGenId($transaction);
            $transactions->save();

            if ($request['action'] === 'bet') {
                Redis::set($user->id . ':last_bet_value', $request['amount']);
                Redis::set($user->id . ':last_bet_id', $s_trans['id']);
            }

            $transaction['gen_id'] = $s_trans['id'];
            $resp->transaction_id = $s_trans['id'];

            DeferredModelWriter::getForModel(GameTransaction::class)->store($transaction);

            return;
        }

        try {
            DB::beginTransaction();
            $wallet = SlergWallet::getForUser($user, $currency);

            if ($wallet) {
                Redis::del('bonus-status-' . $user->id);
                Log::channel('slotegrator')->info('main bet or win');
//            $result = new \StdClass();
//            $result->BalanceBefore = intval($wallet->money);


                if ($request['amount'] < 0) {
                    DB::commit();
                    return $this->genError("INTERNAL_ERROR", "Invalid params (amount < 0)");
                }

                if ($transactions->transactionExist($request['transaction_id'])) {
                    $resp->balance = $wallet->amount;
                    $transaction = $transactions->getByTransactionId($request['transaction_id']);
                    $resp->transaction_id = $transaction['gen_id']?? $request['transaction_id'];
                    DB::commit();
                    return;
                }

                if ($request['action'] == "win") {

                    if ($wallet->bonus == 0) {
//                $wallet->amount += $request['amount'];
                        $wallet->addMoney($request['amount']);
                        $wallet->save();
                        //add to wins table
                        if ($user && $request['amount'] > 0) {
                            $user->TotalWin += $request['amount'];
                            $user->save();

                            $user->getWallet($currency)->addWin($request['amount']);

                            CurrentWin::addWin($user, '', $request['amount'], $currency_name, $gameId, 'sleg');
                        }
                    }

//                    if ($wallet->bonus != 0) {
//                        $wallet->bonus = 0;
//                        $wallet->save();
//                    }

                }

                if ($request['action'] == "bet") {

                    if ($wallet->bonus != 0) {
                        $wallet->bonus = 0;
                        $wallet->save();
                    }

                    /** @var TransactionStorageInterface $rfnd_transactions */
                    $rfnd_transactions = new RedisTransactionStorage($user->id, 'sleg_refunds');

                    if ($rfnd_transactions->exists(['bet_transaction_id' => $request['transaction_id']])) {
                        $resp->balance = $wallet->amount;
                        DB::commit();
                        return;
                    }

                    if (session()->has('bonus_bet')) {
                        session()->forget('bonus_bet');
                    }

                    if ($wallet->amount < $request['amount']) {

                        if ($wallet->amount < $request['amount']) {
                            DB::commit();
                            return $this->genError("INSUFFICIENT_FUNDS", "INSUFFICIENT_BALANCE");
                        }
                    }
                    Log::channel('slotegrator')->info('rem money');
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
                        $tnt_res_game->onBet($request['amount'] * 100, $wallet, "sleg", $gameId);
                        $tnt_res_game->save();
                    }

                    if ($user) {
                        $user->TotalBet += $request['amount'];
                        $user->getWallet($currency)->addBet($request['amount']);

                        if ($game_data->locked_money > 0 && $user->getWallet($currency)->getLockedMoney() > 0) {
                            $user->getWallet($currency)->remLockedMoney($request['amount'] * ($game_data->wager_prc / 100));
                        } else {
                            $game_data->locked_money = 0;
                        }

                        $user->save();
                    }

                    $game_data->save();
                    $wallet->save();
                }

                $wallet->refresh();
                $resp->balance = $wallet->amount;

                $transaction = [
                    'user_id' => $user->id,
                    'action' => $request['action'],
                    'amount' => $request['amount'],
                    'currency' => $currency_name,
                    'game_id' => $request['game_uuid'],
                    'session_id' => $request['session_id'],
                    'transaction_id' => $request['transaction_id'],
                    'type' => $request['type'],
                    'system' => 'sleg',
                    'date' => now()->format('Y-m-d H:i:s'),
                ];

                if ($request['action'] === 'win') {
                    $transaction['last_bet_value'] = Redis::get($user->id.':last_bet_value', 0);
                    $transaction['last_bet_id'] = Redis::get($user->id.':last_bet_id');
                }

                $s_trans = $transactions->addGenId($transaction);
                $transactions->save();

                if ($request['action'] === 'bet') {
                    Redis::set($user->id . ':last_bet_value', $request['amount']);
                    Redis::set($user->id . ':last_bet_id', $s_trans['id']);
                }

                DB::commit();

                $transaction['gen_id'] = $s_trans['id'];
                $resp->transaction_id = $s_trans['id'];

                DeferredModelWriter::getForModel(GameTransaction::class)->store($transaction);

                return;
            }

        } catch (\Exception $e) {
            DB::rollBack();
            Log::channel('slotegrator')->error($e->getMessage());
        }

        DB::commit();

        $resp->error = $this->genError("INTERNAL_ERROR", "Player not found");
    }

    public function refund(array $request, \StdClass $resp)
    {
        Log::channel('slotegrator')->info('refund');

        $player_id = $request['player_id'];
        $is_bonus_game = false;
        $currency_name = "";
        //parse player format [bonus],id,[currency]
        $match = null;
        if (preg_match('/^(bonus)?([0-9]+)([a-zA-z]{3,5})?$/', $player_id, $match)) {
            $is_bonus_game = !empty($match[1]);
            $player_id = $match[2];
            $currency_name = !empty($match[3]) ? $match[3] : $request['currency'];
        }
        //$casino_id=$params['CasinoId'];
        $gameId = $request['game_uuid'];
        /** @var MUser $user */
        $user = MUser::find($player_id);
        $currency = !empty($currency_name) ? UserWallet::getCurrencyCode($currency_name) : $user->currency;

        $wallet = SlergWallet::getForUser($user, $currency);

        $d_bonus = null;

        /** @var TransactionStorageInterface $rfnd_transactions */
        $rfnd_transactions = new RedisTransactionStorage($user->id, 'sleg_refunds');

        // if transaction already processed
        $trans = $rfnd_transactions->transactionExist($request['transaction_id']);
        if (!empty($trans)) {
            Log::channel('slotegrator')->info('refund already processed');
            $transaction = [
                'user_id' => $user->id,
                'action' => $request['action'],
                'amount' => $request['amount'],
                'currency' => $currency_name,
                'game_id' => $request['game_uuid'],
                'session_id' => $request['session_id'],
                'transaction_id' => $request['transaction_id'],
                'bet_transaction_id' => $request['bet_transaction_id'],
                'type' => $request['type'],
                'system' => 'sleg',
                'date' => now()->format('Y-m-d H:i:s'),
            ];

            $s_trans = $rfnd_transactions->addGenId($transaction);
            $rfnd_transactions->save();

            $transaction['gen_id'] = $s_trans['id'];
            $resp->transaction_id = $s_trans['id'];

            DeferredModelWriter::getForModel(GameTransaction::class)->store($transaction);

            $resp->balance = $wallet->amount;
            return;
        }

        /** @var TransactionStorageInterface $transactions */
        $transactions = new RedisTransactionStorage($user->id, 'slegtransaction', 300);

        if ($user->balance_mode == MUser::BALANCE_MODE_BONUS) {
            /** @var UserBonus $d_bonus */
            $d_bonus = UserBonus::findFirstActiveBonus($player_id, $currency);
        }

        if ($user->balance_mode == MUser::BALANCE_MODE_TOURNAMENT) {
            /** @var TournamentGame $trg */
            $trgame = TournamentGame::findFirstActiveGame($user->id);
            if ($trgame && $trgame->isGameAllowedByProvider('sleg', $gameId)) {
                $d_bonus = $trgame;
            }
        }

        if ($d_bonus && $d_bonus->isVegasAllowed() && $wallet) {
            Log::channel('slotegrator')->info('bonus refund not available');
            //todo bonus refund! now just ignored
            $resp->balace = $d_bonus->getBalanceCents($wallet) / 100;
            $transaction = [
                'user_id' => $user->id,
                'action' => $request['action'],
                'amount' => $request['amount'],
                'bonus' => $d_bonus->id?? -1,
                'currency' => $currency_name,
                'game_id' => $request['game_uuid'],
                'session_id' => $request['session_id'],
                'transaction_id' => $request['transaction_id'],
                'bet_transaction_id' => $request['bet_transaction_id'],
                'type' => $request['type'],
                'system' => 'sleg',
                'date' => now()->format('Y-m-d H:i:s'),
            ];

            $s_trans = $rfnd_transactions->addGenId($transaction);
            $rfnd_transactions->save();

            $transaction['gen_id'] = $s_trans['id'];
            $resp->transaction_id = $s_trans['id'];
            DeferredModelWriter::getForModel(GameTransaction::class)->store($transaction);
            return;
        }

        if ($wallet) {

            $tr = $transactions->transactionExist($request['bet_transaction_id']);

            if ($tr) {
                $wallet->addMoney($request['amount']);
                $wallet->save();
            } else {
                Log::channel('slotegrator')->info('refund bet transaction not found in cache storage, searching in db...');
                // look for transaction
                /** @var GameTransaction $tr */
                $tr = GameTransaction::where('transaction_id', $request['bet_transaction_id'])
                    ->where('system', GamesProvider::GAME_SYSTEM_SLOTEGRATOR)
                    ->first();

                if ($tr !== null && !$tr->refunded) {
                    Log::channel('slotegrator')->info('bet transaction found return money');
                    $tr->refunded = true;
                    $wallet->addMoney($request['amount']);
                    $tr->save();
                    $wallet->save();
                } else {
                    Log::channel('slotegrator')->info('refund bet transaction not found in DB!!!');
                }
            }

            $wallet->refresh();


            $transaction = [
                'user_id' => $user->id,
                'action' => $request['action'],
                'amount' => $request['amount'],
                'currency' => $currency_name,
                'game_id' => $request['game_uuid'],
                'session_id' => $request['session_id'],
                'transaction_id' => $request['transaction_id'],
                'bet_transaction_id' => $request['bet_transaction_id'],
                'type' => $request['type'],
                'system' => 'sleg',
                'date' => now()->format('Y-m-d H:i:s'),
            ];

            $s_trans = $rfnd_transactions->addGenId($transaction);
            $rfnd_transactions->save();

            $transaction['gen_id'] = $s_trans['id'];
            $resp->transaction_id = $s_trans['id'];

            DeferredModelWriter::getForModel(GameTransaction::class)->store($transaction);

            $resp->balance = $wallet->amount;
            return;
        }

        $resp->error = $this->genError("INTERNAL_ERROR", "Player not found");
    }

    public function checkSign(Request $request)
    {
        $headers = [
//            'X-Merchant-Id' => $request->header('X-Merchant-Id'),
            'X-Merchant-Id' => $this->merchant_id,
            'X-Timestamp' => $request->header('X-Timestamp'),
            'X-Nonce' => $request->header('X-Nonce'),
        ];

        $XSign = $request->header('X-Sign');

        $mergedParams = array_merge($_POST, $headers);
        ksort($mergedParams);
        $hashString = http_build_query($mergedParams);
        $expectedSign = hash_hmac('sha1', $hashString, $this->key);

        if ($XSign !== $expectedSign) {
            throw new \Exception('Invalid sign');
        }
    }

    protected function genError(string $code, string $message)
    {
        $error = new \StdClass();
        $error->error_code = $code;
        $error->error_description = $message;
        $this->error = $error;
        return $error;
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
                    'operation' => 'CashBack bonus payed in slotegrator'
                ]);
        }
    }

}
