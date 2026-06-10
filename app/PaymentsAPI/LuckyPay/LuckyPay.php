<?php

namespace App\PaymentsAPI\LuckyPay;

use App\Helpers\Currency;
use App\Helpers\Hlog;
use App\MUser;
use App\Payin\Payin;
use App\PayoutTransaction;
use App\UserWallet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * @docs https://docs.google.com/document/d/1zluKr2CMibZ2y2PdGTs-MDnjpkLq4VSMJw-coDDf9ic/edit?tab=t.0#heading=h.eru23sdaqykm
 */
final class LuckyPay
{
    private string $key = "a81ee31a-d359-476b-ba9f-05581d99066b";
    private string $key2 = "d5e9191a-fb36-4320-bb7a-05fdfe48a7a3";
    private string $url = "https://luckypay.io";

    public function __construct(int $mode = 1)
    {
        if ($mode === 2) {
            $this->key = $this->key2;
        }
    }

    public function balance()
    {
        $m = "/api/v1/finance/balance";
        return $this->query($m, 'GET');
    }

    public function methods()
    {
        $m = "/api/v1/finance/payment_methods";
        return $this->query($m, 'GET');
    }

    public function deposit(string $id, float $amount, $method_id)
    {
        $m = "/api/v1/form/create";
        $data = [
            'order_data' => [
                'client_order_id' => $id,
                'order_side' => 'Buy',
                'amount' => number_format($amount, 2, '.', ''),
            ],
            'redirect_url' => route('pay.success'),
            'support_url' => route('landing'),
        ];
        if (!empty($method_id)) {
            if (explode(":", $method_id)[0] === '2') {
                $method_id = explode(":", $method_id)[1];
                $this->key = $this->key2;
            }
            $data['order_data']['payment_method_id'] = $method_id;
        }

        return $this->query($m, 'POST', $data);
    }

    public function payout(string $id, float $amount, string $method_id, string $account)
    {
        $m = "/api/v1/order/";
        $data = [
            'client_order_id' => $id,
            'order_side' => 'Sell',
            'amount' => number_format($amount, 2, '.', ''),
            'payment_method_id' => $method_id,
            'customer_payment_account' => $account,
        ];

        return $this->query($m, 'POST', $data);
    }

    public function orderInfo(string $id)
    {
        $m = "/api/v1/order/".$id;

        return $this->query($m, 'GET');
    }

    private function query($method, $httpMethod, array $data = [])
    {
        $url = $method;
        if (!preg_match('/^http.*$/', $method)) {
            $url = $this->url . $method;
        }

        $ch = curl_init();

        if (strtolower($httpMethod) === 'post') {

            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

        } else {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
            if (!empty($data)) {
                $url .= '?' . http_build_query($data);
            }
        }

        Log::channel('pay')->info(__CLASS__ . 'request: ' . $url);
        Log::channel('pay')->info(var_export($data, true));

        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'X-API-Key: ' . $this->key,
            'Content-Type: application/json',
        ]);

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);

        curl_setopt($ch, CURLOPT_USERAGENT, 'KrakenCassino');
        curl_setopt($ch, CURLOPT_TIMEOUT, 90);
        curl_setopt($ch, CURLOPT_MAXREDIRS, 10);

        curl_setopt($ch, CURLOPT_VERBOSE, true);

        $verbose = fopen('php://temp', 'w+');
        curl_setopt($ch, CURLOPT_STDERR, $verbose);

        $text = curl_exec($ch);
        $err = curl_error($ch);
        $resp_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $resp = json_decode($text, true);

        rewind($verbose);
        $verboseLog = stream_get_contents($verbose);

        Log::channel('pay')->info('curl verbose');
        Log::channel('pay')->info(htmlspecialchars($verboseLog));

        if ($err) {
            Log::channel('pay')->error($err);
        }

        Log::channel('pay')->info(__CLASS__ . 'resp: ');
        Log::channel('pay')->info('code:' . $resp_code);
        Log::channel('pay')->info($text);
        Log::channel('pay')->info(var_export($resp, true));
        return $resp;
    }

    public static function callback(Request $request)
    {
        Log::channel('pay')->info(__CLASS__ . ' callback deposit request');
        Log::channel('pay')->info(var_export($request->all(), true));

        $data = $request->all();

        if (!isset($data['payload']['client_order_id'])) {
            Log::channel('pay')->error('Order_Id field not set');
            return response('', 500);
        }

        $order_id = $data['payload']['client_order_id'];

        Log::channel('pay')->info('Start processing LuckyPay transaction with id ' . $order_id);

        DB::beginTransaction();
        try {
            /** @var PayoutTransaction $transaction */
            $transaction = PayoutTransaction::where('id', $order_id)
                ->whereIn('status_id', [
                    PayoutTransaction::STATUS_LUCKYPAY_PENDING,
                    PayoutTransaction::STATUS_LUCKYPAY_FAIL,
                ])
                ->lockForUpdate()
                ->first();

            if ($transaction?->getStatusId() === PayoutTransaction::STATUS_LUCKYPAY_FAIL) {
                Log::channel('pay')->error('Transaction with id ' . $order_id . ' in failed state found. Request: ' . PHP_EOL . var_export($request->all(), true));
            }

            if ($transaction === null) {
                DB::commit();
                if (PayoutTransaction::where('id', $order_id)
                        ->where('status_id', PayoutTransaction::STATUS_LUCKYPAY_SUCCESS)
                        ->first() !== null) {
                    Log::channel('pay')->error('Transaction with id ' . $order_id . ' is already complete found. Request: ' . PHP_EOL . var_export($request->all(), true));
                    return response('ok', 200);
                }
                Log::channel('pay')->error('Transaction with id ' . $order_id . ' is not found. Request: ' . PHP_EOL . var_export($request->all(), true));
                return response('transaction not found', 404);
            }

            // Find user which create this transaction.
            /** @var MUser $user $user */
            $user = MUser::where('id', $transaction->user_id)->first();

            if ($user === null) {
                Log::channel('pay')->error('User of the transaction is not found.');
                return response('user not found', 500);
            }

            if ($data['webhook_type'] !== 'buy_update') {
                return 'ok';
            }

            $pay_status = $data['payload']['status'];

            if ($pay_status === 'CanceledByTimeout' || $pay_status === 'CanceledByService') {
                Log::channel('pay')->error('status:' . $pay_status);
                $transaction->status_id = PayoutTransaction::STATUS_LUCKYPAY_FAIL;
                $transaction->coment = $pay_status;
                $transaction->save();
                DB::commit();
                return 'ok';
            }

            if ($pay_status !== 'Completed') {
                return 'ok';
            }

            $amount = $data['payload']['amount'];

            $currency = UserWallet::getCurrencyCode(mb_strtoupper($data['payload']['currency']?? 'RUB'));

            $transaction->fee = $amount * ($data['payload']['fee'] ?? 0) / 100;

            Payin::completeTransaction($user, $transaction, $amount, PayoutTransaction::STATUS_LUCKYPAY_SUCCESS, $currency);
        } catch (\Throwable $e) {
            Hlog::logException($e, 'pay');
        } finally {
            DB::commit();
        }


        return response('ok', 200);
    }
}
