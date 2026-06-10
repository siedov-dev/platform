<?php

namespace App\PaymentsAPI\OneVision;

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
 * @docs https://docs.infinityspace.kz/
 */
final class OneVision
{
    private string $url = "https://api.getpayments.tech";
    private string $api_key = "";
    private string $secret_key = "";
    private string $merchant_id = "";
    private string $service_id = "";

    public function __construct(string $api_key = "", string $secret_key = "", string $service_id = "", string $merchant_id = "")
    {
        $this->api_key = $api_key;
        $this->secret_key = $secret_key;
        $this->service_id = $service_id;
        $this->merchant_id = $merchant_id;
    }

    public function deposit(MUser $user, float $amount, string $tr_id, string $currency = 'UZS', string $method = 'ecom', string $payment_type = 'pay' )
    {
        $endpoint = $this->url . "/payment/create";
        $data = [
            'amount' => $amount,
            'currency' => $currency,
            'order_id' => $tr_id,
            'description' => 'Kraken deposit',
            'payment_type' => $payment_type,
            'payment_method' => $method,
            'user_id' => (string)$user->getId(),
            'items' => [
                [
                    'merchant_id' => $this->merchant_id,
                    'service_id' => $this->service_id,
                    'merchant_name' => 'Kraken',
                    'name' => 'deposit',
                    'quantity' => 1,
                    'amount_one_pcs' => $amount,
                    'amount_sum' => $amount,
                ]
            ],
            'email' => $user->email,
            'payment_lifetime' => 600,
            'success_url' => route('pay.close-tab'),
            'failure_url' => route('pay.close-tab'),
            'callback_url' => Hosts::getFinHost($currency) . "/api/pay/onevision/callback",
        ];
        return $this->query($endpoint, $data, 'post');
    }

    public function payout(MUser $user, float $amount, string $tr_id, array $requisites = [], string $currency = 'UZS', string $method = 'ecom')
    {
        $endpoint = $this->url . "/payment/direct";
        $data = [
            'amount' => $amount,
            'currency' => $currency,
            'order_id' => $tr_id,
            'description' => 'Kraken payout',
            'payment_type' => 'payout',
            'payment_method' => $method,
            'user_id' => (string)$user->getId(),
            'email' => $user->email,
            'payment_lifetime' => 11600,
            'payout_data' => $requisites['payout_data'],
            'items' => [
                [
                    'merchant_id' => $this->merchant_id,
                    'service_id' => $this->service_id,
                    'merchant_name' => 'Kraken',
                    'name' => 'payout',
                    'quantity' => 1,
                    'amount_one_pcs' => $amount,
                    'amount_sum' => $amount,
                ]
            ],
//            'test_mode' => 1,
        ];
        return $this->query($endpoint, $data, 'post');
    }

    public function paymentStatus(string $tr_id)
    {
        $endpoint = $this->url . "/payment/status";
        $data = [
            'order_id' => $tr_id,
        ];
        return $this->query($endpoint, $data, 'post');
    }

    private function query(string $url, array $data = [], string $httpMethod = 'post')
    {
        Log::channel('pay')->info('OneVision request: ' . $url, $data);
        $dataJson = json_encode($data);
        $dataBase64 = base64_encode($dataJson);

        $sign = hash_hmac('sha512', $dataBase64, $this->secret_key);

        $request = [
            'data' => $dataBase64,
            'sign' => $sign,
        ];
        Log::channel('pay')->info('OneVision encoded request: ' . $url, $request);

        $token = base64_encode($this->api_key);

        $response = Http::acceptJson()->withToken($token)->asJson()->post($url, $request)->json();
        Log::channel('pay')->info('OneVision response: ', $response);
        return $response;
    }

    public static function depositCallback(Request $request)
    {
        Log::channel('pay')->info(__CLASS__ . " callback:", $request->all());
        $data = $request->all();
        if (!empty($data['data'])) {
            $data = json_decode(base64_decode($data['data']), true);
        }
        Log::channel('pay')->info(__CLASS__ . " callback data:", $data);

        if (!isset($data['order_id'])) {
            Log::channel('pay')->error('payment_id field not set');
            return response('order_id not found', 404);
        }

        $order_id = $data['order_id'];

        Log::channel('pay')->info('Start processing '.__CLASS__.' transaction with id ' . $order_id);

        try {
            DB::beginTransaction();

            /** @var PayoutTransaction $transaction */
            $transaction = PayoutTransaction::where('id', $order_id)
                ->whereIn('status_id', [
                    PayoutTransaction::STATUS_ONEVISION_PENDING,
                    PayoutTransaction::STATUS_ONEVISION_FAIL,
                ])
                ->lockForUpdate()
                ->first();

            if ($transaction?->status_id === PayoutTransaction::STATUS_ONEVISION_FAIL) {
                Log::channel('pay')->warning('Transaction with id ' . $order_id . ' already failed found. Processing');
            }

            if ($transaction === null) {
                if (PayoutTransaction::where('id', $order_id)
                        ->where('status_id', PayoutTransaction::STATUS_ONEVISION_SUCCESS)
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

            if ($data['operation_status'] === 'error') {
                Log::channel('pay')->error('status:' . $data['operation_status']);
                $transaction->status_id = PayoutTransaction::STATUS_ONEVISION_FAIL;
                $transaction->coment = ' status error';
                $transaction->save();
                return 'ok';
            }

            if ($data['operation_status'] !== 'success') {
                Log::channel('pay')->info('status:' . $data['operation_status']);
                $transaction->coment = $data['status'] ?? 'pending status';
                $transaction->save();
                return 'ok';
            }

            $amount = $data['amount'];

            $currency = Wallet::getCurrencyCode($transaction->getCurrency());
            $transaction->fee = 0;
            $transaction->card_number = $data['payer_info']['pan_masked'] ?? '';
            if ($transaction->method === 'UZS_ecom_ftd') {
                $transaction->fee = $amount * 0.17;
            }
            if ($transaction->method === 'UZS_ecom_std') {
                $transaction->fee = $amount * 0.15;
            }
            if ($transaction->method === 'UZS_ecom_ftd_p2p') {
                $transaction->fee = $amount * 0.055;
            }
            if ($transaction->method === 'UZS_ecom_std_p2p') {
                $transaction->fee = $amount * 0.055;
            }
            if ($transaction->method === 'KZT_ecom_ftd') {
                $transaction->fee = $amount * 0.056;
                if ($transaction->fee < 300) $transaction->fee = 100;
            }
            if ($transaction->method === 'KZT_ecom_std') {
                $transaction->fee = $amount * 0.042;
                if ($transaction->fee < 300) $transaction->fee = 100;
            }
            if ($transaction->method === 'KZT_mc_ftd') {
                $transaction->fee = $amount * 0.11;
            }
            if ($transaction->method === 'KZT_mc_std') {
                $transaction->fee = $amount * 0.10;
            }

            Payin::completeTransaction($user, $transaction, $amount, PayoutTransaction::STATUS_ONEVISION_SUCCESS, $currency);

        } catch (\Throwable $e) {
            Hlog::logException($e, 'pay');
        } finally {
            DB::commit();
        }

        return response('ok', 200);
    }
}
