<?php

namespace App\PaymentsAPI\CooCooPay;

use App\Helpers\Currency;
use App\Helpers\Hlog;
use App\Helpers\Hosts;
use App\MUser;
use App\Payin\Payin;
use App\PayoutTransaction;
use App\UserWallet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @docs https://api-reference.coocoopay.com/api-in-depth/payment/hosted
 */
final class CooCooPay
{
    private string $api_key = "HQpiZR8cafHvcvF758G96fauRiEdGQrMOlIAHO14UX3FJ4rP";
    private string $url = "https://api.coocoopay.com";

    private string $public_key = "-----BEGIN PUBLIC KEY-----
MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEA11FN+8yJrq6X+PD18h8A
x+RrTFxOIiYiuJ0fCFeEOR5mkFhUGuDl6iV8lNsrbOvgePoIz0RrBzx6mVireJJZ
TIp+wa1N3263IqAmAkUpRK+hGi0oxdSUgo0tbUEq64F/pX+5berF0/ZwHp9XpkC1
sNBARPetC74WqSJpiqfqNbH9Ghx/H6qVBW33XE/m49FJbvrbLBWC6ZuWf5RZdQEC
y2NAamQZ9iyoDPuwCCcsMuC5jR0eGSq3mg0p7yKd4UjqEQjniJFkLeE7/UUhk7yr
NDKicqrf7D5I5PprurUJI1LtFvs95vqAxlpekoZ5X9Hf34rxz6+fUIIOYTZSYhmN
QwIDAQAB
-----END PUBLIC KEY-----";

    public function deposit(string $tr_id, string $client_id, float $amount, string $currency, bool $trusted = false)
    {
        $method = "/api/v3/order/payment-form/create";
        $data = [
            'merchantOrderId' => $tr_id,
            'invoice' => [
                'amount' => number_format($amount, 2, '.', ''),
                'currency' => mb_strtolower($currency),
            ],
            'client' => [
                'id' => $client_id,
                'ip' => \request()->ip(),
            ],
            'attributes' => [
                'routingKey' => $trusted? 'TRUSTED' : 'UNTRUSTED',
            ],
            'returnUrl' => \route('pay.close-tab'),
            'webhookUrl' => Hosts::getFinHost($currency).'/api/pay/coocoopay/callback'
        ];

        return $this->query($method, $data);
    }

    public function payout(string $tr_id, float $amount, string $currency, string $card_number, string $user_id)
    {
        $method = "/api/v3/order/withdrawal/create";
        $data = [
            'merchantOrderId' => $tr_id,
            'amount' => number_format($amount, 2, '.', ''),
            'currency' => mb_strtolower($currency),
            'counterparty' => [
                'type' => 'cardNumber',
                'number' => $card_number,
            ],
            'client' => [
                'id' => $user_id,
            ],
            'webhookUrl' => Hosts::getFinHost($currency).'/api/pay/coocoopay/callback'
        ];

        return $this->query($method, $data);
    }

    public function status(string $tr_id)
    {
        $method = "/api/v3/order/get-status";
        $data = [
            'merchantOrderId' => $tr_id,
        ];

        return $this->query($method, $data);
    }

    public function checkSign(string $body, string $sign): bool
    {
        return openssl_verify($body, $sign, $this->public_key, \OPENSSL_ALGO_SHA512) === 1;
    }

