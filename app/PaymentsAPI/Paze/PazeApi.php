<?php


namespace App\PaymentsAPI\Paze;


use App\Models\PaySystemsConfig;
use App\Payin\Payin;
use Illuminate\Support\Facades\Log;

/**
 * Class PazeApi
 * @package App\PaymentsAPI\Paze
 * @link https://app.paze.eu/docs/
 */
class PazeApi
{
    private string $sign_key = "8XD1vwG17X1v";
    private string $key = "TijPI5BFp7B6azZp2s1sNvU7LDydg4i5";
    private string $url = "https://app-demo.paze.eu/api/v1/";

    const TYPE_DEPOSIT = "DEPOSIT";
    const TYPE_WITHDRAW = "WITHDRAWAL";
    const TYPE_REFUND = "REFUND";

    public function __construct()
    {
        $conf = PaySystemsConfig::getSystemConfig(Payin::TYPE_PAZE);
        $this->key = $conf['key'] ?? "TijPI5BFp7B6azZp2s1sNvU7LDydg4i5";
        $this->sign_key = $conf['sign_key'] ?? "8XD1vwG17X1v";
        $this->url = $conf['url'] ?? "https://app-demo.paze.eu/api/v1/";
    }

    public function getPayments()
    {
        $data = [];
        return $this->query('payments', 'get', $data);
    }

    public function checkSign(string $json_body, string $hash)
    {
        return hash_hmac('sha256', $json_body, $this->sign_key) === $hash;
    }

    public function getPayment(string $payment_id)
    {
        $data = [
        ];
        return $this->query('payments/'.$payment_id, 'get', $data);
    }

    public function createPayment(string $payment_id, string $type, float $amount, array $requisites, $currency = "INR", string $method = "ALL")
    {
        $data = [
            'referenceId' => $payment_id,
            'paymentType' => $type,
            'amount' => $amount,
            'currency' => $currency,
            'customer' => $requisites,
            'returnUrl' => action('PayController@success', [], true),
        ];

        if ($method !== 'ALL') {
            $data['paymentMethod'] = $method;
        }

        return $this->query('payments', 'post', $data);
    }
    /*
     [
     "timestamp" => "2022-02-09T08:46:22.132+00:00",
     "status" => 415,
     "error" => "Unsupported Media Type",
     "message" => "Content type 'application/x-www-form-urlencoded' not supported",
     "path" => "/api/v1/payments",
    ]

     [
     "timestamp" => "2022-02-09T08:47:55.030+00:00",
     "status" => 200,
     "result" => [
       "id" => "60cb223d90604173ad953a87d3ae8424",
       "referenceId" => "-1",
       "paymentType" => "DEPOSIT",
       "state" => "CHECKOUT",
       "paymentMethod" => "BASIC_CARD",
       "amount" => 100,
       "currency" => "INR",
       "redirectUrl" => "https://demo.paze.eu/payment/60cb223d90604173ad953a87d3ae8424",
     ],
   ]
     */

    public function createWithdraw(string $payment_id, float $amount, $requisites, $currency = "INR", string $method = "ALL")
    {
        $addressLine1 = $requisites['addressLine1'];
        unset($requisites['addressLine1']);
        $data = [
            'referenceId' => $payment_id,
            'paymentType' => self::TYPE_WITHDRAW,
            'amount' => $amount,
            'currency' => $currency,
            'customer' => $requisites,
            'billingAddress' => ['addressLine1' => $addressLine1],
            'returnUrl' => action('PayController@success', [], true),
        ];

        if ($method !== 'ALL') {
            $data['paymentMethod'] = $method;
        }

        return $this->query('payments', 'post', $data);
    }

    private function query($method, $httpMethod, array $data = [])
    {
        $url = $this->url . $method;

        $ch = curl_init();
        Log::channel('pay')->info('Paze request: '.$url);
        Log::channel('pay')->info(json_encode($data));

        if ( strtolower($httpMethod) === 'post') {

            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $this->key,
            ]);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

        } else {

            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Authorization: Bearer ' . $this->key,
            ]);
            $url .= '?' . http_build_query($data);

        }


        curl_setopt($ch, CURLOPT_HEADER, false);

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        curl_setopt($ch, CURLOPT_USERAGENT, 'KrakenCassino');
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);

        $resp = json_decode(curl_exec($ch), true);
        Log::channel('pay')->info('Paze resp:');
        Log::channel('pay')->info(json_encode($resp));
        return $resp;
    }
}
