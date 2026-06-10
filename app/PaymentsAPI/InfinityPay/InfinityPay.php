<?php

namespace App\PaymentsAPI\InfinityPay;

use App\Helpers\Hlog;
use App\Helpers\Hosts;
use App\MUser;
use App\Payin\Payin;
use App\PaymentsAPI\NirvanaPay\NirvanaPay;
use App\PayoutTransaction;
use App\Wallet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * @docs https://test-api.infinity-pay.io/redoc#section/Varianty-integracii/2.-Payment-Request-API-Integration-(H2H)
 */
final class InfinityPay
{
    private string $merchant_token = "ZQrA1U67I6NZbYjGY4OB9nK-6DmOtAMSDlKVIt1FBWI";
    private string $merchant_secret = "9-uWQPn8YMCSC6ViqWIRj6n3jCh63EecdHTXgqDwJYk";
    private string $url = "https://api.infinity-pay.io";

    public function methods(string $currency = 'RUB')
    {
        $endpoint = $this->url . "/v1/methods";
        $data = [
            'currency' => $currency,
        ];
        return $this->query($endpoint, $data, 'get');
    }

    public function currencies()
    {
        $endpoint = $this->url . "/v1/currency";
        $data = [
        ];
        return $this->query($endpoint, $data, 'get');
    }

    public function deposit(string $tr_id, float $amount, string $currency = 'RUB', string $method = 'CIS')
    {
        $endpoint = $this->url . "/v1/pay_in/create";
        $data = [
            'type' => $method,
            'amount' => intval($amount * 100),
            'm_order_id' => $tr_id,
            'currency_id' => 1, // RUB
            'success_url' => route('pay.close-tab'),
            'fail_url' => route('pay.close-tab'),
            'callback_url' => Hosts::getFinHost($currency)."/api/pay/infinity/callback",
            'uniqueness' => true,
        ];
        return $this->query($endpoint, $data, 'post');
    }

    public function payout(string $tr_id, float $amount, int $pay_method_id, array $requisites)
    {
        $endpoint = $this->url . "/v1/pay_out/create";
        $data = [
            'type' => 'CIS',
            'amount' => intval($amount * 100),
            'm_order_id' => $tr_id,
            'pay_method_id' => $pay_method_id,
            'payment_credentials' => $requisites['payment_credentials']
        ];

        return $this->query($endpoint, $data, 'post');
    }

    public function payoutStatus(string $order_id)
    {
        $endpoint = $this->url . "/v1/pay_out/order/$order_id";
        $data = [
            'search_by_merchant_order_id' => true,
        ];

        return $this->query($endpoint, $data, 'get');
    }

    private function query(string $url, array $data = [], string $httpMethod = 'post')
    {
        Log::channel('pay')->info(__CLASS__ . " query $url method $httpMethod data:", $data);

        $timestamp = floor(microtime(true) * 1000);
        $rawSignature = "$this->merchant_token:$timestamp";
        $signature = hash_hmac('sha256', $rawSignature, $this->merchant_secret);
        $request = Http::asJson()->withHeaders([
            "x-signature" => $signature,
            "x-timestamp" => $timestamp,
            "x-merchant-token" => $this->merchant_token,
        ])->acceptJson();

        if (mb_strtolower($httpMethod) === 'get') {
            $result = $request->get($url, $data)->body();
        } else {
            $result  = $request->post($url, $data)->body();
        }

        Log::channel('pay')->info(__CLASS__ . " query $url response:". $result);
        return json_decode($result, true);
    }

    public static function depositCallback(Request $request)
    {
        Log::channel('pay')->info(__CLASS__ . " callback:", $request->all());
        $data = $request->all();

        if (!isset($data['m_order_id'])) {
            Log::channel('pay')->error('m_order_id field not set');
            return response('', 404);
        }

        $order_id = $data['m_order_id'];

        Log::channel('pay')->info('Start processing Infinity transaction with id ' . $order_id);

        try {
            DB::beginTransaction();

            /** @var PayoutTransaction $transaction */
            $transaction = PayoutTransaction::where('id', $order_id)
                ->whereIn('status_id', [
                    PayoutTransaction::STATUS_INFINITY_PENDING,
                    PayoutTransaction::STATUS_INFINITY_FAIL,
                ])
                ->lockForUpdate()
                ->first();

            if ($transaction?->status_id === PayoutTransaction::STATUS_INFINITY_FAIL) {
                Log::channel('pay')->warning('Transaction with id ' . $order_id . ' already failed found. Processing');
            }

            if ($transaction === null) {
                if (PayoutTransaction::where('id', $order_id)
                        ->where('status_id', PayoutTransaction::STATUS_INFINITY_SUCCESS)
                        ->first() !== null) {
                    Log::channel('pay')->error('Transaction with id ' . $order_id . ' is already complete found. Request: ' . PHP_EOL . var_export($request->all(), true));
                    return response('ok', 200);
                }
                Log::channel('pay')->error('Transaction with id ' . $order_id . ' is not found. Request: ' . PHP_EOL . var_export($request->all(), true));
                return response('transaction not found', 404);
            }

            // Find the user which created this transaction.
            /** @var MUser $user $user */
            $user = MUser::find($transaction->user_id);

            if ($user === null) {
                Log::channel('pay')->error('User of the transaction is not found.');
                return response('user not found', 500);
            }

            if ($data['status'] === 'failed' || $data['status'] === 'expired' || $data['status'] === 'rejected') {
                Log::channel('pay')->error('status:' . $data['status']);
                $transaction->status_id = PayoutTransaction::STATUS_INFINITY_FAIL;
                $transaction->coment = $data['status'] ?? ' status error';
                $transaction->save();
                return 'ok';
            }

            if ($data['status'] !== 'success') {
                Log::channel('pay')->info('status:' . $data['ticket']['status']);
                $transaction->coment = $data['status'] ?? 'pending status';
                $transaction->save();
                return 'ok';
            }

            $amount = $data['order_amount'] / 100;

            $currency = Wallet::getCurrencyCode($transaction->getCurrency());
            $transaction->fee = $amount * 0.11;
//            if (!empty($data['ticket']['fee'])) {
//                $transaction->fee = $amount * $data['tx']['fee_pct'] / 100;
//            }
//            $transaction->card_number = $data['ticket']['requisite'] ?? '';

            Payin::completeTransaction($user, $transaction, $amount, PayoutTransaction::STATUS_INFINITY_SUCCESS, $currency);

        } catch (\Throwable $e) {
            Hlog::logException($e, 'pay');
        } finally {
            DB::commit();
        }

        return response('ok', 200);
    }

    public static function payoutCallback(Request $request) {
        Log::channel('pay')->info(__CLASS__ . " callback:", $request->all());
    }
}
