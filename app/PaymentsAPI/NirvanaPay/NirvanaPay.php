<?php

namespace App\PaymentsAPI\NirvanaPay;

use App\Helpers\Hlog;
use App\Helpers\Hosts;
use App\MUser;
use App\Payin\Payin;
use App\PayoutTransaction;
use App\Wallet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * @docs https://gitlab.com/NirvanaPayPlatform/doc
 * @docs https://f.nirvanapay.pro/read/documentation
 */
final class NirvanaPay
{
    private string $api_key = "Qvk2nTCkx9zPD9qQ7HkI";
    private string $pub_key = "ebd5c469-4567-4f2d-90a4-ee3f2099dc5f";
    private string $priv_key = "9f66eeb4-28a6-47fd-8e28-e307e77007d3";

    private string $url = "https://f.nirvanapay.pro";
    private string $payout_url = "https://api.nirvanapay.pro";

    public function getCurrencies()
    {
        $endpoint = $this->url . "/api/v2/currencies";
        $data = [
        ];

        return $this->query($endpoint, $data, 'get');
    }

    public function deposit(float $amount, string $tr_id, MUser $user, $token, $currency = 'RUB')
    {
        $endpoint = $this->url . "/api/v2/order";
        $data = [
            'amount' => $amount,
            'redirectURL' => route('pay.close-tab'),
            'siteName' => 'Kraken',
            'callbackURL' => Hosts::getFinHost($currency)."/api/pay/nirvana/callback?tr_id=$tr_id",
            'externalID' => $tr_id,
            'currency' => $currency,
            'tokenCode' => $token,
            'userInfo' => [
                'id' => $user->getId(),
                'ip' => $user->last_ip,
                'userAgent' => request()->userAgent(),
                'email' => $user->email?? '',
            ],
        ];

        return $this->query($endpoint, $data, 'post');
    }

    public function orderInfo(string $tr_id)
    {
        $endpoint = $this->url . "/api/v2/order";
        $data = [
            'externalId' => $tr_id,
        ];

        return $this->query($endpoint, $data, 'get');
    }

    public function payout(string $tr_id, float $amount, string $currency, string $method, string $receiver, array $requisites)
    {
        $endpoint = $this->payout_url . "/create/out";
        $data = [
            'clientID' => $tr_id,
            'amount' => $amount,
            'currency' => $currency,
            'token' => $method,
            'receiver' => $receiver,
            'callbackUrl' => Hosts::getFinHost($currency)."/api/pay/nirvana/payout",
            'extra' => $requisites['extra_info'],
        ];
        return $this->query($endpoint, $data, 'post');
    }

    public function payoutStatus(string $tr_id)
    {
        $endpoint = $this->payout_url . "/transaction/status";
        $data = [
            'clientID' => $tr_id,
        ];
        return $this->query($endpoint, $data, 'get');

    }

    public function balance()
    {
        $endpoint = $this->payout_url . "/client/balance";
        $data = [
        ];
        return $this->query($endpoint, $data, 'get');
    }

    private function query(string $url, array $data = [], string $httpMethod = 'post')
    {
        Log::channel('pay')->info(__CLASS__ . " query $url method $httpMethod data:", $data);

        if (mb_strtolower($httpMethod) === 'get') {
            $result = Http::asJson()->withHeaders([
                'ApiPublic' => $this->pub_key,
                'ApiPrivate' => $this->priv_key,
            ])->acceptJson()->get($url, $data)->body();
        } else {
            $result = Http::asJson()->withHeaders([
                'ApiPublic' => $this->pub_key,
                'ApiPrivate' => $this->priv_key,
            ])->acceptJson()->post($url, $data)->body();
        }

        Log::channel('pay')->info(__CLASS__ . " query $url response:". $result);
        return json_decode($result, true);
    }

