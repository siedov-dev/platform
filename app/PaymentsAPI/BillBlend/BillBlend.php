<?php

namespace App\PaymentsAPI\BillBlend;

use App\ApplicationForPayment;
use App\Helpers\Hosts;
use App\MUser;
use App\Payin\Payin;
use App\PayoutTransaction;
use App\UserWallet;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Illuminate\Support\Arr;

/**
 * @docs https://doc.billblend.com/card_payment_API/sale-transactions.html#payment-form-integration
 */
class BillBlend
{
    private string $controlKey = "98010A7D-CF2B-11EE-9AE0-3CECEFF9878C";
    private string $endpointId = "9665";
    private string $single_endpointId = "47702";
    private string $single_endpointPayout = "53789";
    private string $testApiUrl = "https://sandbox.billblend.com";
    private string $apiUrl = "https://pay.billblend.com";
    private string $login = "kraken_bb";
    private bool $test = false;

    //payout
    private string $fromCardNumber = '5528566745050809';
    private string $fromCardCvv = '123';
    private string $fromPrintedName = 'John Doe';
    private string $fromExpireMonth = '12';
    private string $fromExpireYear = '2099';

    public function setTestMode(): void
    {
        $this->test = true;
        $this->login = 'kraken_sbb';
        $this->controlKey = 'B9E2299F-31AF-490C-BA8F-6B507CC9FAC9';
    }

    public function deposit(string $orderId, float $amount, string $email, array $formData, string $group_id)
    {

        $control = sha1($group_id . $orderId . ($amount * 100) . $email . $this->controlKey);

        $data = [
            'client_orderid' => $orderId,
            'order_desc' => 'Kraken casino deposit',
            'amount' => $amount,
            'currency' => 'EUR',
            'address1' => $formData['address1'],
            'city' => $formData['city'],
            'zip_code' => $formData['zip_code'],
            'country' => $formData['country'],
            'phone' => $formData['phone'],
            'email' => $email,
            'ipaddress' => $this->getUserIp(),
            'control' => $control,
            'first_name' => $formData['first_name'],
            'last_name' => $formData['last_name'],
            'server_callback_url' => "https://krakenfn.com/api/pay/billbland", // action([\App\PaymentsAPI\BillBlend\BillBlend::class, 'depositCallback'], [], true),
            'redirect_success_url' => route('pay.success'), //don't redirect when using iframe
            'redirect_fail_url' => route('pay.fail'), //don't redirect when using iframe
        ];

        $method = '/checkout/api/v2/sale-form/group/' . $group_id;

        Log::channel('pay')->info('BillBlend deposit request ' . var_export($data, true));

        if ($this->test) {
            $response = Http::asForm()->post($this->testApiUrl . $method, $data);
        } else {
            $response = Http::asForm()->post($this->apiUrl . $method, $data);
        }

        $responseArr = [];
        parse_str($response->body(), $responseArr);

        $responseArr['control'] = $control;

        return $responseArr;
    }

    public function depositS2s(string $orderId, float $amount, string $email, array $formData, string $group_id)
    {
        //test
//        $group_id = '3269';

        $control = sha1($group_id . $orderId . floor($amount * 100) . $email . $this->controlKey);

        $data = [
            'client_orderid' => $orderId,
            'order_desc' => 'Kraken casino deposit',
            'amount' => $amount,
            'currency' => 'EUR',
            'address1' => $formData['address1'],
            'city' => $formData['city'],
            'zip_code' => $formData['zip_code'],
            'country' => $formData['country'],
            'phone' => $formData['phone'],
            'email' => $email,
            'ipaddress' => $this->getUserIp(),
            'control' => $control,
            'first_name' => $formData['first_name'],
            'last_name' => $formData['last_name'],
            'server_callback_url' => Hosts::getFinHost("EUR")."/api/pay/billbland", // action([\App\PaymentsAPI\BillBlend\BillBlend::class, 'depositCallback'], [], true),
            'redirect_success_url' => route('pay.success'),
            'redirect_fail_url' => route('pay.fail'),
            'credit_card_number' => $formData['cardNumber'],
            'card_printed_name' => $formData['card_holder'],
            'expire_month' => $formData['month'],
            'expire_year' => $formData['year'],
            'cvv2' => $formData['cvc'],
        ];

        $method = '/checkout/api/v2/sale/group/' . $group_id;

        Log::channel('pay')->info('BillBlend deposit request '. $this->testApiUrl.$method.' ' . var_export($data, true));

        if ($this->test) {
            $response = Http::asForm()->post($this->testApiUrl . $method, $data);
        } else {
            $response = Http::asForm()->post($this->apiUrl . $method, $data);
        }

        $responseArr = [];
        parse_str(urldecode($response->body()), $responseArr);

        $responseArr['control'] = $control;

        Log::channel('pay')->info('BillBlend deposit response ' . var_export($responseArr, true));
        return $responseArr;
    }

