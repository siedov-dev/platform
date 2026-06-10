<?php

namespace App\PaymentsAPI\ProcTech;

use App\ApplicationForPayment;
use App\Helpers\Hosts;
use App\Models\PaySystemsConfig;
use App\MUser;
use App\Payin\Payin;
use App\PaymentsAPI\Pspy\PspyApi;
use App\PayoutTransaction;
use App\UserWallet;
use App\Wallet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * @docs https://api-docs.proctech.io/?shell#payments-providers
 */
final class ProcTechApi
{
    private string $api_url = '';
    private string $private_key = '';
    private string $mid ="";

    public function __construct()
    {
        $conf = PaySystemsConfig::getSystemConfig(Payin::TYPE_PROCTECH);
        $this->api_url = $conf['ApiUrl'];
        $this->private_key = $conf['PrivateKey'];
        $this->mid = $conf['MID'];
    }

    public function balance(string $currency)
    {
        $payload = [
            'currency' => $currency,
        ];
        return $this->query($this->api_url . '/api/v1/balance/', 'GET', $payload);
    }

    public function deposit(PayoutTransaction $transaction, array $requisites)
    {
        $payload = [
            'product' => "Deposit",
            'amount' => intval($transaction->sum * 100),
            'currency' => $transaction->currency,
            'orderNumber' => (string)$transaction->id,
            'merchantUrl' => route('landing', [], true),
            'redirectSuccessUrl' => route('pay.success', [], true),
            'redirectFailUrl' => route('pay.fail', [], true),
            'callbackUrl' => Hosts::getFinHost($transaction->currency).'/api/pay/proctech/deposit',
            'customer' => [
                'email' => $requisites['email'],
                'phone' => preg_replace('/[^0-9]/', '', $requisites['phone'])
            ],
        ];
        return $this->query($this->api_url . '/api/v1/payments', 'POST', $payload);
    }

    public function confirmOtp(string $token, string $otp)
    {
        $payload = [
            'token' => $token,
            'otp' => $otp,
        ];
        return $this->query($this->api_url . '/api/v1/otp', 'POST', $payload);
    }

    public function resentOtp(string $token)
    {
        $payload = [
        ];
        return $this->query($this->api_url . '/api/v1/otp-resend?token='.$token, 'GET', $payload);
    }

    public function sign(array $data): string
    {
        $hash_str = strlen($data['token']) . $data['token'] .
            strlen($data['type']) . $data['type'] .
            strlen($data['status']) . $data['status'] .
            strlen($data['extraReturnParam']) . $data['extraReturnParam'] .
            strlen($data['orderNumber']) . $data['orderNumber'] .
            strlen($data['amount']) . $data['amount'] .
            strlen($data['currency']) . $data['currency'] .
            strlen($data['gatewayAmount']) . $data['gatewayAmount'] .
            strlen($data['gatewayCurrency']) . $data['gatewayCurrency'] . $this->private_key;
        return md5($hash_str);
    }

    public function payout(ApplicationForPayment $tr, string $method, array $requisites)
    {
        $payload = [
            'amount' => intval($tr->suma * 100),
            'currency' => Wallet::currencyName($tr->currency),
            'orderNumber' => 'o'.$tr->id,
            'merchantUrl' => route('landing', [], true),
            'redirectSuccessUrl' => route('pay.success', [], true),
            'redirectFailUrl' => route('pay.fail', [], true),
            'callbackUrl' => route('pay.success', [], true),
            'customer' => [
                'email' => $requisites['email'],
                'phone' => preg_replace('/[^0-9]/', '', $requisites['phone']),
                'address' => $requisites['address'],
                'ip' => request()->ip(),
            ],
        ];
        return $this->query($this->api_url . '/api/v1/payouts', 'POST', $payload);
    }

    public function getPaymentStatus(string $token)
    {
        return $this->query($this->api_url . '/api/v1/payments/'.$token, 'GET');
    }

    private function query($url, $httpMethod, array $data = [])
    {
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
            'Content-Type: application/json',
            'Authorization: Bearer ' . $this->private_key,
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

    public static function depositCallback(Request $request)
    {
        Log::channel('pay')->info(__CLASS__ . 'callback');
        Log::channel('pay')->info(var_export($request->all(), true));

        $data = $request->all();

        if (!isset($data['token'])) {
            Log::channel('pay')->error('token field not set');
            return response('', 500);
        }
        if ($data['type'] !== 'pay') {
            Log::channel('pay')->info('payment type:' . $data['type']);
            return 'ok';
        }

        Log::channel('pay')->info('Start processing proctech transaction with token ' . $data['token']);
        $api = new ProcTechApi();
        if ($data['signature'] !== $api->sign($data)) {
            Log::channel('pay')->error('Invalid signature');
            return response('invalid signature', 403);
        } else {
            Log::channel('pay')->info('Signature is valid');
        }

        /** @var PayoutTransaction $transaction */
        $transaction = PayoutTransaction::where('o_tr_id', $data['token'])
            ->whereIn('status_id', [
                PayoutTransaction::STATUS_PROCTECH_PENDING,
                PayoutTransaction::STATUS_PROCTECH_FAIL,
            ])
            ->first();

        if ($transaction === null) {
            if (PayoutTransaction::where('o_tr_id', $data['token'])
                    ->where('status_id', PayoutTransaction::STATUS_PROCTECH_SUCCESS)
                    ->first() !== null) {
                Log::channel('pay')->error('Transaction with o id ' . $data['token'] . ' is already complete found. Request: ' . PHP_EOL . var_export($request->all(), true));
                return response('ok', 200);
            }
            Log::channel('pay')->error('Transaction with o id ' . $data['token'] . ' is not found. Request: ' . PHP_EOL . var_export($request->all(), true));
            return response('transaction not found', 404);
        }

        // Find user which create this transaction.
        /** @var MUser $user $user */
        $user = MUser::where('id', $transaction->user_id)->first();

        if ($user === null) {
            Log::channel('pay')->error('User of the transaction is not found.');
            return response('user not found', 500);
        }

        if (in_array($data['status'], ['declined'])) {
            Log::channel('pay')->error('status:' . $data['status']);
            $transaction->status_id = PayoutTransaction::STATUS_PROCTECH_FAIL;
            $transaction->save();
            return response('ok', 200);
        }

        if ($data['status'] !== 'approved') {
            return 'ok';
        }

        $amount = $data['amount'] / 100;

        $currency = UserWallet::getCurrencyCode(strtoupper($data['currency']));
        $transaction->fee = $data['commission']?? $amount * 0.115;

        Payin::completeTransaction($user, $transaction, $amount, PayoutTransaction::STATUS_PROCTECH_SUCCESS, $currency);

        return response('ok', 200);
    }
}
