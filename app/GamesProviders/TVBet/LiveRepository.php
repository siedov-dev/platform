<?php


namespace App\GamesProviders\TVBet;

use App\Events\MoneySpent;
use App\GamesProviders\GamesProvider;
use App\GamesProviders\TVBet\TvBetWallet;
use App\Models\Bonuses\UserBonus;
use App\Models\BonusWagerPrc;
use App\Models\TvBet\TvBetPromocode;
use App\Models\TvBet\TvBetTransaction;
use App\Models\UserGameStat;
use App\MUser;
use App\PayoutTransaction;
use App\Wallet;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * @see https://tvbet.tv/en/technical-documentationhttps://tvbet.tv/en/technical-documentation
 */
class LiveRepository
{

    const TOKEN = 'b5b55978a5f95e92f60d789558fe60730f077c86a75a5fbf7ab866989c4d0303';

    public static function generate($id): string
    {

        $user = MUser::find($id);

        Log::info('TV-BET: generating token');
        $curr_name = $user->getCurrencyName();
        if ($curr_name === 'mBTC') {
            $curr_name = "µBT";
        }
        Log::info('TV-BET: currency ' . $curr_name);

        $generate = base64_encode($user->id) . ':' . base64_encode($user->login) . ':' . base64_encode($curr_name);
        Log::info('TV-BET: generated' . $generate);

        return $generate;

    }

    public static function getUserByToken($to): int
    {
        $to = explode(':', $to);
        return base64_decode($to[0]);
    }

    public static function getUserCurrency($to): string
    {
        $to = explode(':', $to);
        return base64_decode($to[2]);
    }

    public static function generateSi($data): string
    {
        $sign = md5(json_encode($data, JSON_UNESCAPED_UNICODE) . self::TOKEN);
        return base64_encode(hex2bin($sign));
    }

