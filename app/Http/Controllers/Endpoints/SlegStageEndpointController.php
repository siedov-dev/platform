<?php

namespace App\Http\Controllers\Endpoints;

use App\GamesProviders\GamesProvider;
use App\Http\Controllers\Controller;
use App\Models\Bonuses\FilthyFsWashOffBonus;
use App\Models\Bonuses\NonDepositBonus;
use App\Models\Bonuses\UserBonus;
use App\Models\GameTransaction;
use App\Models\Tournaments\TournamentGames\TournamentGame;
use App\MUser;
use App\ProvidersApi\CurrentWin;
use App\ProvidersApi\GameData;
use App\ProvidersApi\Slotegrator\Models\SlergWallet;
use App\ProvidersApi\Transaction\RedisTransactionStorage;
use App\Redis\DeferredModelWriter;
use App\Redis\Transaction\TransactionStorageInterface;
use App\UserWallet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;

/**
 * @deprecated
 */
class SlegStageEndpointController extends Controller
{

    protected string $merchant_id = "";
    protected string $key = "";

    protected $error = null;

    //
    public function endpoint(Request $request)
    {
        $this->error = null;
//        Log::channel('stage')->info('headers: '.var_export($request->headers->all(), true));
        Log::channel('stage')->info('request: ' . var_export($request->all(), true));
        try {
            $this->checkSign($request);
        } catch (\Exception $e) {
            Log::channel('stage')->error($e->getMessage());
            return response('rejected', 403);
        }

        $json_req = $request->all();

        $resp = new \StdClass();

        if (!empty($json_req) && isset($json_req['action'])) {

            switch ($json_req['action']) {
                case 'balance':
                    $this->balance($json_req, $resp);
                    break;
                case 'bet':
                case 'win':
                    $this->betAndWin($json_req, $resp);
                    break;
                case 'refund':
                    $this->refund($json_req, $resp);
                    break;
                default:
                    $resp->error = $this->genError("INTERNAL_ERROR", "Method not found");
            }

        }

        if (isset($json_req['transaction_id'])) {
            $resp->transaction_id = $json_req['transaction_id'];
        }

        if (!empty($this->error)) {
            $resp = $this->error;
        }

        if (isset($resp->balance)) $resp->balance = number_format($resp->balance, 2, '.', '');

        Log::channel('stage')->info(date('Y.m.d H:i:s') . ' resp: ' . json_encode($resp));
        return json_encode($resp);
    }

    public function balance(array $request, \StdClass $resp)
    {
        Log::channel('stage')->info('balance ');

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
            return;
        }

        if ($user->balance_mode == MUser::BALANCE_MODE_TOURNAMENT) {
            /** @var TournamentGame $trg */
            $trg = TournamentGame::findFirstActiveGame($user->id);
            if ($trg) {
                $trg = $trg->castByType($trg);
                $resp->balance = $trg->getBalanceCents($wallet) / 100;
                $trg->refreshBonusStatus($wallet, 'sleg', $gameId);

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
        Log::channel('stage')->info('bet or win ');

        $player_id = $request['player_id'];
        $is_bonus_game = false;
        $currency_name = "";
        //parse player format [bonus],id,[currency]
        $match = null;
        if (preg_match('/^(bonus)?([0-9]+)([a-zA-z]{3,5})?$/', $player_id, $match)) {
            $is_bonus_game = !empty($match[1]);
            $player_id = $match[2];
            $currency_name = $match[3]?? $request['currency'];
        }
        $gameId = $request['game_uuid'];
        /** @var MUser $user */
        $user = MUser::find($player_id);

        /** @var TransactionStorageInterface $transactions */
        $transactions = new RedisTransactionStorage($user->id, 'slegtransaction', 50);

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
                $s_bonus = UserBonus::find($request['freespin_id']);
                if ($s_bonus !== null) $s_bonus = UserBonus::convertByType($s_bonus);
            }
            if ($s_bonus) {
                if ($transactions->transactionExist($request['transaction_id'])) {
                    $resp->balance = floatval($s_bonus->getBalanceCents($wallet) / 100);
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
                    'currency' => $request['currency'],
                    'game_id' => $request['game_uuid'],
                    'session_id' => $request['session_id'],
                    'transaction_id' => $request['transaction_id'],
                    'type' => $request['type'],
                    'system' => GamesProvider::GAME_SYSTEM_SLOTEGRATOR,
                    'date' => now()->format('Y-m-d H:i:s'),
                ];

                $transactions->addGenId($transaction);
                $transactions->save();

                $resp->balance = floatval($balance / 100);

                DeferredModelWriter::getForModel(GameTransaction::class)->store($transaction);
                Log::channel('stage')->info('fs response: ' . var_export($resp, true));
                DB::commit();
                return response($resp);
            }
        }