    public static function depositCallback(Request $request)
    {
        Log::channel('pay')->info(__CLASS__ . " callback:", $request->all());
        Log::channel('pay')->info(__CLASS__ . " callback raw:". var_export($request->getContent(), true));
        $data = $request->all();
        Log::channel('pay')->info(__CLASS__ . " callback data:", $data);

        $targetUrl = "http://127.0.0.1:23106/pay/nirvana?tr_id=".$request->get('tr_id');
        $response = Http::withHeaders($request->headers->all())->send($request->method(), $targetUrl);
        Log::channel('pay')->info('nirvana callback response ' . var_export($response->body(), true));
        return response($response->body(), $response->status())->withHeaders($response->headers());

//        Log::channel('pay')->info(__CLASS__ . " callback:", $request->all());
//        $data = $request->all();
//
//        if (!isset($data['tr_id'])) {
//            Log::channel('pay')->error('transactionId field not set');
//            return response('', 404);
//        }
//
//        $order_id = $data['tr_id'];
//
//        Log::channel('pay')->info('Start processing NirvanaPay transaction with id ' . $order_id);
//
//        try {
//            DB::beginTransaction();
//
//            /** @var PayoutTransaction $transaction */
//            $transaction = PayoutTransaction::where('id', $order_id)
//                ->whereIn('status_id', [
//                    PayoutTransaction::STATUS_NIRVANA_PENDING,
//                    PayoutTransaction::STATUS_NIRVANA_FAIL,
//                ])
//                ->lockForUpdate()
//                ->first();
//
//            if ($transaction?->status_id === PayoutTransaction::STATUS_NIRVANA_FAIL) {
//                Log::channel('pay')->warning('Transaction with id ' . $order_id . ' already failed found. Processing');
//            }
//
//            if ($transaction === null) {
//                if (PayoutTransaction::where('id', $order_id)
//                        ->where('status_id', PayoutTransaction::STATUS_NIRVANA_SUCCESS)
//                        ->first() !== null) {
//                    Log::channel('pay')->error('Transaction with id ' . $order_id . ' is already complete found. Request: ' . PHP_EOL . var_export($request->all(), true));
//                    return response('ok', 200);
//                }
//                Log::channel('pay')->error('Transaction with id ' . $order_id . ' is not found. Request: ' . PHP_EOL . var_export($request->all(), true));
//                return response('transaction not found', 404);
//            }
//
//            // Find the user which created this transaction.
//            /** @var MUser $user $user */
//            $user = MUser::find($transaction->user_id);
//
//            if ($user === null) {
//                Log::channel('pay')->error('User of the transaction is not found.');
//                return response('user not found', 500);
//            }
//
//            $api = new NirvanaPay();
//            $orderInfo = $api->orderInfo($order_id)['data']?? [];
//            if (empty($orderInfo)) {
//                Log::channel('pay')->error('Order info not found. Request: ' . PHP_EOL . var_export($request->all(), true));
//                return response('order info not found', 500);
//            }
//
//            if ($orderInfo['status'] === 'ERROR') {
//                Log::channel('pay')->error('status:' . $orderInfo['status']);
//                $transaction->status_id = PayoutTransaction::STATUS_NIRVANA_FAIL;
//                $transaction->coment = $orderInfo['status'] ?? ' status error';
//                $transaction->save();
//                return 'ok';
//            }
//
//            if ($orderInfo['status'] !== 'SUCCESS') {
//                Log::channel('pay')->info('status:' . $orderInfo['status']);
//                $transaction->coment = $orderInfo['status'] ?? 'pending status';
//                $transaction->save();
//                return 'ok';
//            }
//
//            $amount = $orderInfo['amount'];
//
//            $currency = Wallet::getCurrencyCode($transaction->getCurrency());
//            $transaction->fee = $amount * 0.11;
////            if (!empty($data['ticket']['fee'])) {
////                $transaction->fee = $amount * $data['tx']['fee_pct'] / 100;
////            }
////            $transaction->card_number = $data['ticket']['requisite'] ?? '';
//
//            Payin::completeTransaction($user, $transaction, $amount, PayoutTransaction::STATUS_NIRVANA_SUCCESS, $currency);
//
//        } catch (\Throwable $e) {
//            Hlog::logException($e, 'pay');
//        } finally {
//            DB::commit();
//        }
//
//        return response('ok', 200);
    }

    public static function payoutCallback(Request $request) {
        Log::channel('pay')->info(__CLASS__ . " callback:", $request->all());
    }
}
