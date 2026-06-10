<?php

namespace App\Http\Controllers\Endpoints;

use App\Gameproviders\Transaction\TransactionStorageInterface;
use App\GamesProviders\GameData;
use App\GamesProviders\GamesProvider;
use App\GamesProviders\SoftSwiss\Exceptions\InsufficientFundsException;
use App\GamesProviders\SoftSwiss\Models\FsStatus;
use App\GamesProviders\SoftSwiss\Models\SoftSwissFs;
use App\GamesProviders\SoftSwiss\Models\SoftSwissGameSession;
use App\GamesProviders\SoftSwiss\Models\SoftSwissWallet;
use App\GamesProviders\SoftSwiss\SoftSwissProviderApi;
use App\Http\Controllers\Controller;
use App\Jobs\ExitGameJob;
use App\Models\Bonuses\FilthyFsWashOffBonus;
use App\Models\Bonuses\NonDepositBonus;
use App\Models\Bonuses\UserBonus;
use App\Models\GameTransaction;
use App\Models\Tournaments\TournamentGames\TournamentGame;
use App\MUser;
use App\PayoutTransaction;
use App\Redis\DeferredModelWriter;
use App\Redis\Transaction\RedisTransactionStorage;
use App\UserWallet;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;

/**
 * not connected
 * @deprecated
 */
class SoftSwissController extends Controller
{
    private SoftSwissProviderApi $_api;
    private string $_user_id = "";
    private $_data;
    private string $_game = "";
    private string $_game_id = "";
    private string $_game_currency = "";
    private int $_currency_id;

    private SoftSwissWallet $_wallet;
    private SoftSwissGameSession $_session;
    private MUser $_user;
    private GameData $_game_data;

    private array $_resp = [];