        if ($d_bonus && $d_bonus->isVegasAllowed() && $wallet) {
            Log::channel('stage')->info('bonus bet or win');

//            $result=new \StdClass();
//            $result->BalanceBefore = intval($d_bonus->getBalanceCents());

            if ($transactions->transactionExist($request['transaction_id'])) {
                $resp->balance = floatval($d_bonus->getBalanceCents($wallet) / 100);
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
                'currency' => $request['currency'],
                'game_id' => $request['game_uuid'],
                'session_id' => $request['session_id'],
                'transaction_id' => $request['transaction_id'],
                'type' => $request['type'],
                'system' => 'sleg',
                'date' => now()->format('Y-m-d H:i:s'),
            ];

            $transactions->addGenId($transaction);
            $transactions->save();

            DeferredModelWriter::getForModel(GameTransaction::class)->store($transaction);

            return;
        }

        try {
            DB::beginTransaction();
            $wallet = SlergWallet::getForUser($user, $currency);

            if ($wallet) {
                Redis::del('bonus-status-' . $user->id);
                Log::channel('stage')->info('main bet or win');
//            $result = new \StdClass();
//            $result->BalanceBefore = intval($wallet->money);


                if ($request['amount'] < 0) {
                    DB::commit();
                    return $this->genError("INTERNAL_ERROR", "Invalid params (amount < 0)");
                }

                if ($transactions->transactionExist($request['transaction_id'])) {
                    $resp->balance = $wallet->amount;
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

//                        //cashback
//                        /** @var CashbackEventBonus $cashback_bonus */
//                        $cashback_bonus = null;
//                        if ($game_data->cashback_bonus_id) {
//                            $cashback_bonus = CashbackEventBonus::find($game_data->cashback_bonus_id);
//                            if ($cashback_bonus->status != CashbackEventBonus::STATUS_PENDING) {
//                                $cashback_bonus = null;
//                                $game_data->cashback_bonus_id = 0;
//                                $game_data->save();
//                            }
//                        }
//
//                        if ($cashback_bonus) {
////                        $wallet->amount += $cashback_bonus->money_available;
//                            $wallet->addMoney($cashback_bonus->money_available);
//                            $cashback_bonus->status = CashbackEventBonus::STATUS_DONE;
//                            $wallet->save();
//                            $cashback_bonus->save();
//                            $this->writeCashbackTransaction($player_id, $cashback_bonus->money_available, $cashback_bonus->currency);
//                        }

                        if ($wallet->amount < $request['amount']) {
                            DB::commit();
                            return $this->genError("INSUFFICIENT_FUNDS", "INSUFFICIENT_BALANCE");
                        }
                    }
                    Log::channel('stage')->info('rem money');
//                $wallet->amount -= $request['amount'];
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

//                    if ($wallet->amount < 10) {
//
//                        //cashback
//                        /** @var CashbackEventBonus $cashback_bonus */
//                        $cashback_bonus = null;
//                        if ($game_data->cashback_bonus_id) {
//                            $cashback_bonus = CashbackEventBonus::find($game_data->cashback_bonus_id);
//                            if ($cashback_bonus->status != CashbackEventBonus::STATUS_PENDING) {
//                                $cashback_bonus = null;
//                                $game_data->cashback_bonus_id = 0;
//                                $game_data->save();
//                            }
//                        }
//
//                        if ($cashback_bonus) {
//                            $wallet->addMoney($cashback_bonus->money_available);
//                            $cashback_bonus->status = CashbackEventBonus::STATUS_DONE;
//                            $wallet->save();
//                            $cashback_bonus->save();
//                            $this->writeCashbackTransaction($player_id, $cashback_bonus->money_available, $cashback_bonus->currency);
//                        }
//
//                        if ($game_data->lose_bonus_id) {
//                            /** @var UserBonus $wl_bonus */
//                            $wl_bonus = UserBonus::getWaitingLose($user);
//                            if ($wl_bonus) {
//                                $wl_bonus->onUserLose();
//                            } else {
//                                $game_data->lose_bonus_id = 0;
//                            }
//                        }
//                    }

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
//            $result->BalanceAfter = intval($wallet->money);
//            $resp->result = $result;
                $resp->balance = $wallet->amount;

                $transaction = [
                    'user_id' => $user->id,
                    'action' => $request['action'],
                    'amount' => $request['amount'],
                    'currency' => $request['currency'],
                    'game_id' => $request['game_uuid'],
                    'session_id' => $request['session_id'],
                    'transaction_id' => $request['transaction_id'],
                    'type' => $request['type'],
                    'system' => 'sleg',
                    'date' => now()->format('Y-m-d H:i:s'),
                ];

                $transactions->addGenId($transaction);
                $transactions->save();

                DB::commit();

                DeferredModelWriter::getForModel(GameTransaction::class)->store($transaction);

                return;
            }

        } catch (\Exception $e) {
            DB::rollBack();
            Log::channel('stage')->error($e->getMessage());
        }