    public function balance()
    {
        $apiUrl = $this->apiUrl;
        $method = '/checkout/api/v2/get-balance/merchant/kraken_bb';// . $this->single_endpointId;
        $merchantLogin = "kraken_bb";
        $private_key_pem = file_get_contents(resource_path() . '/private_key_pkcs_2.pem');

        $oauth = new tmhOAuth();
        $oauth->reconfigure(array(
            'consumer_key' => $merchantLogin,
            'consumer_secret' => $this->controlKey,
            'oauth_signature_method' => 'HMAC-SHA1',
            'private_key_pem' => $private_key_pem,
        ));

        // uncomment the following line to see the configuration before the request

        //print_r($oauth);
        //echo "\n";

        $params = [];

        $status = $oauth->request('POST', $apiUrl.$method, $params);

        Log::channel('payout')->info('balance_response:'. var_export($status, true));

        if ($status == 0) {
            throw new \Exception('Could not make an HTTP request; please inspect $oauth object to see what happened');
        } else if ($status != 200) {
            throw new \Exception('Non-successful HTTP status ' . $status . '; please inspect $oauth object to see what happened');
        }

        return $oauth->response['response'];
    }

    public function payout(ApplicationForPayment $transaction, array $requisites)
    {
        /** @var MUser $user */
        $user = MUser::findOrFail($transaction->getUserId());

        $apiUrl = $this->apiUrl;
        $method = '/checkout/api/v4/transfer/' . $this->single_endpointPayout;

        $merchantLogin = "kraken_bb";
        $private_key_pem = file_get_contents(resource_path() . '/private_key_pkcs_2.pem');

        $oauth = new tmhOAuth();
        $oauth->reconfigure(array(
            'consumer_key' => $merchantLogin,
            'consumer_secret' => $this->controlKey,
            'oauth_signature_method' => 'RSA-SHA256',
            'private_key_pem' => $private_key_pem,
        ));

        $params = [
            'deposit2card' => 'true',//payout mode
            //mandatory
            'ipaddress' => $user->getLastIp(),
            'client_orderid' => strval($transaction->getId()),
            'currency' => 'EUR',
            'amount' => strval($transaction->suma),
            'redirect_url' => route('landing'),
            'order_desc' => 'Payout from Kraken casino',

            // receiver card data
            'destination-card-no' => strval($transaction->card_number),
//            'card_printed_name' => $requisites['card_holder'],
            'destination_expire_month' => $requisites['exp_month'],
            'destination_expire_year' => $requisites['exp_year'],

            //required
            'receiver_first_name' => $requisites['first_name'],
            'receiver_last_name' => $requisites['last_name'],
            'receiver_address1' => $requisites['address1'],
            'receiver_city' => $requisites['city'],
            'receiver_zip_code' => $requisites['zip_code'],
            'receiver_country_code' => mb_strtoupper($requisites['country_code']),
            'receiver_phone' => $requisites['phone'],
            'receiver_email' => $requisites['email'],
        ];

        $status = $oauth->request('POST', $apiUrl.$method, $params, true, false);
        Log::channel('payout')->info('transfer_response:'. var_export($oauth, true));

        if ($status == 0) {
            throw new \Exception('Could not make an HTTP request; please inspect $oauth object to see what happened');
        } else if ($status != 200) {
            throw new \Exception('Non-successful HTTP status ' . $status . '; please inspect $oauth object to see what happened');
        }

        parse_str($oauth->response['response'], $responseFields);

        return $responseFields;
    }

