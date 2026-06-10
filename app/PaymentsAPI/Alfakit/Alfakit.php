<?php

namespace App\PaymentsAPI\Alfakit;

use App\Helpers\Hosts;
use App\Models\PaySystemsConfig;
use App\Payin\Payin;
use App\UserWallet;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

/**
 * @url https://backoffice.alfakit.io/doc/?shell#introduction
 */
final class Alfakit
{
    private string $pub_key = "OtsIpYe3PwSRiKfAbPBaLtVJtjcUj53xOc6JJ89Y2iRr7NRB81eAOqUYapD0DBdenEEFr1H1QCjNhf4DhKzsyPmuz80EXh2BiL6tYKYYF9t6ZMD46MgQEZMXJjaoywTL2qaHRiH71cnmFM5QLNygKNDsr3jbVh859jCp9CBlaC2judzJvDZkRkuQf5FGtvUSj5jCq7Tdrw8dyoLBR4JVv5CjpRCxbxUXRCQXEEH7ZzQtiy4nkRgdwOZLZ6TmvcJY";
    private string $secret_key = "chHvMSpk9tNxwTDc9Z61gUXVUrzTghzTPE3AiSwFet5xEdznoaMjXBgHRuq3HG4ld1RhV9qtSTAM0JtlrNxs5TmKF9p8SXWRrwXyhc1WlvXhtOBYJRUZslEUiOqOR4FSfsQ7iIoHa055hoyFjqeb26Z8vMHFP2mp1BRnODpyq5dht4tgnx68PilCYO7aZiJtfggtTU6Fz3mtWBs7tB66SVRMVjbnZZt4T69lK5qWdzXmYHTySqAbH36MvtqXmbjInQfb6EZMwfvm2hObEf5swuroZBw52swusgokGKvZLRlrUhMZ7iyXCBHDK6QrlYRBycZJFkiw4PogdEGg91vbqDHR8DIExVZFNUSnd41lsHkiXSPy2x9Vy4GqAYrQD9iUc2Lbh75F6Y2FAJ1wtJ3r9GrXGWUiDknv0LBH2VgiVWZdfsVUpf0jPCvspsELoCA32mIBaI0ykEyBi0Ze6YsHjshVTA4kSM4aE5JYdwPR3Hf8KhLg7SIknpUwnVfRYvQb";
    private string $api_url = "https://admin.alfakit.io/api/api";

    private array $currencies = [
        UserWallet::CURRENCY_RUB => 1,
        UserWallet::CURRENCY_KZT => 3,
    ];

    public function __construct()
    {
        $conf = PaySystemsConfig::getSystemConfig(Payin::TYPE_ALFAKIT);
        $this->pub_key = $conf['pub_key'];
        $this->secret_key = $conf['secret_key'];
    }

    public function sign(string $data)
    {

        return hash_hmac('sha512', $data, $this->secret_key);
    }

    public function balance()
    {
        $enpoint = "/repayment/fetch_balance";
        $data = [];
        return $this->query($enpoint, 'post', $data);
    }

    public function deposit(int $amount, int $currency, string $tr_id, $verified = false)
    {
        $enpoint = "/repayment/create_payment_p2p";
//        $enpoint = "/repayment/create_payment";
//        if ($verified) $enpoint = "/repayment/create_payment_p2p";
        $kit_currency = $this->currencies[$currency];
        $data = [
            'sum' => $amount,
            'ttl' => 9000,
            'message' => 'KrakenCassino',
            'walletID' => $kit_currency,
            'webhookUrl' => Hosts::getFinHost(UserWallet::currencyName($currency)). '/api/pay/alfakit?tr_id='.$tr_id.'&hash='.$this->sign($tr_id.$amount.$this->secret_key),
            'redirect' => [
                'successURL' => route('pay.success', [], true),
                'failURL' => route('pay.fail', [], true),
            ],
        ];
        return $this->query($enpoint, 'post', $data);
    }

    public function payout(int $amount, int $currency, string $method, $card_number, $tr_id, $user_id, $ip)
    {
        $enpoint = "/repayment/create_payout";
        $kit_currency = $this->currencies[$currency];

        $data = [
            'sum' => $amount,
            'ttl' => 9000,
            'message' => 'KrakenCassino',
            'IP' => $ip,
            'type' => $method,
            'walletID' => $kit_currency,
            'webhookUrl' => Hosts::getFinHost(UserWallet::currencyName($currency)) . '/api/pay/alfakit?payout_tr_id='.$tr_id.'&hash='.$this->sign($tr_id.$amount.$this->secret_key),
            'cardNumber' => $card_number,
            'payerInfo' => [
                'payerId' => $user_id,
                'cardNumber' => $card_number,
                'type' => $method,
            ],
        ];
        return $this->query($enpoint, 'post', $data);
    }

    public function paymentInfo(string $uuid, $type = 'invoice')
    {
        $enpoint = "/repayment/fetch_order_info";
        $data = [
            'uuid' => $uuid,
            'type' => $type,
        ];
        return $this->query($enpoint, 'post', $data);
    }

    private function query($method, $httpMethod, array $data = [])
    {
        $url = $this->api_url . $method;

        $ch = curl_init();

        $payload = json_encode($data);

        $sign = $this->sign($payload);

        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'API-Key: ' . $this->pub_key,
            'Signature: ' . $sign,
            'Content-Type: application/json',
        ]);

        if ( strtolower($httpMethod) === 'post') {

            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);

        } else {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
            if (!empty($data)) {
                $url .= '?' . http_build_query($data);
            }
        }

        Log::channel('pay')->info('Alfakit request: '.$url);
        Log::channel('pay')->info(var_export($data, true));
        Log::channel('pay')->info('Alfakit request body: '.$url);
        Log::channel('pay')->info($payload);

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
        $resp = json_decode($text, true);

        rewind($verbose);
        $verboseLog = stream_get_contents($verbose);

        Log::channel('pay')->info('curl verbose');
        Log::channel('pay')->info(htmlspecialchars($verboseLog));

        if ($err) {
            Log::channel('pay')->error($err);
        }

        Log::channel('pay')->info('Alfakit resp:');
        Log::channel('pay')->info('code:'.$httpcode);
        Log::channel('pay')->info($text);
        Log::channel('pay')->info(var_export($resp, true));
        return $resp;
    }
}