    public static function acceptanceBidPromocode($request, MUser $user, TvBetTransaction $tvBetTransaction)
    {

        Log::info(__CLASS__ . __METHOD__);
        Log::info(var_export($request->all(), true));

        $curr_name = LiveRepository::getUserCurrency($request->input('to'));
        if ($curr_name === 'µBT') {
            $curr_name = 'mBTC';
        }

        $currency = Wallet::getCurrencyCode($curr_name);

        $user = \App\MUser::where('id', '=', $user->id)->first();
        if (!$user) {
            $data = [
                'ti' => time(),
                'sc' => true,
                'cd' => 3,
                'er' => 'User not found.',
                'val' => null,
            ];
            $data['si'] = LiveRepository::generateSi($data);
            return $data;
        }
        $promocode = $request->input('pr');

//        $promocodes = \DB::connection('cms')->table('promocodes')->where('name', '=', $promocode)->first();

        /** @var TvBetPromocode $promocodes */
        $promocodes = TvBetPromocode::where('name', '=', $promocode)->first();
        $amount = json_decode($promocodes->amounts, true) ?? [];

        // not found
        if (!$promocodes || empty($amount[$curr_name])) {
            $data = [
                'ti' => time(),
                'sc' => true,
                'cd' => 20,
                'er' => 'Err',
                'val' => null,
            ];
            $data['si'] = LiveRepository::generateSi($data);
            return $data;
        }

        // check expired
        $expired = $promocodes->expired_in;
        $now = now();

        if ($now > $expired) {
            $data = [
                'ti' => time(),
                'sc' => true,
                'cd' => 22,
                'er' => utf8_encode("Error"),
                'val' => null,
            ];
            $data['si'] = LiveRepository::generateSi($data);
            return $data;
        }

        Log::info('checking use');

        // check use

        $checkUse = \DB::table('user_use_promocode')->where('promocode_id', '=', $promocodes->id)->where('user_id', '=', $user->id)->first();

        Log::info('check_use value: ' . var_export($checkUse, true));


        // check total count
        if ($promocodes->total_count == 0) {
            $data = [
                'ti' => time(),
                'sc' => true,
                'cd' => 22,
                'er' => utf8_encode("Error"),
                'val' => null,
            ];
            $data['si'] = LiveRepository::generateSi($data);

            return $data;
        }

        Log::info('total_count' . $promocodes->total_count);

        //check activation count
        if ($checkUse != null && $promocodes->activations <= 1) {
            $data = [
                'ti' => time(),
                'sc' => false,
                'cd' => 19,
                'er' => utf8_encode("Promo used"),
                'val' => null,
            ];
            $data['si'] = self::generateSi($data);

            return $data;
        }
        Log::info('activations < passed' . $promocodes->total_count);


        if ($checkUse != null && $promocodes->activations > 1 && $checkUse->cnt >= $promocodes->activations) {
            $data = [
                'ti' => time(),
                'sc' => false,
                'cd' => 19,
                'er' => utf8_encode("Promo used"),
                'val' => null,
            ];
            $data['si'] = self::generateSi($data);

            return $data;
        }

        Log::info('checking ids');
        $ids = json_decode($promocodes->assign) ?? [];

        if (!empty($ids)) {

            $ids = json_decode($promocodes->assign);
            $search = 0;

            foreach ($ids as $id) {
                $userCheck = \DB::table('m_users')->where('id', '=', $id)->first();
                if ($userCheck->id == $user->id) {
                    $search = 1;
                }

                if ($search == 1) {
                    break;
                }
            }

            if ($search == 0) {

                $data = [
                    'ti' => time(),
                    'sc' => true,
                    'cd' => 23,
                    'er' => '',
                    'val' => null,
                ];
                $data['si'] = LiveRepository::generateSi($data);

                return $data;
            }
        }

        Log::info('all good');

        // if all good

        // insert log use promocode users
        Log::info('check_use: ' . var_export($checkUse, true));

        if (empty($checkUse)) {
            \DB::table('user_use_promocode')->insert([
                'promocode_id' => $promocodes->id,
                'user_id' => $user->id,
                'cnt' => 1,
                'date_use' => date('Y-m-d G:i:s')
            ]);

            if ($promocodes->total_count > 0 && $promocodes->activations == 1) {
                $promocodes->decrement('total_count');
            }
        } else {
            \DB::table('user_use_promocode')->where('id', $checkUse->id)->update([
                'cnt' => $checkUse->cnt + 1,
                'date_use' => date('Y-m-d G:i:s')
            ]);
            if ($promocodes->total_count > 0 && ($checkUse->cnt + 1 >= $promocodes->activations)) {
                $promocodes->decrement('total_count');
            }
        }

        \DB::table('payout_transactions')->insert([
            'user_id' => $user->id,
            'hall_id' => $user->hall_id,
            'hall_credits' => '0.00',
            'date' => now(),
            'sum' => $amount[$curr_name],
            'currency' => $curr_name,
            'operation' => 'Активация промокода TVBET: ' . $promocodes->name,
            'card_number' => null,
            'status_id' => PayoutTransaction::STATUS_TVBET_PROMO,
        ]);


        $data = [
            'ti' => time(),
            'sc' => true,
            'cd' => 0,
            'er' => '',
            'val' => [
                'tid' => (string)$tvBetTransaction->id,
                'dt' => $tvBetTransaction->created_at->timestamp,
            ]
        ];

        $data['si'] = self::generateSi($data);

        $resp = json_encode($data, JSON_UNESCAPED_UNICODE + JSON_UNESCAPED_SLASHES);
        Log::info('TvBet resp promo resp: ' . $resp);
        return $data;
    }

