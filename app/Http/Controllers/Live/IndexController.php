<?php

namespace App\Http\Controllers\Live;

use App\Models\TvBet\TvBetPromocode;
use App\Models\TvBet\TvBetTransaction;
use App\MUser;
use App\UserWallet;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\GamesProviders\TVBet\LiveRepository;
use DB;
use Illuminate\Support\Facades\Redis;

class IndexController extends Controller
{
    public function frame()
    {
        $meta = $this->createDefaultMetaTags();
        $meta->title = 'Ставки на TvBet 🎲 Live ставки, быстрые игры Кено в казино 🐙 Кракен';
        $meta->keywords = 'Ставки на TvBet, Live ставки, быстрые игры Кено, Кракен';
        $meta->description = 'Ставки на TvBet, Live ставки, быстрые игры Кено, Кракен';

        $this->renderAdaptive('games.tv-bet-frame', [], request()->ajax() ? [] : ['layouts.main']);
        return $this->sendPreparedResponse();
    }

    public function index()
    {
        if (Auth::id()) {
            Redis::del('bonus-status-' . Auth::id());
        }
        $this->renderAdaptive('games.tv-bet', [], request()->ajax() ? [] : ['layouts.main']);
        return $this->sendPreparedResponse();
    }

    public function get_user_data(Request $request)
    {
        Log::info('TV-BET: get_user_data request ' . var_export($request->all(), true));
        $user_id = LiveRepository::getUserByToken($request->input('to'));

        /** @var MUser $user */
        $user = MUser::find($user_id); // \DB::table('m_users')->where('id', '=', $user_id)->first();
        if ($user === null) return '';

        $currency = $user->getWallet()->getGameCurrencyName();

        $balance = $user->getWallet()->getBalance();
        if ($currency === 'mBTC') {
            $currency = 'µBT';
            $balance = $balance * 1000;
        }

        $data = [
            'ti' => time(),
            'sc' => true,
            'cd' => 0,
            'er' => '',
            'val' => [
                'uid' => (string)'tv' . $user->id . $user->getWallet()->getCurrencyName(),
                'cc' => $currency,
                'to' => \App\GamesProviders\TVBet\LiveRepository::generate($user->id),//todo: generate real token
                'ts' => false,
                'bl' => (string)$balance,
            ]
        ];

        $ci = LiveRepository::generateSi($data);

        $data['si'] = $ci;
        $json = json_encode($data, JSON_UNESCAPED_UNICODE);
        Log::info('TV-BET: get_user_data resp ' . var_export($data, true));
        Log::info('TV-BET: get_user_data json ' . $json);

        return $json;
    }


