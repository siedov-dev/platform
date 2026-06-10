<?php

namespace App\PaymentsAPI\CryptoCloud;

use App\Models\PaySystemsConfig;
use App\Payin\Payin;
use Illuminate\Support\Facades\Log;

final class CryptoCloud
{
    private string $token = "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpZCI6MzE1LCJleHAiOjg4MDQ5MzE0ODI2fQ.ivnHlZWRCMlUuMPuI7h1bkcpbyhKMufSRL7KTrJ2TcY";
    private string $shop_id = "D5sjVMVTEUMSMs72";
    private string $url = "https://cryptocloud.pro";

    public function __construct()
    {
        $conf = PaySystemsConfig::getSystemConfig(Payin::TYPE_CRYPTOCLOUD);
        $this->token = $conf['token'];
        $this->shop_id = $conf['shop_id'];
    }

    public function invoiceCreate(float $amount, string $order_id)
    {
        $method = "/api/v2/invoice/create";
        $data  = [
            'shop_id' => $this->shop_id,
            'amount' => $amount,
            'order_id' => $order_id,
            'currency' => 'USD',
        ];

        return $this->query($method, 'post', $data);
    }

    private function query($method, $httpMethod, array $data = [])
    {
        $url = $this->url . $method;

        $ch = curl_init();
        if ( strtolower($httpMethod) === 'post') {

            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'authorization: Token ' . $this->token,
                'Content-Type: application/json',
            ]);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

        } else {

            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Authorization: Bearer ' . $this->token,
                'Content-Type: application/json',
            ]);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
            curl_setopt($ch, CURLOPT_ENCODING, "");
            if (!empty($data)) {
                $url .= '?' . http_build_query($data);
            }
        }

        Log::channel('pay')->info('CryptoCloud request: '.$url);
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

        Log::channel('pay')->info('CryptoCloud resp:');
        Log::channel('pay')->info('code:'.$httpcode);
        Log::channel('pay')->info($text);
        Log::channel('pay')->info(var_export($resp, true));
        return $resp;
    }
}