    /**
     * endpoint entry point
     * @param Request $request
     * @param $method
     * @return JsonResponse
     * @throws \Exception
     */
    public function endpoint(Request $request, $method)
    {
        try {
            $this->log($method, 'method');
            $this->log($request->header('X-REQUEST-SIGN'), 'X-REQUEST-SIGN');
            $this->log($request->getContent(), 'content');
            $this->_data = $request->json()->all();
            $this->log($this->_data, 'json data');
            $this->_api = new SoftSwissProviderApi();

            //check sign
            if ($request->header('X-REQUEST-SIGN') !== $this->_api->sign($request->getContent())) {
                return $this->error(403, "Request sign doesn't match.");
            }

            DB::beginTransaction();

            if (empty($this->_data)) return $this->error(400, 'empty data');
            $this->_resp = [];

            $data = $this->_data;

            if ($method === 'freespins') {
                $bonus_id = $data['issue_id'];
                /** @var UserBonus $d_bonus */
                $d_bonus = UserBonus::where('id', $bonus_id)->first();
                if ($d_bonus !== null) {
                    if ($d_bonus->spins_available <= 0) {
                        return $this->error(610, "freespins not set or already completed");
                    }
                    $this->_user_id = $d_bonus->user_id;
                    $user = MUser::find($d_bonus->user_id);
                    $this->_wallet = SoftSwissWallet::getForUser($user);
                    $d_bonus = UserBonus::convertByType($d_bonus);

                    if ($data['status'] === 'expired') $d_bonus->cancelBonus();
                    if ($data['status'] === 'played') $d_bonus->externFreeSpinsCompleted($this->_wallet, GamesProvider::GAME_SYSTEM_SOFT_SWISS, '', $this->_wallet->toFloatValue($data['total_amount']?? 0));

                    $this->_resp['balance'] = $d_bonus->getBalanceCents($this->_wallet);

                    DB::commit();
                    $this->log($this->_resp, 'response:');
                    return response()->json($this->_resp);
                }

                /** @var SoftSwissFs $spins */
                $spins = SoftSwissFs::where('spins_id', $data['issue_id'])->first();

                $this->_user_id = $spins->getUserId();
                $user = MUser::find($this->_user_id);
                $this->_wallet = SoftSwissWallet::getForUser($user);

                if ($spins === null) {
                    return $this->error(610, "freespins not set");
                }

                $spins->setStatus(FsStatus::Active);

                if ($data['status'] === 'expired') $spins->setStatus(FsStatus::Expired);
                if ($data['status'] === 'played') {
                    $spins->setStatus(FsStatus::Played);
                    $this->_wallet->addCents($data['total_amount']);
                }

                $spins->save();
                DB::commit();

                $this->_resp['balance'] = $this->_wallet->refresh()->getBalanceCents();

                $this->log($this->_resp, 'response:');
                return response()->json($this->_resp);
            }

            $this->_user_id = $data['user_id'];
            $this->_game = $data['game'];
            $this->_game_currency = $data['currency'];
            $currency_name = $this->_game_currency;


            if (preg_match('/^(bonus)?([0-9]+)([a-zA-z]{3,5})?$/', $this->_user_id, $match)) {
                $is_bonus_game = !empty($match[1]);
                $this->_user_id = $match[2];
                $currency_name = $match[3] ?? '';
            }

            /** @var MUser $user */
            $user = MUser::find($this->_user_id);
            if ($user === null) return $this->error(500, 'user not found');
            $this->_user = $user;

            $this->_currency_id = UserWallet::getCurrencyCode($currency_name);
            $this->_wallet = SoftSwissWallet::getForUser($user, $this->_currency_id);

            $this->_session = SoftSwissGameSession::find($this->_wallet->token);

            //if game was change
            if ($this->_game !== $this->_session?->game_id) {
                $money = $this->_wallet->getBalance();
                $softswiss_session = $this->_session;
                $softswiss_session->logout_date = date('Y-m-d H:i:s');
                $softswiss_session->logout_balance = $money;
                $softswiss_session->last_active_date = date('Y-m-d H:i:s');

                $currency = $user->getCurrencyName($this->_wallet->currency);

                DB::table('payout_transactions')
                    ->insert([
                        'user_id' => $user->id,
                        'hall_id' => $user->hall_id,
                        'date' => date('Y-m-d H:i:s'),
                        'sum' => ($money),
                        'status_id' => PayoutTransaction::STATUS_SOFTSWISS_RETURN,
                        'currency' => $currency,
                        'operation' => 'Выход с SoftSwiss'
                    ]);

                $softswiss_session->save();

                ExitGameJob::dispatch(GamesProvider::GAME_SYSTEM_SOFT_SWISS, $softswiss_session::class, $softswiss_session->id, $currency);

                $this->_wallet->session_id = vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex(random_bytes(16)), 4));

                $softSwissGameSession = new SoftSwissGameSession();
                $softSwissGameSession->user_id = $user->id;
                $softSwissGameSession->hall_id = UserWallet::currencyHallId($user->currency);
                $softSwissGameSession->currency = $user->currency;
                $softSwissGameSession->login_date = date('Y-m-d H:i:s');
                $softSwissGameSession->login_balance = $this->_wallet->getBalance();
                $softSwissGameSession->game_id = $this->_game;
                $softSwissGameSession->session_id = $this->_wallet->session_id;
                $softSwissGameSession->last_active_date = date('Y-m-d H:i:s');
                $softSwissGameSession->save();

                $this->_session = $softSwissGameSession;

                $this->_wallet->token = $softSwissGameSession->id;
                $this->_wallet->save();
            }

            $this->_game_id = $data['game_id']?? $this->_wallet->session_id;

            $this->_resp['game_id'] = $this->_game_id;

            $game_data = new GameData($user);
            $game_data->getDataFormGame(GamesProvider::GAME_SYSTEM_SOFT_SWISS, $this->_game, $this->_currency_id);
            $this->_game_data = $game_data;

            $d_bonus = null;