        DB::commit();

        $resp->error = $this->genError("INTERNAL_ERROR", "Player not found");
    }

    public function refund(array $request, \StdClass $resp)
    {
        Log::channel('stage')->info('refund');

        $player_id = $request['player_id'];
        $is_bonus_game = false;
        $currency_name = "";
        //parse player format [bonus],id,[currency]
        $match = null;
        if (preg_match('/^(bonus)?([0-9]+)([a-zA-z]{3,5})?$/', $player_id, $match)) {
            $is_bonus_game = !empty($match[1]);
            $player_id = $match[2];
            $currency_name = !empty($match[3]) ? $match[3] : "";
        }
        //$casino_id=$params['CasinoId'];
        $gameId = $request['game_uuid'];
        /** @var MUser $user */
        $user = MUser::find($player_id);

        /** @var TransactionStorageInterface $rfnd_transactions */
        $rfnd_transactions = new RedisTransactionStorage($user->id, 'sleg_refunds');

        /** @var TransactionStorageInterface $transactions */
        $transactions = new RedisTransactionStorage($user->id, 'slegtransaction', 50);

        $currency_name = $request['currency'];
        $currency = !empty($currency_name) ? UserWallet::getCurrencyCode($currency_name) : $user->currency;

        $wallet = SlergWallet::getForUser($user, $currency);

        $d_bonus = null;

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
            //todo bonus refund!
            $resp->balace = $d_bonus->getBalanceCents($wallet) / 100;
            return;
        }

        if ($wallet) {

            $tr = $transactions->transactionExist($request['bet_transaction_id']);

            if ($tr) {
//                $tr->refunded = true;
//                $tr->save();

//                $wallet->amount += $request['amount'];
                $wallet->addMoney($request['amount']);
                $wallet->save();
            }

            $wallet->refresh();
//            $result->BalanceAfter = intval($wallet->money);
//            $resp->result = $result;


            $transaction = [
                'user_id' => $user->id,
                'action' => $request['action'],
                'amount' => $request['amount'],
                'currency' => $request['currency'],
                'game_id' => $request['game_uuid'],
                'session_id' => $request['session_id'],
                'transaction_id' => $request['transaction_id'],
                'bet_transaction_id' => $request['bet_transaction_id'],
                'type' => $request['type'],
                'system' => 'sleg',
                'date' => now()->format('Y-m-d H:i:s'),
            ];

            $rfnd_transactions->addGenId($transaction);
            $rfnd_transactions->save();

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
