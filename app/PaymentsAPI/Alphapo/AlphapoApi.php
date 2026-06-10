<?php

namespace App\PaymentsAPI\Alphapo;

use App\Models\PaySystemsConfig;
use App\Payin\Payin;
use Illuminate\Support\Facades\Log;

final class AlphapoApi
{
    private string $url = "https://app.sandbox.cryptoprocessing.com/api/v2/";
    private string $public_key = "S0Dd4rIu6aZ9aeWv6ESleQgkfJRkRmNA";
    private string $secret_key = "DYYfeWHeobIvbwCHsvuZlgkOcYyN8XrxbBVbtsXAACxMzk9de6Uz9zDUJpKn4lH3";

    public function __construct()
    {
        $conf = PaySystemsConfig::getSystemConfig(Payin::TYPE_ALPHAPO);
        $this->public_key = $conf['public_key'];
        $this->secret_key = $conf['secret_key'];
    }

    public function ping()
    {
        $method = "ping";
        $data = [];
        return $this->query($method, 'get', $data);
    }

    public function getCurrencies()
    {
        $method = "currencies/list";
        $data = [];
        return $this->query($method, 'post', $data);
    }

    public function getAddress($user_id, $currency)
    {
        $method = "addresses/take";
        $data = [
            'foreign_id' => $user_id,
            'currency' => $currency,
        ];
        return $this->query($method, 'post', $data);
    }

    public function payout($amount, $tr_id, $method, $currency, $wallet)
    {
        if ($currency === 'mBTC') {
            $currency = 'BTC';
            $amount = $amount/1000;
        }

        $method = "withdrawal/crypto";
        $data = [
            'foreign_id' => $tr_id,
            'currency' => $currency,
            'amount' => $amount,
            'address' => $wallet,
        ];
        return $this->query($method, 'post', $data);
    }

    private function sign($paramsArray)
    {
        $requestBody = json_encode($paramsArray);
        return hash_hmac('sha512', $requestBody, $this->secret_key);
    }

    public function checkSign($data, $sign)
    {
        $requestBody = json_encode($data);
        return $sign === hash_hmac('sha512', $requestBody, $this->secret_key);
    }

    private function query($method, $httpMethod, array $data = [])
    {
        $url = $this->url . $method;

        $ch = curl_init();
        if ( strtolower($httpMethod) === 'post') {

            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'X-Processing-Key: '.$this->public_key,
                'X-Processing-Signature: '.$this->sign($data),
                'Content-Type: application/json',
            ]);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

        } else {

            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'X-Processing-Key: '.$this->public_key,
                'X-Processing-Signature: '.$this->sign($data),
                'Content-Type: application/json',
            ]);
            if (!empty($data)) {
                $url .= '?' . http_build_query($data);
            }
        }

        Log::channel('pay')->info(__CLASS__.' request: '.$url);
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

        $text = curl_exec($ch);
        $err = curl_error($ch);
        $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $resp = json_decode($text, true);
        if ($err) {
            Log::channel('pay')->error($err);
        }

        Log::channel('pay')->info(__CLASS__.' resp:');
        Log::channel('pay')->info('code:'.$httpcode);
        Log::channel('pay')->info($text);
        Log::channel('pay')->info(var_export($resp, true));
        return $resp;
    }
}