            if ($user->balance_mode == MUser::BALANCE_MODE_BONUS) {
                /** @var UserBonus $d_bonus */
                $d_bonus = UserBonus::findFirstActiveBonus($user->id, UserWallet::getCurrencyCode($currency_name));
                if ($d_bonus !== null) {
                    $this->_wallet->bonus_id = $d_bonus->getId();
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

            if ($method === 'play' && !empty($data['actions'])) {


                foreach ($data['actions'] as $action) {
                    match($action['action']) {
                        'bet' => $this->bet($action),
                        'win' => $this->win($action),
                    };
                }
            }
            if ($method === 'rollback' && !empty($data['actions'])) {
                foreach ($data['actions'] as $action) {
                    $this->rollback($action);
                }
            }

            if ($d_bonus !== null) {
                $this->_resp['balance'] = $d_bonus->getBalanceCents($this->_wallet);
            } else {
                Redis::del('bonus-status-' . $user->id);
                $this->_resp['balance'] = $this->_wallet->getBalanceCents();
            }

            DB::commit();

            $this->log($this->_resp, 'response:');
            return response()->json($this->_resp);

        } catch (InsufficientFundsException $e) {
            DB::rollBack();
            return $this->error(100, $e->getMessage(), $this->_wallet?->refresh()?->getBalanceCents()?? 0);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::channel('softswiss')->error($e->getMessage());
            Log::channel('softswiss')->error($e->getFile());
            Log::channel('softswiss')->error($e->getLine());
            Log::channel('softswiss')->error($e->getTraceAsString());
            return $this->error(500, $e->getMessage()); //here we have rollback
        }
    }