    public function promo_info(Request $request)
    {
        Log::info(__CLASS__ . __METHOD__);
        Log::info(var_export($request->all(), true));

        // user
        $user_id = LiveRepository::getUserByToken($request->input('to'));

        $user = \App\MUser::where('id', '=', $user_id)->first();

        $curr_name = LiveRepository::getUserCurrency($request->input('to'));
        if ($curr_name === 'µBT') {
            $curr_name = 'mBTC';
        }

        Log::info('promo currency' . $curr_name);

        $currency = UserWallet::getCurrencyCode($curr_name);

        // get promocode
        $promocode = $request->input('pr');

        /** @var TvBetPromocode $promocodes */
        $promocodes = TvBetPromocode::where('name', '=', $promocode)->first();
        $amount = json_decode($promocodes['amounts'] ?? '', true) ?? [];

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
            return response()->json($data);
        }
        Log::info('promo found ' . $promocodes->name);

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
            return response()->json($data);
        }
        Log::info('not expired ' . $promocodes->expired_in);


        $checkUse = \DB::table('user_use_promocode')->where('promocode_id', '=', $promocodes->id)->where('user_id', '=', $user->id)->first();

        Log::info('total_count ' . $promocodes->total_count);

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

            return response()->json($data);
        }

        Log::info('activations ' . $promocodes->activations);

        //check activation count
        if ($checkUse != null && $promocodes->activations <= 1) {
            $data = [
                'ti' => time(),
                'sc' => false,
                'cd' => 19,
                'er' => utf8_encode("Promo used"),
                'val' => null,
            ];
            $data['si'] = LiveRepository::generateSi($data);

            return response()->json($data);
        }

        if ($checkUse != null && $promocodes->activations > 1 && $checkUse->cnt >= $promocodes->activations) {
            $data = [
                'ti' => time(),
                'sc' => false,
                'cd' => 19,
                'er' => utf8_encode("Promo used"),
                'val' => null,
            ];
            $data['si'] = LiveRepository::generateSi($data);

            return response()->json($data);
        }
        Log::info('not used ');

        // check assign to users
        $ids = json_decode($promocodes->assign) ?? [];

        if (!empty($ids)) {

            if (!in_array($user_id, $ids) || $user === null) {

                $data = [
                    'ti' => time(),
                    'sc' => true,
                    'cd' => 23,
                    'er' => '',
                    'val' => null,
                ];
                $data['si'] = LiveRepository::generateSi($data);

                return response()->json($data);
            }
        }

        $data = [
            'ti' => time(),
            'sc' => true,
            'cd' => 0,
            'er' => "",
            'val' => [
                'am' => $amount[$curr_name],
                'mc' => '0',
                'gts' => [],
                'bt' => 0,
                'ec' => 0,
            ]
        ];

        $data['si'] = LiveRepository::generateSi($data);

        $resp = json_encode($data);
        Log::info('TvBet resp promo resp: ' . $resp);
        return response()->json($data);
    }

    public function payment(Request $request)
    {
        Log::info(__CLASS__ . __METHOD__);
        Log::info(var_export($request->all(), true));

        $completedTr = TvBetTransaction::where('bid', $request->input('bid'))
            ->where('tt', $request->input('tt'))->first();
        if ($completedTr !== null) {
            $data = [
                'ti' => time(),
                'sc' => false,
                'cd' => 12,
                'er' => 'Transaction already exists',
                'val' => null,
            ];
            Log::info('tv-bet transaction already exists');
            return response()->json($data);
        }

        $tvBetTr = new TvBetTransaction();
        $tvBetTr->bid = $request->input('bid');
        $tvBetTr->tt = $request->input('tt');
        $tvBetTr->ti = $request->input('ti');
        $tvBetTr->si = $request->input('si');
        $tvBetTr->to = $request->input('to');
        $tvBetTr->sm = $request->input('sm');
        $tvBetTr->pr = $request->input('pr');
        $tvBetTr->ed = json_encode($request->input('ed', '{}'));

        $user_id = LiveRepository::getUserByToken($request->input('to'));

        $tvBetTr->cc = LiveRepository::getUserCurrency($request->input('to'));
        $tvBetTr->user_id = $user_id;
        $tvBetTr->save();

        $user = MUser::find($user_id);

        $resp = match (intval($request->input('tt'))) {
            TvBetTransaction::TYPE_BET => LiveRepository::acceptanceBid($request, $user, $tvBetTr),
            TvBetTransaction::TYPE_PAYMENT => LiveRepository::paymentBid($request, $user, $tvBetTr),
            TvBetTransaction::TYPE_REFUND, TvBetTransaction::TYPE_CANCEL => LiveRepository::returnBid($request, $user, $tvBetTr),
            TvBetTransaction::TYPE_JACKPOT => LiveRepository::jackpotBid($request, $user, $tvBetTr),
            default =>
                [
                    'ti' => time(),
                    'sc' => false,
                    'cd' => 17,
                    'er' => 'Invalid type of transaction',
                    'val' => null,
                ]
            ,
        };
        Log::info('tv-bet resp:'. var_export($resp, true));
        return json_encode($resp);
    }

    public function payment_info(Request $request)
    {
        Log::info(__CLASS__ . __METHOD__);
        Log::info(var_export($request->all(), true));


        /** @var TvBetTransaction $transaction */
        $transaction = TvBetTransaction::where('id', '=', $request->input('tid'))->first();

        if (!$transaction) {
            $data = [
                'ti' => time(),
                'sc' => false,
                'cd' => 14,
                'er' => 'Транзакция не найдена.',
                'val' => null
            ];
            $data['si'] = LiveRepository::generateSi($data);
            return response()->json($data);
        }


        $data = [
            'ti' => (integer)time(),
            'sc' => true,
            'cd' => 0,
            'er' => "",
            'val' => [
                'dt' => (integer)$transaction->created_at,
                'bid' => (integer)$transaction->bid,
                'tt' => (integer)$transaction->tt,
                'uid' => (string)LiveRepository::getUserByToken($request->input('to')),
                'sm' => (string)$transaction->sm,
            ]
        ];

        $data['si'] = LiveRepository::generateSi($data);

        return response()->json($data);
    }

    public function refresh_token(Request $request)
    {
        Log::notice('refresh token');
        Log::notice($request);
        $user_id = LiveRepository::getUserByToken($request->input('to'));

        $data = [
            'ti' => time(),
            'sc' => true,
            'cd' => 0,
            'er' => '',
            'val' => [
                'to' => $request->input('to'),//todo: generate real token
            ]
        ];

        $ci = LiveRepository::generateSi($data);

        $data['si'] = $ci;

        return response()->json($data);
    }


}