    public static function acceptanceBid($request, MUser $user, TvBetTransaction $tvBetTransaction)
    {
        Log::info(__CLASS__ . __METHOD__);
        Log::info(var_export($request->all(), true));

        if ($request->input('pr')) {
            try {
                return self::acceptanceBidPromocode($request, $user, $tvBetTransaction);
            } catch (\Exception $e) {
                Log::error($e->getMessage());
            }
            die;
        }

        $curr_name = LiveRepository::getUserCurrency($request->input('to'));
        $sum = floatval($request->input('sm'));
        if ($curr_name === 'µBT') {
            $curr_name = 'mBTC';
            $sum = $sum / 1000;
        }

        $currency = Wallet::getCurrencyCode($curr_name);

        $wallet = TvBetWallet::getForUser($user, $currency);

        /** @var UserBonus $bonus */
        $bonus = null;
        $provider = 'tv-bet';
        $game_id = 'tvbetgame';

        //uncomment code below to enable bonuses
//        $g_bonus = UserBonus::getFirstActiveGameBonus($user->id, $provider, $game_id, $currency);
//        if ($g_bonus !== null) {
//            Log::info(__CLASS__ . "user $user->id found game bonus $g_bonus->id");
//            $bonus = $g_bonus;
//        } else {
//            /** @var UserBonus $d_bonus */
//            $d_bonus = UserBonus::findFirstActiveBonus($user->id, $currency);
//            if ($d_bonus && !$d_bonus->isGameAllowedByProvider($provider, $game_id)) {
//                Log::info(__CLASS__ . "user $user->id found bonus $d_bonus->id not allowed!");
//                $d_bonus = null;
//            }
//            if ($d_bonus) {
//                Log::info(__CLASS__ . "user $user->id found bonus $d_bonus->id");
//                $bonus = $d_bonus;
//            } else {
//                Log::info(__CLASS__ . "user $user->id bonus not found $provider $game_id $currency");
//
//            }
//        }
//
//        if ($bonus !== null) {
//            if (($bonus->getBalanceCents() / 100) - $sum < 0
//                || $bonus->withdrawCents($sum * 100, $wallet, $provider, $game_id)) {
//                $data = [
//                    'ti' => (integer)time(),
//                    'sc' => false,
//                    'cd' => 8,
//                    'er' => 'Insufficient funds on balance.',
//                    'val' => null
//                ];
//
//                $data['si'] = self::generateSi($data);
//
//                return $data;
//            }
//
//            DB::table('payout_transactions')->insert([
//                'user_id' => $user->id,
//                'hall_id' => $user->getWallet($currency)->getHallId(),
//                'hall_credits' => '0.00',
//                'date' => \Carbon\Carbon::now(),
//                'sum' => $sum,
//                'currency' => $request->input('cc'),
//                'operation' => 'Приём ставки ТВБЕТ bonus ' . $bonus->id,
//                'card_number' => null,
//                'status_id' => PayoutTransaction::STATUS_TVBET_BET,
//            ]);
//
//            $data = [
//                'ti' => time(),
//                'sc' => true,
//                'cd' => 0,
//                'er' => '',
//                'val' => [
//                    'tid' => (string)$insert,
//                    'dt' => (integer)DB::table('live_transactions')->where('id', '=', $insert)->first()->created_at
//                ]
//            ];
//
//            $data['si'] = self::generateSi($data);
//
//            $bonus->refreshBonusStatus($wallet, $provider, $game_id);
//            $bonus->save();
//
//            return $data;
//        }

        if ($user->getBalance($currency) - $sum < 0) {
            $data = [
                'ti' => time(),
                'sc' => false,
                'cd' => 8,
                'er' => 'Insufficient funds on balance.',
                'val' => null
            ];

            $data['si'] = self::generateSi($data);

            return $data;
        }

        $user->remMoney($sum, $currency)->save();

        if ($user->getWallet($currency)->getLockedMoney() > 0) {
            $conf = BonusWagerPrc::where('system',$provider)->where('game_id', $game_id)->first();
            $wager_prc = $conf? $conf->prc : 100;
            $user->getWallet($currency)->remLockedMoney($sum * ($wager_prc / 100));
        }

        DB::table('payout_transactions')->insert([
            'user_id' => $user->id,
            'hall_id' => $user->getWallet($currency)->getHallId(),
            'hall_credits' => '0.00',
            'date' => \Carbon\Carbon::now(),
            'sum' => $sum,
            'currency' => $request->input('cc'),
            'operation' => 'Приём ставки на площадке ТВБЕТ.',
            'card_number' => null,
            'status_id' => PayoutTransaction::STATUS_TVBET_BET,
        ]);

        event(new MoneySpent($user, $sum, $user->getCurrencyName()), 'TvBet bet');

        $user_game = new UserGameStat();
        $user_game->user_id = $user->id;
        $user_game->currency = $curr_name;
        $user_game->system = GamesProvider::GAME_SYSTEM_TV_BET;
        $user_game->session_id = $tvBetTransaction->bid;
        $user_game->game_id = '';
        $user_game->login_balance = 0;
        $user_game->logout_balance = 0;
        $user_game->balance_diff = 0;
        $user_game->login_date = now();
        $user_game->logout_date = now();
        $user_game->last_active_date = now();
        $user_game->outer_table = $tvBetTransaction->getTable();
        $user_game->outer_id = $tvBetTransaction->id;
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
                'tid' => (string)$tvBetTransaction->id,
                'dt' => $tvBetTransaction->created_at->timestamp,
            ]
        ];

        $data['si'] = self::generateSi($data);

        return $data;
    }

    public static function paymentBid($request, MUser $user, TvBetTransaction $tvBetTransaction)
    {

        $curr_name = LiveRepository::getUserCurrency($request->input('to'));
        $sum = floatval($request->input('sm'));
        if ($curr_name === 'µBT') {
            $curr_name = 'mBTC';
            $sum = $sum / 1000;
        }
        $currency = Wallet::getCurrencyCode($curr_name);

        if ($sum !== 0) {
//            $wallet = TvBetWallet::getForUser($user, $currency);

            /** @var UserBonus $bonus */
            $bonus = null;
            $provider = 'tv-bet';
            $game_id = 'tvbetgame';

            //bonus system
//            $g_bonus = UserBonus::getFirstActiveGameBonus($user->id, $provider, $game_id, $currency);
//            if ($g_bonus !== null) {
//                Log::info(__CLASS__ . "user $user->id found game bonus $g_bonus->id");
//                $bonus = $g_bonus;
//            } else {
//                /** @var UserBonus $d_bonus */
//                $d_bonus = UserBonus::findFirstActiveBonus($user->id, $currency);
//                if ($d_bonus && !$d_bonus->isGameAllowedByProvider($provider, $game_id)) {
//                    Log::info(__CLASS__ . "user $user->id found bonus $d_bonus->id not allowed!");
//                    $d_bonus = null;
//                }
//                if ($d_bonus) {
//                    Log::info(__CLASS__ . "user $user->id found bonus $d_bonus->id");
//                    $bonus = $d_bonus;
//                } else {
//                    Log::info(__CLASS__ . "user $user->id bonus not found $provider $game_id $currency");
//
//                }
//            }
//
//            if ($bonus !== null) {
//
//                $bonus->depositCents($sum * 100, $wallet, $provider, $game_id);
//
//                DB::table('payout_transactions')->insert([
//                    'user_id' => $user->id,
//                    'hall_id' => $user->getWallet($currency)->getHallId(),
//                    'hall_credits' => '0.00',
//                    'date' => \Carbon\Carbon::now(),
//                    'sum' => $sum,
//                    'currency' => $request->input('cc'),
//                    'operation' => 'TVBET win on bonus' . $bonus->id,
//                    'card_number' => null,
//                    'status_id' => PayoutTransaction::STATUS_TVBET_WIN,
//                ]);
//
//                $data = [
//                    'ti' => time(),
//                    'sc' => true,
//                    'cd' => 0,
//                    'er' => '',
//                    'val' => [
//                        'tid' => (string)$insert,
//                        'dt' => (integer)DB::table('live_transactions')->where('id', '=', $insert)->first()->created_at
//                    ]
//                ];
//
//                $data['si'] = self::generateSi($data);
//
//                $bonus->refreshBonusStatus($wallet, $provider, $game_id);
//                $bonus->save();
//
//                return $data;
//            }

//      $user = \DB::table('m_users')->where('id', '=', self::getUserByToken($request->input('to')))->first();
            DB::table('payout_transactions')->insert([
                'user_id' => $user->id,
                'hall_id' => $user->getWallet($currency)->getHallId(),
                'hall_credits' => '0.00',
                'date' => \Carbon\Carbon::now(),
                'sum' => $sum,
                'currency' => $request->input('cc'),
                'operation' => 'Зачисление выиграша ставки на площадке ТВБЕТ.',
                'card_number' => null,
                'status_id' => PayoutTransaction::STATUS_TVBET_WIN,
            ]);

            $user->getWallet($currency)->addMoney($sum)->save();
            event(new MoneySpent($user, -$sum, $user->getCurrencyName()), 'TvBet win');

            $user_game = new UserGameStat();
            $user_game->user_id = $user->id;
            $user_game->currency = $curr_name;
            $user_game->system = GamesProvider::GAME_SYSTEM_TV_BET;
            $user_game->session_id = $tvBetTransaction->bid;
            $user_game->game_id = '';
            $user_game->login_balance = 0;
            $user_game->logout_balance = 0;
            $user_game->balance_diff = 0;
            $user_game->login_date = now();
            $user_game->logout_date = now();
            $user_game->last_active_date = now();
            $user_game->outer_table = $tvBetTransaction->getTable();
            $user_game->outer_id = $tvBetTransaction->id;
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
                'tid' => (string)$tvBetTransaction->id,
                'dt' => $tvBetTransaction->created_at->timestamp,
            ]
        ];

        $data['si'] = self::generateSi($data);

        return $data;

    }

    public static function returnBid($request, MUser $user, TvBetTransaction $tvBetTransaction)
    {
        $curr_name = LiveRepository::getUserCurrency($request->input('to'));
        $sum = floatval($request->input('sm'));
        if ($curr_name === 'µBT') {
            $curr_name = 'mBTC';
            $sum = $sum / 1000;
        }
        if ($curr_name === 'BT') {
            $curr_name = 'mBTC';
            $sum = $sum / 1000;
        }
        $currency = Wallet::getCurrencyCode($curr_name);

//        $wallet = TvBetWallet::getForUser($user, $currency);

        /** @var UserBonus $bonus */
        $bonus = null;
        $provider = 'tv-bet';
        $game_id = 'tvbetgame';

//        $g_bonus = UserBonus::getFirstActiveGameBonus($user->id, $provider, $game_id, $currency);
//        if ($g_bonus !== null) {
//            Log::info(__CLASS__ . "user $user->id found game bonus $g_bonus->id");
//            $bonus = $g_bonus;
//        } else {
//            /** @var UserBonus $d_bonus */
//            $d_bonus = UserBonus::findFirstActiveBonus($user->id, $currency);
//            if ($d_bonus && !$d_bonus->isGameAllowedByProvider($provider, $game_id)) {
//                Log::info(__CLASS__ . "user $user->id found bonus $d_bonus->id not allowed!");
//                $d_bonus = null;
//            }
//            if ($d_bonus) {
//                Log::info(__CLASS__ . "user $user->id found bonus $d_bonus->id");
//                $bonus = $d_bonus;
//            } else {
//                Log::info(__CLASS__ . "user $user->id bonus not found $provider $game_id $currency");
//
//            }
//        }

//        if ($bonus !== null) {
//
//            $bonus->depositCents($sum * 100, $wallet, $provider, $game_id);
//
//            DB::table('payout_transactions')->insert([
//                'user_id' => $user->id,
//                'hall_id' => $user->getWallet($currency)->getHallId(),
//                'hall_credits' => '0.00',
//                'date' => \Carbon\Carbon::now(),
//                'sum' => $sum,
//                'currency' => $request->input('cc'),
//                'operation' => 'Возврат ставки TVBET on bonus' . $bonus->id,
//                'card_number' => null,
//                'status_id' => PayoutTransaction::STATUS_TVBET_REFUND,
//            ]);
//
//            $data = [
//                'ti' => time(),
//                'sc' => true,
//                'cd' => 0,
//                'er' => '',
//                'val' => [
//                    'tid' => (string)$tvBetTransaction->id,
//                    'dt' => $tvBetTransaction->created_at->timestamp,
//                ]
//            ];
//
//            $data['si'] = self::generateSi($data);
//
//            $bonus->refreshBonusStatus($wallet, $provider, $game_id);
//            $bonus->save();
//
//            return $data;
//        }

        $user->getWallet($currency)->addMoney($sum)->save();

        event(new MoneySpent($user, -$sum, $user->getCurrencyName()), 'TvBet refund');

        $user_game = new UserGameStat();
        $user_game->user_id = $user->id;
        $user_game->currency = $curr_name;
        $user_game->system = GamesProvider::GAME_SYSTEM_TV_BET;
        $user_game->session_id = $tvBetTransaction->bid;
        $user_game->game_id = '';
        $user_game->login_balance = 0;
        $user_game->logout_balance = 0;
        $user_game->balance_diff = 0;
        $user_game->login_date = now();
        $user_game->logout_date = now();
        $user_game->last_active_date = now();
        $user_game->outer_table = $tvBetTransaction->getTable();
        $user_game->outer_id = $tvBetTransaction->id;
        $user_game->total_bet = 0;
        $user_game->full_total_bet = 0;
        $user_game->real_total_bet = 0;
        $user_game->total_win = $sum;
        $user_game->full_total_win = $sum;
        $user_game->real_total_win = $sum;
        $user_game->status = UserGameStat::STATUS_OK;
        $user_game->save();

        \DB::table('payout_transactions')->insert([
            'user_id' => $user->id,
            'hall_id' => $user->getWallet($currency)->getHallId(),
            'hall_credits' => '0.00',
            'date' => \Carbon\Carbon::now(),
            'sum' => $request->input('sm'),
            'currency' => $request->input('cc'),
            'operation' => 'Возврат ставки на площадке ТВБЕТ.',
            'card_number' => null,
            'status_id' => PayoutTransaction::STATUS_TVBET_REFUND,
        ]);


        $data = [
            'ti' => time(),
            'sc' => true,
            'cd' => 0,
            'er' => '',
            'val' => [
                'tid' => (string)$tvBetTransaction->id,
                'dt' => $tvBetTransaction->created_at->timestamp,
            ]
        ];

        $data['si'] = self::generateSi($data);

        return $data;

    }


    public static function jackpotBid($request, $user, TvBetTransaction $tvBetTransaction)
    {
        $curr_name = LiveRepository::getUserCurrency($request->input('to'));
        $sum = floatval($request->input('sm'));
        if ($curr_name === 'µBT') {
            $curr_name = 'mBTC';
            $sum = $sum / 1000;
        }
        $currency = Wallet::getCurrencyCode($curr_name);

        if ($sum !== 0) {
//            $wallet = TvBetWallet::getForUser($user, $currency);

            /** @var UserBonus $bonus */
            $bonus = null;
            $provider = 'tv-bet';
            $game_id = 'tvbetgame';

//            $g_bonus = UserBonus::getFirstActiveGameBonus($user->id, $provider, $game_id, $currency);
//            if ($g_bonus !== null) {
//                Log::info(__CLASS__ . "user $user->id found game bonus $g_bonus->id");
//                $bonus = $g_bonus;
//            } else {
//                /** @var UserBonus $d_bonus */
//                $d_bonus = UserBonus::findFirstActiveBonus($user->id, $currency);
//                if ($d_bonus && !$d_bonus->isGameAllowedByProvider($provider, $game_id)) {
//                    Log::info(__CLASS__ . "user $user->id found bonus $d_bonus->id not allowed!");
//                    $d_bonus = null;
//                }
//                if ($d_bonus) {
//                    Log::info(__CLASS__ . "user $user->id found bonus $d_bonus->id");
//                    $bonus = $d_bonus;
//                } else {
//                    Log::info(__CLASS__ . "user $user->id bonus not found $provider $game_id $currency");
//
//                }
//            }

//            if ($bonus !== null) {
//
//                $bonus->depositCents($sum * 100, $wallet, $provider, $game_id);
//
//                DB::table('payout_transactions')->insert([
//                    'user_id' => $user->id,
//                    'hall_id' => $user->getWallet($currency)->getHallId(),
//                    'hall_credits' => '0.00',
//                    'date' => \Carbon\Carbon::now(),
//                    'sum' => $sum,
//                    'currency' => $request->input('cc'),
//                    'operation' => 'TVBET win on bonus' . $bonus->id,
//                    'card_number' => null,
//                    'status_id' => 52,
//                ]);
//
//                $data = [
//                    'ti' => time(),
//                    'sc' => true,
//                    'cd' => 0,
//                    'er' => '',
//                    'val' => [
//                        'tid' => (string)$insert,
//                        'dt' => (integer)DB::table('live_transactions')->where('id', '=', $insert)->first()->created_at
//                    ]
//                ];
//
//                $data['si'] = self::generateSi($data);
//
//                $bonus->refreshBonusStatus($wallet, $provider, $game_id);
//                $bonus->save();
//
//                return $data;
//            }

            DB::table('payout_transactions')->insert([
                'user_id' => $user->id,
                'hall_id' => $user->getWallet($currency)->getHallId(),
                'hall_credits' => '0.00',
                'date' => \Carbon\Carbon::now(),
                'sum' => $sum,
                'currency' => $request->input('cc'),
                'operation' => 'Зачисление джекпота TVBET.',
                'card_number' => null,
                'status_id' => PayoutTransaction::STATUS_TVBET_JACKPOT,
            ]);

            $user->getWallet($currency)->addMoney($sum)->save();
            event(new MoneySpent($user, -$sum, $user->getCurrencyName()), 'TvBet jackpot');

            $user_game = new UserGameStat();
            $user_game->user_id = $user->id;
            $user_game->currency = $curr_name;
            $user_game->system = GamesProvider::GAME_SYSTEM_TV_BET;
            $user_game->session_id = $tvBetTransaction->bid;
            $user_game->game_id = '';
            $user_game->login_balance = 0;
            $user_game->logout_balance = 0;
            $user_game->balance_diff = 0;
            $user_game->login_date = now();
            $user_game->logout_date = now();
            $user_game->last_active_date = now();
            $user_game->outer_table = $tvBetTransaction->getTable();
            $user_game->outer_id = $tvBetTransaction->id;
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
                'tid' => (string)$tvBetTransaction->id,
                'dt' => $tvBetTransaction->created_at->timestamp,
            ]
        ];

        $data['si'] = self::generateSi($data);

        return $data;
    }

}
