<?php

namespace App\Http\Controllers;

use App\Events\MoneySpent;
use App\GamesProviders\BetProduction\BetProductionApi;
use App\GamesProviders\GamesProvider;
use App\Models\BetProdTransactions;
use App\Models\BonusWagerPrc;
use App\Models\UserGameStat;
use App\MUser;
use App\PayoutTransaction;
use App\UserWallet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class BetProductionController extends Controller
{

    public function index($data, Request $request)
    {
        Log::info(__CLASS__.__METHOD__." api index:".var_export($data, true));
        Log::info(__CLASS__.__METHOD__." api data:".var_export($request->all(), true));
    }


    public function play(Request $request)
    {
        $meta = $this->createDefaultMetaTags();
        $api = new BetProductionApi();
        $token = '';
        if (Auth::check()) {
//            $token = $api->refreshToken(MUser::getAuth());
            $token = $api->getToken(MUser::getAuth());
        }
        $this->renderAdaptive('games.bet-production', ['token' => $token], request()->ajax() ? [] : ['layouts.main']);
        return $this->sendPreparedResponse();
    }

    public function getPlayerData(Request $request)
    {
        Log::info(__CLASS__.__METHOD__." data:".var_export($request->all(), true));
        if (empty($request->input('to'))) {
            Log::error('to field not set!');
            $data = [
                'ti' => time(),
                'sc' => false,
                'cd' => 5,
                'er' => 'Token not valid',
                'val' => null,
            ];
            return json_encode($data, JSON_UNESCAPED_UNICODE);
        }
        $api = new BetProductionApi();
        /** @var UserWallet $wallet */
        $wallet = $api->getUserWalletByToken($request->input('to'));
        if ($wallet === null) {
            Log::error('wallet not found');
            $data = [
                'ti' => time(),
                'sc' => false,
                'cd' => 4,
                'er' => 'An interaction token not found',
                'val' => null,
            ];
            return json_encode($data, JSON_UNESCAPED_UNICODE);
        }
        $user = $wallet->getUser();
        $currency = $wallet->getGameCurrencyName();
        $balance = $wallet->getBalance();

        if ($currency === 'mBTC') {
            $currency = 'BTC';
            $balance = $balance / 1000;
        }

        $data = [
            'ti' => time(),
            'sc' => true,
            'cd' => 0,
            'er' => '',
            'val' => [
                'pid' => '' . $user->id . $wallet->getCurrencyName(),
                'cc' => $currency,
                'to' => $api->getToken($user),
                'ts' => false,
                'bl' => (string)$balance,
            ]
        ];

        $data['si'] = $api->sign($data);
        $json = json_encode($data, JSON_UNESCAPED_UNICODE);
        Log::info('BetProd: get_user_data resp ' . var_export($data, true));
        Log::info('BetProd: get_user_data json ' . $json);

        return $json;
    }

    public function doBet(Request $request)
    {
        Log::info(__CLASS__ . __METHOD__);
        Log::info(var_export($request->all(), true));

        if (empty($request->input('to'))) {
            Log::error('to field not set!');
            $data = [
                'ti' => time(),
                'sc' => false,
                'cd' => 5,
                'er' => 'Token not valid',
                'val' => null,
            ];
            return json_encode($data, JSON_UNESCAPED_UNICODE);
        }
        $api = new BetProductionApi();
        /** @var UserWallet $wallet */
        $wallet = $api->getUserWalletByToken($request->input('to'));
        if ($wallet === null) {
            Log::error('wallet not found');
            $data = [
                'ti' => time(),
                'sc' => false,
                'cd' => 4,
                'er' => 'An interaction token not found',
                'val' => null,
            ];
            return json_encode($data, JSON_UNESCAPED_UNICODE);
        }
        $user = $wallet->getUser();
        $user_id = $user->id;

        $completedTr = BetProdTransactions::where('bid', $request->input('bid'))
            ->where('tt', $request->input('tt'))->first();
        if ($completedTr !== null) {
            $data = [
                'ti' => time(),
                'sc' => false,
                'cd' => 12,
                'er' => 'Transaction already exists',
                'val' => null,
            ];
            Log::info('BetProd transaction already exists');
            return json_encode($data, JSON_UNESCAPED_UNICODE);
        }

        $BetTr = new BetProdTransactions();
        $BetTr->bid = $request->input('bid');
        $BetTr->tt = $request->input('tt');
        $BetTr->ti = $request->input('ti');
        $BetTr->si = $request->input('si');
        $BetTr->to = $request->input('to');
        $BetTr->sm = $request->input('sm');
        $BetTr->pr = $request->input('pr');
        $BetTr->cc = $wallet->getCurrencyName();
        $BetTr->ed = json_encode($request->input('ed', '{}'));
        $BetTr->user_id = $user_id;
        $BetTr->save();

        $resp = match (intval($request->input('tt'))) {
            BetProdTransactions::TYPE_BET => $this->bet($request, $wallet, $BetTr),
            BetProdTransactions::TYPE_WIN => $this->win($request, $wallet, $BetTr),
            BetProdTransactions::TYPE_LOSE => $this->lose($request, $wallet, $BetTr),
            BetProdTransactions::TYPE_REFUND,
            BetProdTransactions::TYPE_CASHOUT,
            BetProdTransactions::TYPE_RETURN => $this->refund($request, $wallet, $BetTr),
            default =>
            [
                'ti' => time(),
                'sc' => false,
                'cd' => 15,
                'er' => 'Invalid type of transaction',
                'val' => null,
            ],
        };
        Log::info('BetProd resp:'. var_export($resp, true));
        return json_encode($resp);
    }

    public function bet(Request $request, UserWallet $wallet, BetProdTransactions $BetTransaction): array
    {
        Log::info(__CLASS__ . __METHOD__);
        Log::info(var_export($request->all(), true));

        $curr_name = $wallet->getCurrencyName();;
        $user = $wallet->getUser();
        $api = new BetProductionApi();

        $sum = floatval($request->input('sm'));
        if ($curr_name === 'µBT') {
            $curr_name = 'mBTC';
            $sum = $sum / 1000;
        }
        if ($curr_name === 'BTC') {
            $curr_name = 'mBTC';
            $sum = $sum * 1000;
        }

        $currency = UserWallet::getCurrencyCode($curr_name);

        $provider = 'bet-production';
        $game_id = '1';

        if ($wallet->getBalance() - $sum < 0) {
            $data = [
                'ti' => time(),
                'sc' => false,
                'cd' => 8,
                'er' => 'Insufficient funds on balance.',
                'val' => null
            ];

            $data['si'] = $api->sign($data);

            return $data;
        }

        $wallet->remMoney($sum)->save();

        //locked money process
        if ($wallet->getLockedMoney() > 0) {
            $conf = BonusWagerPrc::where('system', $provider)->where('game_id', $game_id)->first();
            $wager_prc = $conf? $conf->prc : 50;
            $wallet->remLockedMoney($sum * ($wager_prc / 100));
        }

        DB::table('payout_transactions')->insert([
            'user_id' => $user->id,
            'hall_id' => $user->getWallet($currency)->getHallId(),
            'hall_credits' => '0.00',
            'date' => \Carbon\Carbon::now(),
            'sum' => $sum,
            'currency' => $request->input('cc'),
            'operation' => 'Приём ставки BetProduction.',
            'card_number' => null,
            'status_id' => PayoutTransaction::STATUS_BETPROD_BET,
        ]);

        event(new MoneySpent($user, $sum, $user->getCurrencyName()), 'BetProduction bet');

        $user_game = new UserGameStat();
        $user_game->user_id = $user->id;
        $user_game->currency = $wallet->getCurrencyName();
        $user_game->system = GamesProvider::GAME_SYSTEM_BET_PRODUCTION;
        $user_game->session_id = $BetTransaction->bid;
        $user_game->game_id = '';
        $user_game->login_balance = 0;
        $user_game->logout_balance = 0;
        $user_game->balance_diff = 0;
        $user_game->login_date = now();
        $user_game->logout_date = now();
        $user_game->last_active_date = now();
        $user_game->outer_table = $BetTransaction->getTable();
        $user_game->outer_id = $BetTransaction->id;
        $user_game->total_bet = $sum;
        $user_game->full_total_bet = $sum;
        $user_game->real_total_bet = $sum;
        $user_game->total_win = 0;
        $user_game->full_total_win = 0;
        $user_game->real_total_win = 0;
        $user_game->status = UserGameStat::STATUS_OK;
        $user_game->save();

        $data = [
            'ti' => time(),
            'sc' => true,
            'cd' => 0,
            'er' => '',
            'val' => [
                'tid' => (string)$BetTransaction->id,
                'dt' => $BetTransaction->created_at->timestamp,
            ]
        ];

        $data['si'] = $api->sign($data);

        return $data;
    }

    public function win(Request $request, UserWallet $wallet, BetProdTransactions $BetTransaction): array
    {
        Log::info(__CLASS__ . __METHOD__);
        Log::info(var_export($request->all(), true));

        $curr_name = $wallet->getCurrencyName();;
        $user = $wallet->getUser();
        $api = new BetProductionApi();

        $sum = floatval($request->input('sm'));
        if ($curr_name === 'µBT') { //todo:: fix if btc is enabled again
            $curr_name = 'mBTC';
            $sum = $sum / 1000;
        }
        if ($curr_name === 'BTC') {
            $curr_name = 'mBTC';
            $sum = $sum * 1000;
        }

        $currency = UserWallet::getCurrencyCode($curr_name);

        $provider = 'bet-production';
        $game_id = '1';

        if ($sum !== 0) {

            DB::table('payout_transactions')->insert([
                'user_id' => $user->id,
                'hall_id' => $user->getWallet($currency)->getHallId(),
                'hall_credits' => '0.00',
                'date' => \Carbon\Carbon::now(),
                'sum' => $sum,
                'currency' => $request->input('cc'),
                'operation' => 'Зачисление выигрыша BetProduction.',
                'card_number' => null,
                'status_id' => PayoutTransaction::STATUS_BETPROD_WIN,
            ]);

            $wallet->addMoney($sum)->save();
            event(new MoneySpent($user, -$sum, $user->getCurrencyName()), 'BetProduction win');

            $user_game = new UserGameStat();
            $user_game->user_id = $user->id;
            $user_game->currency = $wallet->getCurrencyName();
            $user_game->system = GamesProvider::GAME_SYSTEM_BET_PRODUCTION;
            $user_game->session_id = $BetTransaction->bid;
            $user_game->game_id = '';
            $user_game->login_balance = 0;
            $user_game->logout_balance = 0;
            $user_game->balance_diff = 0;
            $user_game->login_date = now();
            $user_game->logout_date = now();
            $user_game->last_active_date = now();
            $user_game->outer_table = $BetTransaction->getTable();
            $user_game->outer_id = $BetTransaction->id;
            $user_game->total_bet = 0;
            $user_game->full_total_bet = 0;
            $user_game->real_total_bet = 0;
            $user_game->total_win = $sum;
            $user_game->full_total_win = $sum;
            $user_game->real_total_win = $sum;
            $user_game->status = UserGameStat::STATUS_OK;
            $user_game->save();
        }

        $data = [
            'ti' => time(),
            'sc' => true,
            'cd' => 0,
            'er' => '',
            'val' => [
                'tid' => (string)$BetTransaction->id,
                'dt' => $BetTransaction->created_at->timestamp,
            ]
        ];

        $data['si'] = $api->sign($data);

        return $data;
    }

    public function lose(Request $request, UserWallet $wallet, BetProdTransactions $BetTransaction): array
    {
        Log::info(__CLASS__ . __METHOD__);
        Log::info(var_export($request->all(), true));

        $curr_name = $wallet->getCurrencyName();;
        $user = $wallet->getUser();
        $api = new BetProductionApi();

        $sum = floatval($request->input('sm'));
        if ($curr_name === 'µBT') {
            $curr_name = 'mBTC';
            $sum = $sum / 1000;
        }
        if ($curr_name === 'BTC') {
            $curr_name = 'mBTC';
            $sum = $sum * 1000;
        }

        $currency = UserWallet::getCurrencyCode($curr_name);

        $provider = 'bet-production';
        $game_id = '1';

        $data = [
            'ti' => time(),
            'sc' => true,
            'cd' => 0,
            'er' => '',
            'val' => [
                'tid' => (string)$BetTransaction->id,
                'dt' => $BetTransaction->created_at->timestamp,
            ]
        ];

        $data['si'] = $api->sign($data);

        return $data;
    }

    public function refund(Request $request, UserWallet $wallet, BetProdTransactions $BetTransaction): array
    {
        Log::info(__CLASS__ . __METHOD__);
        Log::info(var_export($request->all(), true));

        $curr_name = $wallet->getCurrencyName();;
        $user = $wallet->getUser();
        $api = new BetProductionApi();

        $sum = floatval($request->input('sm'));
        if ($curr_name === 'µBT') {
            $curr_name = 'mBTC';
            $sum = $sum / 1000;
        }
        if ($curr_name === 'BTC') {
            $curr_name = 'mBTC';
            $sum = $sum * 1000;
        }

        $currency = UserWallet::getCurrencyCode($curr_name);

        $provider = 'bet-production';
        $game_id = '1';

        if ($sum === 0) {
            /** @var BetProdTransactions $win_transaction */
            $win_transaction = BetProdTransactions::where('bid', $BetTransaction->bid)
                ->where('tt', BetProdTransactions::TYPE_WIN)
                ->first();
            if (!$win_transaction !== null) {
                \DB::table('payout_transactions')->insert([
                    'user_id' => $user->id,
                    'hall_id' => $user->getWallet($currency)->getHallId(),
                    'hall_credits' => '0.00',
                    'date' => \Carbon\Carbon::now(),
                    'sum' => $win_transaction->sm,
                    'currency' => $request->input('cc'),
                    'operation' => 'Возврат выигрыша BetProduction.',
                    'card_number' => null,
                    'status_id' => PayoutTransaction::STATUS_BETPROD_REFUND,
                ]);
                $user->getWallet($currency)->remMoney($win_transaction->sm)->save();
            }
        }
        $user->getWallet($currency)->addMoney($sum)->save();

        event(new MoneySpent($user, -$sum, $user->getCurrencyName()), 'BetProduction refund');

        \DB::table('payout_transactions')->insert([
            'user_id' => $user->id,
            'hall_id' => $user->getWallet($currency)->getHallId(),
            'hall_credits' => '0.00',
            'date' => \Carbon\Carbon::now(),
            'sum' => $request->input('sm'),
            'currency' => $request->input('cc'),
            'operation' => 'Возврат ставки BetProduction.',
            'card_number' => null,
            'status_id' => PayoutTransaction::STATUS_BETPROD_REFUND,
        ]);

        $user_game = new UserGameStat();
        $user_game->user_id = $user->id;
        $user_game->currency = $wallet->getCurrencyName();
        $user_game->system = GamesProvider::GAME_SYSTEM_BET_PRODUCTION;
        $user_game->session_id = $BetTransaction->bid;
        $user_game->game_id = '';
        $user_game->login_balance = 0;
        $user_game->logout_balance = 0;
        $user_game->balance_diff = 0;
        $user_game->login_date = now();
        $user_game->logout_date = now();
        $user_game->last_active_date = now();
        $user_game->outer_table = $BetTransaction->getTable();
        $user_game->outer_id = $BetTransaction->id;
        $user_game->total_bet = 0;
        $user_game->full_total_bet = 0;
        $user_game->real_total_bet = 0;
        $user_game->total_win = $sum;
        $user_game->full_total_win = $sum;
        $user_game->real_total_win = $sum;
        $user_game->status = UserGameStat::STATUS_OK;
        $user_game->save();

        $data = [
            'ti' => time(),
            'sc' => true,
            'cd' => 0,
            'er' => '',
            'val' => [
                'tid' => (string)$BetTransaction->id,
                'dt' => $BetTransaction->created_at->timestamp,
            ]
        ];

        $data['si'] = $api->sign($data);

        return $data;
    }
}
