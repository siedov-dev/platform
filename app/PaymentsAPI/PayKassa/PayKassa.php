<?php

namespace App\PaymentsAPI\PayKassa;

use App\Models\PaySystemsConfig;
use App\Payin\Payin;
use Illuminate\Support\Facades\Log;

class PayKassa
{

    private int $api_id = 20489;
    private string $api_key = '7au15zVALZO90TlsWLisfkauZ0zzPd2S';

    private int $sci_id = 8608;
    private string $sci_key = "PzYPhlf9YOCoAibduYZWyek0QOxHvJ0n";

    private string $sci_url = "https://paykassa.app/sci/0.4/index.php";
    private string $api_url = "https://paykassa.app/api/0.5/index.php";

    private bool $test = false;

    public function __construct()
    {
        $conf = PaySystemsConfig::getSystemConfig(Payin::TYPE_PAYKASSA);
        $this->api_id = $conf['api_id'];
        $this->api_key = $conf['api_key'];
        $this->sci_id = $conf['sci_id'];
        $this->sci_key = $conf['sci_key'];
        $this->sci_url = $conf['sci_url'];
        $this->api_url = $conf['api_url'];
    }

    public function getBalance()
    {
        $url = $this->api_url;
        $data = [
            'func' => 'api_get_shop_balance',
            'api_id' => $this->api_id,
            'api_key' => $this->api_key,
            'shop' => $this->sci_id,
        ];

        return $this->query($url, 'post', $data);
    }

    public function deposit($order_id, $amount, $currency, $method)
    {
        $system = explode('-', $method)[0];

        if ($currency === 'mBTC') {
            $currency = 'BTC';
            $amount = $amount / 1000;
        }

        $url = $this->sci_url;
        $data = [
            'func' => 'sci_create_order',
            'sci_id' => $this->sci_id,
            'sci_key' => $this->sci_key,
            'order_id' => $order_id,
            'amount' => $this->format_currency($amount, $currency),
            'currency' => $currency,
            'system' => $system,
            'comment' => 'krakencassino deposit',
            'phone' => false,
            'paid_commission' => 'shop',
            'test' => $this->test,
        ];

        return $this->query($url, 'post', $data);
    }

    public function payout($amount, $currency, $method, $wallet)
    {
        $system = explode('-', explode('.', $method)[1])[0];

        if ($currency === 'mBTC') {
            $currency = 'BTC';
            $amount = $amount / 1000;
        }

        $url = $this->api_url;
        $data = [
            'func' => 'api_payment',
            'api_id' => $this->api_id,
            'api_key' => $this->api_key,
            'shop' => $this->sci_id,
            'amount' => $this->format_currency($amount, $currency),
            'currency' => $currency,
            'system' => $system,
            'paid_commission' => 'shop',
            'number' => $wallet,
            'tag' => 1,
            'priority' => 'medium',
            'test' => $this->test,
        ];

        return $this->query($url, 'post', $data);
    }

    public function format_currency($money, $currency): string
    {
        if (strtolower($currency) === "btc") {
            return $this->format_btc($money);
        }
        return $this->format_money($money);
    }

    public function format_btc($money): string
    {
        return sprintf("%01.8f", $money);
    }

    public function format_money($money): string
    {
        return sprintf("%01.2f", $money);
    }

    public function checkPayment($private_hash)
    {
        $url = $this->sci_url;
        $data = [
            'func' => 'sci_confirm_order',
            'sci_id' => $this->sci_id,
            'sci_key' => $this->sci_key,
            'private_hash' => $private_hash,
//            'test' => $this->test,
        ];

        return $this->query($url, 'post', $data);
    }

    public function checkTransaction($private_hash)
    {
        $url = $this->sci_url;
        $data = [
            'func' => 'sci_confirm_transaction_notification',
            'sci_id' => $this->sci_id,
            'sci_key' => $this->sci_key,
            'private_hash' => $private_hash,
            'test' => $this->test,
        ];

        return $this->query($url, 'post', $data);
    }

    private function query($url, $httpMethod, array $data = [])
    {
        $ch = curl_init();
        if (strtolower($httpMethod) === 'post') {
            Log::channel('pay')->info('PayKassa post: ' . $url);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
//                'Content-Type: application/json',
                'Content-Type: application/x-www-form-urlencoded',
            ]);
            $url .= '?' . http_build_query($data);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));

        } else {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
            curl_setopt($ch, CURLOPT_ENCODING, "");
            if (!empty($data)) {
                $url .= '?' . http_build_query($data);
            }
            Log::channel('pay')->info('PayKassa get request: ' . $url);
        }

        Log::channel('pay')->info('PayKassa get request: ' . $url);
        Log::channel('pay')->info(json_encode($data));
        Log::channel('pay')->info(var_export($data, true));

//        curl_setopt($ch, CURLOPT_HEADER, true);

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
//        curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);

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
        if ($err) {
            Log::channel('pay')->error($err);
        }

        rewind($verbose);
        $verboseLog = stream_get_contents($verbose);

        Log::channel('pay')->info('curl verbose');
        Log::channel('pay')->info(htmlspecialchars($verboseLog));


        Log::channel('pay')->info('PayKassa resp:');
        Log::channel('pay')->info('code:' . $httpcode);
        Log::channel('pay')->info($text);
        Log::channel('pay')->info(var_export($resp, true));
        return $resp;
    }
}