    private function query($method, array $data = [])
    {
        $url = $this->url . $method;

        $ch = curl_init();

        $payload = json_encode($data, JSON_FORCE_OBJECT);

        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: ' . $this->api_key,
        ]);

        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);

        Log::channel('pay')->info(__CLASS__ . ' request: ' . $url);
        Log::channel('pay')->info(var_export($data, true));
        Log::channel('pay')->info(__CLASS__ . ' request body: ' . $url);
        Log::channel('pay')->info($payload);

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);

        curl_setopt($ch, CURLOPT_USERAGENT, 'KrakenCassino');
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_MAXREDIRS, 10);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

        curl_setopt($ch, CURLOPT_VERBOSE, true);

        $verbose = fopen('php://temp', 'w+');
        curl_setopt($ch, CURLOPT_STDERR, $verbose);

        $json = curl_exec($ch);
        $err = curl_error($ch);
        $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $resp = json_decode($json, true);

        rewind($verbose);
        $verboseLog = stream_get_contents($verbose);

        Log::channel('pay')->info('curl verbose');
        Log::channel('pay')->info(htmlspecialchars($verboseLog));

        if ($err) {
            Log::channel('pay')->error($err);
        }

        Log::channel('pay')->info(__CLASS__ . ' resp:');
        Log::channel('pay')->info('code:' . $httpcode);
        Log::channel('pay')->info($json);
        Log::channel('pay')->info(var_export($resp, true));
        return $resp;
    }

    public static function depositCallback(Request $request)
    {
        Log::channel('pay')->info(__CLASS__ . ' callback deposit request');
        Log::channel('pay')->info(var_export($request->all(), true));

        $data = $request->all();

        if ($data['type'] !== 'payin') {
            Log::channel('pay')->info('payout callback');
            return 'ok';
        }

        if (!isset($data['merchantOrderId'])) {
            Log::channel('pay')->error('merchantOrderId field not set');
            return response('', 500);
        }

        //check sign
        $api = new CooCooPay();
        if (!$api->checkSign($request->getContent(), base64_decode($request->header('Signature')))) {
            Log::channel('pay')->error('Check sign error signature:'.$request->header('Signature'). ' body:'.$request->getContent());
            return response('auth check error', 500);
        }

        Log::channel('pay')->info('Start processing CooCoo transaction with id ' . $data['merchantOrderId']);

        DB::beginTransaction();
        try {
            /** @var PayoutTransaction $transaction */
            $transaction = PayoutTransaction::where('id', $data['merchantOrderId'])
                ->whereIn('status_id', [
                    PayoutTransaction::STATUS_COOCOO_PENDING,
                    PayoutTransaction::STATUS_COOCOO_FAIL,
                ])
                ->lockForUpdate()
                ->first();

            if ($transaction?->getStatusId() === PayoutTransaction::STATUS_COOCOO_FAIL)  {
                Log::channel('pay')->error('Transaction with id ' . $data['merchantOrderId'] . ' in failed state found. Request: ' . PHP_EOL . var_export($request->all(), true));
            }

            if ($transaction === null) {
                if (PayoutTransaction::where('id', $data['merchantOrderId'])
                        ->where('status_id', PayoutTransaction::STATUS_COOCOO_SUCCESS)
                        ->first() !== null) {
                    Log::channel('pay')->error('Transaction with id ' . $data['merchantOrderId'] . ' is already complete found. Request: ' . PHP_EOL . var_export($request->all(), true));
                    return response('ok', 200);
                }
                Log::channel('pay')->error('Transaction with id ' . $data['merchantOrderId'] . ' is not found. Request: ' . PHP_EOL . var_export($request->all(), true));
                return response('transaction not found', 404);
            }

            // Find user which create this transaction.
            /** @var MUser $user $user */
            $user = MUser::where('id', $transaction->user_id)->first();

            if ($user === null) {
                Log::channel('pay')->error('User of the transaction is not found.');
                return response('user not found', 500);
            }

            if (in_array($data['status'], ['canceled', 'rejected'])) {
                Log::channel('pay')->error('status:' . $data['status']);
                $transaction->status_id = PayoutTransaction::STATUS_COOCOO_FAIL;
                $transaction->coment = $data['event'] ?? ' event error';
                $transaction->save();
                return 'ok';
            }

            if (!in_array($data['status'],['completed' ,'underpaid', 'overpaid', 'partially_completed'])) {
                return 'ok';
            }

            $amount = $data['transactions'][0]['authorized']['amount']?? $data['merchantTargetWallet']['amount'];

            $currency = UserWallet::getCurrencyCode(mb_strtoupper($data['merchantTargetWallet']['currency']));

            $transaction->fee = $amount - ($data['transactions'][0]['merchantWallet']['amount'] ?? $amount);

            Payin::completeTransaction($user, $transaction, $amount, PayoutTransaction::STATUS_COOCOO_SUCCESS, $currency);
        } catch (\Exception $e) {
            Hlog::logException($e, 'pay');
        } finally {
            DB::commit();
        }

        return response('ok', 200);
    }
}
