<?php

namespace App\PaymentsAPI\SredaPay;

use App\Models\PaySystemsConfig;
use App\MUser;
use App\Payin\Payin;
use App\PayoutTransaction;
use App\UserWallet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class SredaPayVisa
{
//    private string $url = "https://secure-test.sredapay.kz";
    private string $url = "https://secure.sredapay.kz/";
    private string $api_key = "6501b410-673e-013b-7c15-02420a000789";
    private string $secret_key = "d5b9bf673038ad8abeb71bc8dd4bdb6402d06f1f43482848";

    const RESP_OK = 0;
    const RESP_RETRY = 1;
    const RESP_ERROR = 2;

    public function __construct()
    {
        $conf = PaySystemsConfig::getSystemConfig(Payin::TYPE_SREDAPAY);
        $this->api_key = $conf['api_key_visa']??'';
        $this->secret_key = $conf['secret_key_visa']??'';
    }

    public function sign($data): string
    {
        return hash_hmac('md5', $data, $this->secret_key);
    }

    public function checkSign($data, $sign): bool
    {
        return hash_hmac('md5', $data, $this->secret_key) === $sign;
    }

    public function pay(float $amount, string $tr_id, string $currency = 'KZT')
    {
        $method = '/pay';

        $data = [
            'api_key' => $this->api_key,
            'expiration' => now()->addMinutes(10)->timezone('Europe/Moscow')->format('Y-m-d H:i'),
            'amount' => $amount,
            'currency' => $currency,
            'description' => 'Krakencassino deposit',
            'reference' => $tr_id,
            'success_url' => route('pay.success', [], true),
            'failure_url' => route('pay.fail', [], true),
            'lang' => app()->getLocale(),
            'params' => [
                'flag_get_url' => 1,
            ]
        ];

        return $this->query($method, 'post', $data);
    }

    private function query($method, $httpMethod, array $data = [], $payout = false)
    {
        $url = $this->url . $method;

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_HTTPHEADER, [
        ]);

        $payload = [];
        $payload['data'] = base64_encode(json_encode($data));
        $sign = $this->sign($payload['data']);
        $payload['sign'] = $sign;

        if ( strtolower($httpMethod) === 'post') {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($payload));

        } else {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
            if (!empty($data)) {
                $url .= '&' . http_build_query($data);
            }
        }

        Log::channel('pay')->info('SredaPay request: '.$url);
        Log::channel('pay')->info(var_export($data, true));
        Log::channel('pay')->info(var_export($payload, true));

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION,  true);
        curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);

        curl_setopt($ch, CURLOPT_USERAGENT, 'KrakenCassino');
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        curl_setopt($ch, CURLOPT_MAXREDIRS, 10);

        curl_setopt($ch, CURLOPT_VERBOSE, true);

        $verbose = fopen('php://temp', 'w+');
        curl_setopt($ch, CURLOPT_STDERR, $verbose);

        $text = curl_exec($ch);
        $err = curl_error($ch);
        $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $resp = json_decode($text, true);

        rewind($verbose);
        $verboseLog = stream_get_contents($verbose);

//        Log::channel('pay')->info('curl verbose');
//        Log::channel('pay')->info(htmlspecialchars($verboseLog));

        if ($err) {
            Log::channel('pay')->error($err);
        }

        Log::channel('pay')->info('SredaPay resp:');
        Log::channel('pay')->info('code:'.$httpcode);
        Log::channel('pay')->info($text);
        Log::channel('pay')->info(var_export($resp, true));
        return $resp;
    }

    public static function payCallback(Request $request)
    {
        Log::channel('pay')->info('SredaPay visa callback request');
        Log::channel('pay')->info(var_export($request->all(), true));
        Log::channel('pay')->info('SredaPay visa callback сontent');
        Log::channel('pay')->info(var_export($request->getContent(), true));
        $api = new SredaPay();

        $data = json_decode(base64_decode($request->data), true);
        Log::channel('pay')->info('SredaPay visa data');
        Log::channel('pay')->info(var_export($data, true));

        if (empty($data['reference'])) {
            Log::channel('pay')->error('reference field is not set');
            return response('no reference field', 500);
        }
        $api = new SredaPayVisa();
        if ($api->checkSign($request->data, $request->sign)) {
            return response('wrong sign', 403);
        }

        Log::channel('pay')->info('Start processing SredaPay transaction with id ' . $data['reference']);

        // Find transaction.
        /** @var PayoutTransaction $transaction */
        $transaction = PayoutTransaction::where('id', $data['reference'])
            ->where('status_id', PayoutTransaction::STATUS_SREDAPAY_PENDING)
            ->first();

        if ($transaction === null) {
            $transaction = PayoutTransaction::where('id', $data['reference'])
                ->where('status_id', PayoutTransaction::STATUS_SREDAPAY_SUCCESS)
                ->first();
            if ($transaction !== null) {
                Log::channel('pay')->error('Transaction with id ' . $data['reference'] . ' is already completed. Request: ' . PHP_EOL . var_export($request->all(), true));
                return response('OK', 200);
            }
            Log::channel('pay')->error('Transaction with id ' . $data['reference'] . ' is not found. Request: ' . PHP_EOL . var_export($request->all(), true));
            return response('transaction not found', 404);
        }

        // Find user which create this transaction.
        /** @var MUser $user $user */
        $user = MUser::where('id', $transaction->user_id)->first();

        if ($user === null) {
            Log::channel('pay')->error('User of the transaction is not found.');
            return response('user not found', 404);
        }

        if ($data['status'] == 99 || $data['status'] == 98) {
            $transaction->status_id = PayoutTransaction::STATUS_SREDAPAY_FAIL;
            $transaction->coment = $data['status_name'];
            $transaction->save();
            Log::channel('pay')->info('status failed');
            return response('OK', 200);
        }
        if ($data['status'] != 3) {
            $transaction->coment = $data['status_name'];
            return response('OK', 200);
        }
        $amount = $data['amount']?? $transaction->sum;

        $currency = UserWallet::getCurrencyCode($data['currency']?? $transaction->currency);

        $transaction->fee = $data['commission']?? 0;

        if (!empty($data['card_number'])) {
            $transaction->card_number = $data['card_number'];
        }

        Payin::completeTransaction($user, $transaction, $amount, PayoutTransaction::STATUS_SREDAPAY_SUCCESS, $currency);
        return response('OK', 200);
    }
}