    /**
     * player makes bet
     * @throws InsufficientFundsException
     */
    private function bet($action): void
    {
        $this->log($action, 'bet action');

        $player_id = $this->_wallet->user_id;
        $gameId = $this->_game;
        $user = $this->_user;

        $currency = $this->_currency_id;
        $currency_name = $this->_game_currency;
        $float_amount = $this->_wallet->toFloatValue($action['amount']);

        $game_data = $this->_game_data;

        /** @var TransactionStorageInterface $transactions */
        $transactions = new RedisTransactionStorage($user->id, 'softswiss-bet', 200);

        // if transaction already processed
        $trans = $transactions->getByTransactionId($action['action_id']);
        if (!empty($trans)) {
            $this->log($trans['resp'], 'transaction already processed');
            $this->_resp['transactions'][] = $trans['resp'];
            return;
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

        if ($d_bonus && $d_bonus->isVegasAllowed() && $this->_wallet) {
           $this->log('bonus_mode');

            //set bonus id to prevent restoring prize games after bonus is complete
            $this->_wallet->bonus_id = $d_bonus->id;
            $this->_wallet->save();

            //check if the transaction is already refunded
            $to_torefund_trs = new RedisTransactionStorage($user->id, 'softswiss-torefund', 50);
            $refunded = !empty($to_torefund_trs->getByTransactionId($action['action_id']));

            if (!$refunded) {
                if ($d_bonus->getBalanceCents($this->_wallet) < $action['amount']) {
                    throw new InsufficientFundsException('Player has not enough funds to process an action');
                }

                if ($d_bonus->withdrawCents($action['amount'], $this->_wallet, GamesProvider::GAME_SYSTEM_SOFT_SWISS, $gameId) === false) {
                    throw new InsufficientFundsException('Player has not enough funds to process an action');
                }

                $d_bonus->actualizeVegas($gameId, $this->_wallet, GamesProvider::GAME_SYSTEM_SOFT_SWISS);
                $d_bonus->save();

                $status = $d_bonus->getStatusString($this->_wallet);
                if (empty($status)) {
                    Redis::del('bonus-status-' . $user->id);
                } else {
                    Redis::set('bonus-status-' . $user->id, $status);
                }
            }

            $transaction = [
                'user_id' => $user->id,
                'bonus' => $d_bonus->id,
                'action' => 'bet',
                'refunded' => $refunded,
                'amount' => $float_amount,
                'currency' => $currency_name,
                'game_id' => $this->_game,
                'session_id' => $this->_wallet->session_id,
                'transaction_id' => $action['action_id'],
                'type' => $this->_game_id, //round
                'system' => GamesProvider::GAME_SYSTEM_SOFT_SWISS,
                'date' => now()->format('Y-m-d H:i:s'),
            ];

            $s_trans = $transactions->addGenId($transaction);
            $transactions->save();

            $sw_transaction = [
                'action_id' => $action['action_id'],
                'tx_id' => $s_trans['id'],
                'processed_at' => now()->format('Y-m-d\TH:i:s\Z'),
                //bonus amount???
            ];

            $resp = $sw_transaction;
            $s_trans['resp'] = $resp;
            $transactions->update(['id' => $s_trans['id']], $s_trans);

            $this->_resp['transactions'][] = $sw_transaction;

            Redis::set($user->id.':last_bet_value', $float_amount);
            Redis::set($user->id.':last_bet_id', $s_trans['id']);

            $transaction['gen_id'] = $s_trans['id'];

            DeferredModelWriter::getForModel(GameTransaction::class)->store($transaction);
            return;
        }

        if ($this->_wallet) {
            $this->log('normal mode');
            Redis::del('bonus-status-' . $user->id);
            if ($this->_wallet->bonus_id != 0) {
                $this->_wallet->bonus_id = 0;
                $this->_wallet->save();
            }

            //check if the transaction is already refunded
            $to_torefund_trs = new RedisTransactionStorage($user->id, 'softswiss-torefund', 50);
            $refunded = !empty($to_torefund_trs->getByTransactionId($action['action_id']));

            if (!$refunded) {
                if ($action['amount'] < 0) {
                    throw new \Exception("amount < 0");
                }

                if ($this->_wallet->getBalanceCents() < $action['amount']) {
                    throw new InsufficientFundsException('Player has not enough funds to process an action');
                }

                $this->_wallet->remCents($action['amount']);

                if ($game_data->non_dep_bonus_id) {
                    /** @var NonDepositBonus $nd_bonus */
                    $nd_bonus = NonDepositBonus::getDone($player_id);
                    if ($nd_bonus) {
                        $nd_bonus->onUserBet($float_amount, $gameId);
                    } else {
                        $game_data->non_dep_bonus_id = 0;
                    }
                }

                if ($game_data->filthy_bonus_id) {
                    /** @var FilthyFsWashOffBonus $nd_bonus */
                    $nd_bonus = FilthyFsWashOffBonus::getDone($player_id);
                    if ($nd_bonus) {
                        $nd_bonus->onUserBet($float_amount, $gameId);
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
                    $tnt_res_game->onBet($float_amount, $this->_wallet, GamesProvider::GAME_SYSTEM_SOFT_SWISS, $gameId);
                    $tnt_res_game->save();
                }

                if ($user) {
                    if ($game_data->locked_money && $user->getWallet($currency)->getLockedMoney() > 0) {
                        $user->getWallet($currency)->remLockedMoney($float_amount * ($game_data->wager_prc / 100));
                    } else {
                        $game_data->locked_money = 0;
                        $game_data->save();
                    }
                    $user->save();
                    $user->getWallet($currency)->addBet($float_amount);
                }
                $this->_wallet->save();
            }

            $transaction = [
                'user_id' => $user->id,
                'bonus' => null,
                'action' => 'bet',
                'refunded' => $refunded,
                'amount' => $float_amount,
                'currency' => $currency_name,
                'game_id' => $this->_game,
                'session_id' => $this->_wallet->session_id,
                'transaction_id' => $action['action_id'],
                'type' => $this->_game_id, //round
                'system' => GamesProvider::GAME_SYSTEM_SOFT_SWISS,
                'date' => now()->format('Y-m-d H:i:s'),
            ];

            $s_trans = $transactions->addGenId($transaction);
            $transactions->save();

            $sw_transaction = [
                'action_id' => $action['action_id'],
                'tx_id' => $s_trans['id'],
                'processed_at' => now()->format('Y-m-d\TH:i:s\Z'),
            ];

            $resp = $sw_transaction;
            $s_trans['resp'] = $resp;
            $transactions->update(['id' => $s_trans['id']], $s_trans);

            $this->_resp['transactions'][] = $sw_transaction;

            Redis::set($user->id.':last_bet_value', $float_amount);
            Redis::set($user->id.':last_bet_id', $s_trans['id']);

            $transaction['gen_id'] = $s_trans['id'];

            DeferredModelWriter::getForModel(GameTransaction::class)->store($transaction);
            return;
        }
    }

    private function win($action): void
    {
        $this->log($action, 'win action');

        $player_id = $this->_wallet->user_id;
        $gameId = $this->_game;
        $user = $this->_user;

        $currency = $this->_currency_id;
        $currency_name = $this->_game_currency;
        $float_amount = $this->_wallet->toFloatValue($action['amount']);

        $game_data = $this->_game_data;

        /** @var TransactionStorageInterface $transactions */
        $transactions = new RedisTransactionStorage($user->id, 'softswiss-bet', 200);

        // if transaction already processed
        $trans = $transactions->getByTransactionId($action['action_id']);
        if (!empty($trans)) {
            $this->_resp['transactions'][] = $trans['resp'];
            return;
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

        if ($d_bonus && $d_bonus->isVegasAllowed() && $this->_wallet) {
            $this->log('bonus_mode');

            //check if the transaction is already refunded
            $to_torefund_trs = new RedisTransactionStorage($user->id, 'softswiss-torefund', 50);
            $refunded = !empty($to_torefund_trs->getByTransactionId($action['action_id']));

            if (!$refunded) {
                $d_bonus->depositCents($action['amount'], $this->_wallet, GamesProvider::GAME_SYSTEM_SOFT_SWISS, $gameId);
                $d_bonus->actualizeVegas($gameId, $this->_wallet, GamesProvider::GAME_SYSTEM_SOFT_SWISS);
                $d_bonus->save();

                $status = $d_bonus->getStatusString($this->_wallet);
                if (empty($status)) {
                    Redis::del('bonus-status-' . $user->id);
                } else {
                    Redis::set('bonus-status-' . $user->id, $status);
                }
            }

            $transaction = [
                'user_id' => $user->id,
                'bonus' => $d_bonus->id,
                'action' => 'win',
                'refunded' => $refunded,
                'amount' => $float_amount,
                'currency' => $currency_name,
                'game_id' => $this->_game,
                'session_id' => $this->_wallet->session_id,
                'transaction_id' => $action['action_id'],
                'type' => $this->_game_id, //round
                'system' => GamesProvider::GAME_SYSTEM_SOFT_SWISS,
                'date' => now()->format('Y-m-d H:i:s'),
                'last_bet_value' => Redis::get($user->id.':last_bet_value', 0),
                'last_bet_id' => Redis::get($user->id.':last_bet_id'),
            ];

            $s_trans = $transactions->addGenId($transaction);
            $transactions->save();

            $sw_transaction = [
                'action_id' => $action['action_id'],
                'tx_id' => $s_trans['id'],
                'processed_at' => now()->format('Y-m-d\TH:i:s\Z'),
                //bonus amount???
            ];

            $resp = $sw_transaction;
            $s_trans['resp'] = $resp;
            $transactions->update(['id' => $s_trans['id']], $s_trans);

            $this->_resp['transactions'][] = $sw_transaction;

            $transaction['gen_id'] = $s_trans['id'];

            DeferredModelWriter::getForModel(GameTransaction::class)->store($transaction);
            return;
        }

        if ($this->_wallet) {
            $this->log('normal mode');
            Redis::del('bonus-status-' . $user->id);

            if ($action['amount'] < 0) {
                throw new \Exception("amount < 0");
            }

            //check if the transaction is already refunded
            $to_torefund_trs = new RedisTransactionStorage($user->id, 'softswiss-torefund', 50);
            $refunded = !empty($to_torefund_trs->getByTransactionId($action['action_id']));

            if ($this->_wallet->bonus_id == 0 && !$refunded) {
                $this->_wallet->addCents($action['amount']);
                $this->_wallet->save();
                //add to wins table
                if ($user && $action['amount'] > 0) {
                    $user->getWallet($currency)->addWin($float_amount);
                }
            }
            $this->_wallet->save();
            $this->_wallet->refresh();

            $transaction = [
                'user_id' => $user->id,
                'bonus' => null,
                'action' => 'win',
                'refunded' => $refunded,
                'amount' => $float_amount,
                'currency' => $currency_name,
                'game_id' => $this->_game,
                'session_id' => $this->_wallet->session_id,
                'transaction_id' => $action['action_id'],
                'type' => $this->_game_id, //round
                'system' => GamesProvider::GAME_SYSTEM_SOFT_SWISS,
                'date' => now()->format('Y-m-d H:i:s'),
                'last_bet_value' => Redis::get($user->id.':last_bet_value', 0),
                'last_bet_id' => Redis::get($user->id.':last_bet_id'),
            ];

            $s_trans = $transactions->addGenId($transaction);
            $transactions->save();

            $sw_transaction = [
                'action_id' => $action['action_id'],
                'tx_id' => $s_trans['id'],
                'processed_at' => now()->format('Y-m-d\TH:i:s\Z'),
            ];

            $resp = $sw_transaction;
            $s_trans['resp'] = $resp;
            $transactions->update(['id' => $s_trans['id']], $s_trans);

            $this->_resp['transactions'][] = $sw_transaction;

            $transaction['gen_id'] = $s_trans['id'];

            DeferredModelWriter::getForModel(GameTransaction::class)->store($transaction);
            return;
        }
    }

    private function rollback($action): void
    {
        $this->log($action, 'rollback action');

        $player_id = $this->_wallet->user_id;
        $gameId = $this->_game;
        $user = $this->_user;

        $currency = $this->_currency_id;
        $currency_name = $this->_game_currency;

        /** @var TransactionStorageInterface $transactions */
        $transactions = new RedisTransactionStorage($user->id, 'softswiss-refund', 200);

        // if transaction already processed
        $trans = $transactions->getByTransactionId($action['action_id']);
        if (!empty($trans)) {
            $this->_resp[] = $trans['resp'];
            return;
        }

        //store all caches transactions to DB
        DeferredModelWriter::getForModel(GameTransaction::class)->writeToDb();

        // look for transaction
        /** @var GameTransaction $tr */
        $tr = GameTransaction::where('transaction_id', $action['original_action_id'])
            ->where('system', GamesProvider::GAME_SYSTEM_SOFT_SWISS)
            ->where('refunded', false)
            ->first();
        //
        if ($tr !== null) {
            $tr->refunded = true;
            // don't return bonus money
            if (empty($tr->bonus)) {
                if ($tr->action === 'bet') $this->_wallet->addMoney($tr->amount);
                if ($tr->action === 'win') $this->_wallet->remMoney($tr->amount);
            }
            $tr->save();
            $this->_wallet->save();
        } else {
            //store transaction that should be rolled back
            $to_torefund_trs = new RedisTransactionStorage($user->id, 'softswiss-torefund', 50);
            $to_torefund_trs->add([
                'transaction_id' => $action['original_action_id'],
                'refund_id' => $action['action_id'],
            ]);
        }

        $transaction = [
            'user_id' => $user->id,
            'bonus' => null,
            'action' => 'refund',
            'refunded' => false,
            'amount' => $tr?->amount ?? 0,
            'currency' => $currency_name,
            'game_id' => $this->_game,
            'session_id' => $this->_wallet->session_id,
            'transaction_id' => $action['action_id'],
            'type' => $this->_game_id, //round
            'system' => GamesProvider::GAME_SYSTEM_SOFT_SWISS,
            'date' => now()->format('Y-m-d H:i:s'),
        ];

        $s_trans = $transactions->addGenId($transaction);
        $transactions->save();

        $sw_transaction = [
            'action_id' => $action['action_id'],
            'tx_id' => $s_trans['id'],
            'processed_at' => now()->format('Y-m-d\TH:i:s\Z'),
        ];

        $resp = $sw_transaction;
        $s_trans['resp'] = $resp;
        $transactions->update(['id' => $s_trans['id']], $s_trans);

        $this->_resp['transactions'][] = $sw_transaction;

        DeferredModelWriter::getForModel(GameTransaction::class)->store($transaction);
    }

    /**
     * return error
     * @param int $code
     * @param string $message
     * @param int|null $balance
     * @return JsonResponse
     */
    private function error(int $code, string $message, ?int $balance = null): JsonResponse
    {
        DB::rollBack();
        $http_code = ($code >= 400 && $code < 600) ? $code : 500;
        $resp_data = [
            'code' => $code,
            'message' => $message,
        ];
        if ($balance !== null) $resp_data['balance'] = $balance;

        $this->log($resp_data, true);
        return response()->json($resp_data, $http_code);
    }

    private function log($data, string $tag = '', bool $error = false)
    {
        if (!$error) {
            Log::channel('softswiss')->info($tag . ' ' . var_export($data, true));
        } else {
            Log::channel('softswiss')->error($tag . ' ' . var_export($data, true));
        }
    }
}
