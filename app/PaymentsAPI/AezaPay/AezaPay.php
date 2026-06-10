<?php

namespace App\PaymentsAPI\AezaPay;

use App\Helpers\Hosts;
use App\MUser;
use App\Payin\Payin;
use App\PaymentsAPI\Paycos\Paycos;
use App\PayoutTransaction;
use App\UserWallet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AezaPay
{
    private string $key = "00a5456b5ea2b9774c0c";
    private string $url = "https://api.aezapay.io/api/";
    private string $payout_key = "e78ac35cddd25c0a3efa";

    public function __construct(bool $payout = false)
    {
        if ($payout) $this->key = $this->payout_key;
    }

    public function balance(string $currency = 'RUB')
    {
        $method = 'v1/balance';
        $data = [
            'currency' => $currency,
        ];
        return $this->query($method, 'get', $data);
    }

    public function getPayment($payment_token)
    {
        $method = 'v2/merchant/payment/'.$payment_token;
        $data = [
        ];

        return $this->query($method, 'get', $data);
    }

    public function initPay(string $orderId, int $cents, array $customer, string $currency = 'RUB', $p_method = 'default')
    {
        Log::channel('pay')->info('AezaPay init pay key:'.$this->key);
        $method = 'v1/init/pay';
        $data = [
            'product' => 'karkencassino refill',
            'amount' => $cents,
            'currency' => $currency,
            'orderNumber' => $orderId,
            "platform" => "web",
            'locale' => Auth::user()?->locale?? 'en',
            'redirectSuccessUrl' => action('PayController@success'),
            'redirectFailUrl' => action('PayController@fail'),
            'callbackUrl' => Hosts::getFinHost($currency). '/api/pay/aeza',
            'extraReturnParam' => $p_method,
        ];

        if (!empty($customer['_token'])) unset($customer['_token']);
        if (!empty($customer['payment-method'])) unset($customer['payment-method']);
        if (!empty($customer['amount'])) unset($customer['amount']);

        if (!empty($customer['mop_type'])) {
            $data['mop_type'] = $customer['mop_type'];
            unset($customer['mop_type']);
        }
        if (!empty($customer['payer_address'])) {
            $data['payer_address'] = $customer['payer_address'];
            unset($customer['payer_address']);
        }

        if (!empty($customer)) $data['customer'] = $customer;
//        $data['customer']['client_id'] = "".Auth::id();
//        $data['customer']['email'] = Auth::user()->email;
        $data['browser_info']['user_agent'] = request()->userAgent();
        return $this->query($method, 'post', $data);
    }

    public function h2hConfirm($url, $data = [])
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'authorization: Bearer ' . $this->key,
            'Content-Type: application/json',
        ]);

        Log::channel('pay')->info('AezaPay h2h request: '.$url);

        curl_setopt($ch, CURLOPT_HEADER, false);

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);

        curl_setopt($ch, CURLOPT_USERAGENT, 'KrakenCassino');
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_MAXREDIRS, 10);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

        curl_setopt($ch, CURLOPT_VERBOSE, true);
        $verbose = fopen('php://temp', 'w+');
        curl_setopt($ch, CURLOPT_STDERR, $verbose);

        $text = curl_exec($ch);
        $err = curl_error($ch);
        $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $resp = json_decode($text, true);
        if ($err) {
            Log::channel('pay')->error($err);
        }

        rewind($verbose);
        $verboseLog = stream_get_contents($verbose);

        Log::channel('pay')->info('curl verbose');
        Log::channel('pay')->info(htmlspecialchars($verboseLog));

        Log::channel('pay')->info('AezaPay resp:');
        Log::channel('pay')->info('code:'.$httpcode);
        Log::channel('pay')->info($text);
        Log::channel('pay')->info(var_export($resp, true));
        return $resp;
    }

    public function checkSign($data): bool
    {
        $sign = $data['signature'];
        unset($data['signature']);
        $json = json_encode($data);
        Log::channel('pay')->info('json for sign:'. $json.$this->key);
        Log::channel('pay')->info('hash:'. hash('sha512', $json.$this->key));
        return hash('sha512', $json.$this->key) === $sign;
    }

    public function payOut(string $orderId, int $cents, $requisites, $pay_method, string $currency = 'RUB')
    {
        $customer = $requisites;

        $method = $this->url.'v1/init/payout';
        $data = [
            'product' => 'karkencassino payout#'.$orderId,
            'amount' => $cents,
            'currency' => $currency,
            'orderNumber' => $orderId,
            "platform" => "web",
            'callbackUrl' => 'https://krakencassino.com/api/aeza/pay',
            'redirectSuccessUrl' => action('PayController@success'),
            'redirectFailUrl' => action('PayController@fail'),
        ];

        if (!empty($customer['_token'])) unset($customer['_token']);
        if (!empty($customer['payment-method'])) unset($customer['payment-method']);
        if (!empty($customer['amount'])) unset($customer['amount']);

        if (!empty($customer['transfer_type'])) {
            $data['transfer_type'] = $customer['transfer_type'];
            unset($customer['transfer_type']);
        }

        if (!empty($customer['qiwi_phone'])) $data['customer'] = ['phone' => $customer['qiwi_phone']];
        if (!empty($customer['cardNumber'])) $data['card'] = ['pan' => $customer['cardNumber']];

        return $this->query($method, 'post', $data);
    }

    private function query($method, $httpMethod, array $data = [])
    {
        $url = $this->url . $method;

        $ch = curl_init();
        if ( strtolower($httpMethod) === 'post') {

            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'authorization: Bearer ' . $this->key,
                'Content-Type: application/json',
            ]);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

        } else {

            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Authorization: Bearer ' . $this->key,
                'Content-Type: application/json',
            ]);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
            curl_setopt($ch, CURLOPT_ENCODING, "");
            if (!empty($data)) {
                $url .= '?' . http_build_query($data);
            }
        }

        Log::channel('pay')->info('AezaPay request: '.$url);
        Log::channel('pay')->info(json_encode($data));
        Log::channel('pay')->info(var_export($data, true));

        curl_setopt($ch, CURLOPT_HEADER, false);

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);

        curl_setopt($ch, CURLOPT_USERAGENT, 'KrakenCassino');
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_MAXREDIRS, 10);


        curl_setopt($ch, CURLOPT_VERBOSE, true);
        $verbose = fopen('php://temp', 'w+');
        curl_setopt($ch, CURLOPT_STDERR, $verbose);

        $text = curl_exec($ch);
        $err = curl_error($ch);
        $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        rewind($verbose);
        $verboseLog = stream_get_contents($verbose);

        Log::channel('pay')->info('curl verbose');
        Log::channel('pay')->info(htmlspecialchars($verboseLog));

        $resp = json_decode($text, true);
        if ($err) {
            Log::channel('pay')->error($err);
        }

        Log::channel('pay')->info('AezaPay resp:');
        Log::channel('pay')->info('code:'.$httpcode);
        Log::channel('pay')->info($text);
        Log::channel('pay')->info(var_export($resp, true));
        return $resp;
    }

    public static function depositCallback(Request $request)
    {
        Log::channel('pay')->info('Start processing AezaPay ' . PHP_EOL . var_export($request->getContent(), true));
        Log::channel('pay')->info('Start processing AezaPay ' . PHP_EOL . var_export(json_decode($request->getContent(), true), true));

        $trans_id = $request->get('orderNumber');
        if (!$trans_id) {
            Log::channel('pay')->error('orderId field not set');
            return response('Платеж не найден', 200);
        }

        $data = json_decode($request->getContent(), true);

        // if payout
        if ($data['type'] !== 'pay' && $data['type'] !== 'payment') {
            DB::commit();
            return response('OK', 200);
        }

        $currency = UserWallet::getCurrencyCode($data['currency']);
        $method = $data['extraReturnParam']??'';

        $api = new AezaPay();
        if (!$api->checkSign($data)) {
            Log::channel('pay')->error('Wrong sign, possible hack');
//            return response('Wrong sign', 500);
        }

        DB::beginTransaction();

        // Find transaction.
        /** @var PayoutTransaction $transaction */
        $transaction = PayoutTransaction::where('id', $trans_id)
            ->where('status_id', PayoutTransaction::STATUS_AEZA_PENDING)
            ->lockForUpdate()
            ->first();

        if ($transaction === null) {
            $transaction = PayoutTransaction::where('id', $trans_id)
                ->where('status_id', PayoutTransaction::STATUS_AEZA_SUCCESS)
                ->first();
            if ($transaction !== null) {
                Log::channel('pay')->error('Transaction with id ' . $trans_id . ' already completed. Request: ' . PHP_EOL . var_export($request->all(), true));
                DB::commit();
                return response('OK', 200);
            }
            Log::channel('pay')->error('Transaction with id ' . $trans_id . ' is not found. Request: ' . PHP_EOL . var_export($request->all(), true));
            DB::commit();
            return response('OK', 200);
        }

        if ($data['status'] === 'declined') {
            $transaction->status_id = PayoutTransaction::STATUS_AEZA_FAIL;
            $transaction->save();
            DB::commit();
            return response('OK', 200);
        }

        if ($data['status'] !== 'approved') {
            DB::commit();
            return response('OK', 200);
        }

        // Find user which create this transaction.
        /** @var MUser $user $user */
        $user = MUser::where('id', $transaction->user_id)->first();

        if ($user === null) {
            Log::channel('pay')->error('User of the transaction is not found.');
            DB::commit();
            return response('User not found.', 500);
        }

        $amount = $data['amount'] / 100;


        $fee = 0;

        if ($currency == UserWallet::CURRENCY_RUB) {
            $fee = $amount * 0.14;
        }

        $transaction->sum = $amount;
        $transaction->fee = round($fee, 2);
        $transaction->total_sum = $amount + $fee;
        $transaction->card_number = $data['sanitizedMask']?? '';

        try {
            Payin::completeTransaction($user, $transaction, $amount, PayoutTransaction::STATUS_AEZA_SUCCESS, $currency);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::channel('pay')->error($e->getMessage());
        }

        DB::commit();

        return response('OK', 200);
    }
}
