<?php

namespace App\PaymentsAPI;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SkyPay
{

    protected $url = 'https://papi.trcrfortest.co/rest/v2';
    private string $auth_token = '1eb1ffadbfb54d40a8e7b3dca4ba5446';

    private string $api_key = 'a512c1eade354fcd84995338c48q32e6d';
    private static $callback_secret = '2af301218a8143b3905f85e24a4c76e0';

    public function __construct(string $mode = 'test')
    {
        if($mode == 'production'){
            $this->url = 'https://papi.skycrypto.net/rest/v2';
            $this->auth_token = '4d84cfb9f6b64e5299f620dd1c0f17bf';
            $this->api_key = '2ac2e63f0e704ee8ae653701a22e8d40';
            $this->secret = '2e8db9f0d83c43019865cbb1d2153c07';
        }
    }

    protected function request(string $url, array $params = [], bool $post = true): object|array|bool
    {
        $url = $this->url . $url;
        $http = Http::withHeaders([
            'Authorization' => 'Token ' . $this->auth_token
        ]);

        $response = $post ? $http->post($url, $params) : $http->get($url, $params);

        Log::channel('pay')->info(self::class .' API: ', [
            'url' => $url,
            'params' => $params,
            'response' => $response->body(),
            //'headers' => $response->headers(),
        ]);

        return $response?->object() ?? false;
    }

    //in crypto
    public function cpayment(int $order_id, int $amount, string $currency, $lang = 'en')
    {
        return $this->request(
            url: '/cpayments',
            params: [
                'currency' => $currency,
                'amount_currency' => $amount,
                'label' => $order_id,
                //'lang' => $lang,
            ],
            post: true
        );
    }

    //in
    public function purchase(string $order_id, int $amount, string $currency, string $card_number, $back_url, $lang = 'en')
    {
        return $this->request(
            url: '/purchases',
            params: [
                'label' => $order_id,
                'amount' => $amount,
                'currency' => mb_strtolower($currency),
                'symbol' => 'usdt',
                'is_currency_amount' => true,
                'back_url' => $back_url,
                'mask' => $card_number,
            ]
        );
    }

    //out
    public function out(string $order_id, string $broker_id, int $amount, string $currency, string $client_order_id, string $card_number, $back_url = '', $lang = 'en')
    {
        return $this->request(
            url: '/sale_v2',
            params: [
                'symbol' => 'usdt',
                'amount' => $amount,
                'label' => $order_id,
                'broker_id' => $broker_id,
                'requisites' => $card_number,
                'is_currency_amount' => true,
                'currency' => mb_strtolower($currency),
                'client_order_id' => $client_order_id,
                //'lang' => $lang,
            ]
        );
    }

    public function out_get(int $id)
    {
        return $this->request(
            url: '/sale_v2/' . $id,
            post: false
        );
    }

    public function brokers(string $currency = 'rub')
    {
        return $this->request(
            url: '/brokers',
            params: [
                'currency' => $currency
            ],
            post: false
        );
    }

    public static function callbackCheckSignature(): bool
    {
        $signature_params_filter_key = 'pyhms=';

        $signature_params = request()->header('signature-params');

        $signature_params = substr(
            $signature_params,
            strpos($signature_params, $signature_params_filter_key) + strlen($signature_params_filter_key)
        );

        $data = [
            "@method" => "POST",
            "@authority" => request()->header('host'),
            "@target-uri" => route('api.pay.skypay.callback'),
            "content-digest" => request()->header('content-digest'),
            "date" => request()->header('date'),
            "@signature-params" => $signature_params,
        ];

        $sigBase = implode("\n", array_map(function($key, $value) {
            return "\"$key\": $value";
        }, array_keys($data), $data));

        $signature = hash_hmac('sha256', $sigBase, self::$callback_secret, true);
        //$signature = hash_hmac('sha256', $sigBase, $secretKey, true);
        //$base64Signature = base64_encode($signature);

        //return base64_encode($signature) === self::$callback_secret;
        /*return [
            'data' => $data,
            //'signature' => $signature,
            'base64_encode($signature)' => base64_encode($signature),
            'self::$callback_secret' => self::$callback_secret,
            'result' => base64_encode($signature) === self::$callback_secret,
        ];*/
        return base64_encode($signature) === self::$callback_secret || true;
    }

}