    public function transactionStatusByGroupID(string $client_order_id, string $order_id, string $group_id)
    {
        //test
//        $group_id = '3269';
        $login = $this->login;
        $control = sha1($login.$client_order_id.$order_id.$this->controlKey);

        $data = [
            'login' => $login,
            'client_orderid' => $client_order_id,
            'orderid' => $order_id,
            'control' => $control,
        ];

        $method = '/checkout/api/v2/status/group/' . $group_id;

        Log::channel('pay')->info('BillBlend status request ' . var_export($data, true));

        if ($this->test) {
            $response = Http::asForm()->post($this->testApiUrl . $method, $data);
        } else {
            $response = Http::asForm()->post($this->apiUrl . $method, $data);
        }

        $responseArr = [];
        parse_str(urldecode($response->body()), $responseArr);

        $responseArr['control'] = $control;

        Log::channel('pay')->info('BillBlend status response ' . var_export($responseArr, true));

        return $responseArr;
    }

    public function transactionStatus($client_orderid)
    {
        $login = $this->login;
        $control = sha1($login.$client_orderid.$this->controlKey);

        $data = [
            'login' => $login,
            'client_orderid' => $client_orderid,
            'control' => $control,
        ];

        $method = '/checkout/api/v2/status/' . $this->single_endpointPayout;

        Log::channel('payout')->info('BillBlend status request ' . var_export($data, true));

        if ($this->test) {
            $response = Http::asForm()->post($this->testApiUrl . $method, $data);
        } else {
            $response = Http::asForm()->post($this->apiUrl . $method, $data);
        }

        $responseArr = [];
        parse_str($response->body(), $responseArr);

        $responseArr['control'] = $control;

        Log::channel('payout')->info('BillBlend status response ' . var_export($responseArr, true));

        return $responseArr;
    }

//    public function payoutBack(ApplicationForPayment $transaction, array $requisites)
//    {
//        /** @var MUser $user */
//        $user = MUser::findOrFail($transaction->getUserId());
//
//        $method = '/checkout/api/v4/transfer/' . $this->endpointId;
//
//        $authData = [
//            'oauth_consumer_key' => 'kraken_bb',
//            'oauth_nonce' => Str::random(20),
//            'oauth_signature_method' => 'RSA-SHA256',
//            'oauth_timestamp' => time(),
//            'oauth_version' => '1.0',
//        ];
//
//        $requestFields = array(
//            //mandatory
//            'ipaddress' => $user->getLastIp(),
//            'client_orderid' => strval($transaction->getId()),
//            'currency' => 'EUR',
//            'amount' => strval($transaction->suma),
//            'redirect_url' => route('landing'),
//            'order_desc' => 'Payout from Kraken casino',
//
//            // receiver card data
//            'destination-card-no' => strval($transaction->card_number),
//            'card_printed_name' => $requisites['card_holder'],
//            'destination_expire_month' => $requisites['exp_month'],
//            'destination_expire_year' => $requisites['exp_year'],
//
//            //required
//            'receiver_first_name' => $requisites['first_name'],
//            'receiver_last_name' => $requisites['last_name'],
//            'receiver_address1' => $requisites['address1'],
//            'receiver_city' => $requisites['city'],
//            'receiver_zip_code' => $requisites['zip_code'],
//            'receiver_country_code' => $requisites['country_code'],
//            'receiver_phone' => $requisites['phone'],
//            'receiver_email' => $requisites['email'],
//        );
//
//        $apiUrl = $this->apiUrl;
//        if ($this->test) {
//            $apiUrl = $this->testApiUrl;
//        }
//
//        $arrToQuery = $requestFields;
//        foreach ($authData as $key => $value) {
//            $arrToQuery[$key] = $value;
//        }
//
//        ksort($arrToQuery);
//
//        $baseURL = rawurlencode($apiUrl . $method);
//        $baseQuery = http_build_query($arrToQuery, false, null, PHP_QUERY_RFC3986);
//        $base = 'POST' . '&' . $baseURL . '&' . rawurlencode($baseQuery);
//
//        $privateKeyId = openssl_pkey_get_private(file_get_contents(resource_path() . '/private_key_pkcs_2.pem'));
//        openssl_sign($base, $signature, $privateKeyId, 'RSA-SHA256');
////        if (PHP_VERSION_ID < 80000) {
////            openssl_free_key($privateKeyId);
////        }
//        $sign = rawurlencode(base64_encode($signature));
//
//
//        $customHeaders = array(
//            'Authorization' => 'OAuth oauth_consumer_key="' . $authData['oauth_consumer_key'] . '", oauth_nonce="' . $authData['oauth_nonce'] . '", oauth_signature="' . $sign . '", oauth_signature_method="RSA-SHA256", oauth_timestamp="' . $authData['oauth_timestamp'] . '", oauth_version="1.0"'
//        );
//
//        return $this->sendRequestPayout($apiUrl . $method, $requestFields, $customHeaders);
//    }

    public function depositCallback(Request $request)
    {
        Log::channel('pay')->info('BillBlend callback');
        Log::channel('pay')->info(var_export($request->all(), true));

        if (!isset($request->status)) return response('status not found', 500);

        $orderId = $request->client_orderid;
        /** @var PayoutTransaction $transaction */
        $transaction = PayoutTransaction::where('id', $orderId)
            ->where('status_id', PayoutTransaction::STATUS_BILLBLEND_PENDING)
            ->first();

        if (!$transaction) return response('transaction not found', 500);

        if ($request->status == 'approved') {
            $user = MUser::where('id', $transaction->user_id)->first();

            if ($user === null) {
                Log::channel('pay')->error('User of the transaction is not found.');
                return response('user not found', 500);
            }

            $amount = $request->amount;
            $currency = UserWallet::getCurrencyCode($request->currency);
            $transaction->fee = $amount * 0.14;

            Payin::completeTransaction($user, $transaction, $amount, PayoutTransaction::STATUS_BILLBLEND_SUCCESS, $currency);

            return response('OK', 200);

        } else if ($request->status == 'processing') {
            //*
        } else {
            $transaction->status_id = PayoutTransaction::STATUS_BILLBLEND_FAIL;
            $transaction->save();
            return response('OK', 200);
        }
    }

    function getUserIp(): string
    {
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else {
            $ip = $_SERVER['REMOTE_ADDR'];
        }

        return $ip;
    }

    function sendRequestPayout($url, array $requestFields, array $customHeaders = array())
    {

        Log::channel('payout')->info(__CLASS__ . ' payout request');
        Log::channel('payout')->info(var_export($requestFields, true));
        Log::channel('payout')->info(var_export($customHeaders, true));

        $curl = curl_init($url);

        curl_setopt_array($curl, array
        (
            CURLOPT_HEADER => 1,
            CURLOPT_USERAGENT => 'BillBlend-Client/1.0',
            CURLOPT_SSL_VERIFYHOST => 0,
            CURLOPT_SSL_VERIFYPEER => 0,
            CURLOPT_POST => 1,
            CURLOPT_RETURNTRANSFER => 1
        ));

        $headersJoiner = function ($key, $value) {
            return "{$key}: {$value}";
        };

        curl_setopt($curl, CURLOPT_HTTPHEADER, array_map($headersJoiner, array_keys($customHeaders), array_values($customHeaders)));
        curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($requestFields));

        $response = curl_exec($curl);

        if (curl_errno($curl)) {
            $error_message = 'Error occurred: ' . curl_error($curl);
            $error_code = curl_errno($curl);
        } elseif (curl_getinfo($curl, CURLINFO_HTTP_CODE) != 200) {
            $error_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
            $error_message = "Error occurred. HTTP code: '{$error_code}'. Response: {$response}";
        }

        curl_close($curl);

        if (!empty($error_message)) {
            Log::channel('payout')->error($error_message);
        }

        if (empty($response)) {
            Log::channel('payout')->error('User of the transaction is not found.');
        }

        $responseFields = array();

        parse_str($response, $responseFields);

        return $responseFields;
    }
}
