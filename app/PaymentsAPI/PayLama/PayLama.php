<?php

namespace App\PaymentsAPI\PayLama;

use App\Models\PaySystemsConfig;
use App\Payin\Payin;
use Illuminate\Support\Facades\Log;

final class PayLama
{
    private string $api_key = "A9OyD1pmWuHcEcorh123Xyh2BYHSP8B0Z1kvoSx3NKvkih7zpxP3y3ISfSZmKLvt5jneA2l22ebylWQTX5JLKQYNkJvere5NIGEEThh3y7hePYLXWjOQsw4hOS7hYJ87Q5KHZGMqqFecIz5nM5sScGpCN6NiNNS8LTFYqSHKMix8fGjKM0RBQxyKsVi4zUOC0kimGA4rj2xf0xVEM1XZO98Hi4TEabcV0Jy4XZ7yhqtGHYQe1lL64rVz1n0PuUEn";
    private string $secret_key = "Zb5OSi08y0AqxUvHISnrdcOq4CiErcs30K8Ty2IQO674wfrHTUuDXQHnuXBWaKzBK1D6zbGDPAS3HP2Lx1zMV1nmE7rfg891BR1rWcaomhbjh2TI17p48IaobT0xsyrBvkYaLk5tzLGhDudMcYTaPj6PI0c8JFYUAVjUXvEvnjADleqbpENgfWM4UuIPWWej5UPuPzt0yMdA0RMV4dlCoywKbbPqosjNZEuhKlFhJyP06vB8JmWIj5riwkCyFr8kKnW1Rsze01jCSQJQc8ErNJZ05PkDrqTp90bEthxXkcdj00v25EEbEJPy97KTxzg7fyULIwLNhrwSpZm4cpToQ6Yo7XOn5vOffYBB1s3XwrFmv5s81Fht6w8NZZBhDMhsbJftJ00wJBxAe713Y3CqNuAVdAbc9erCv1fugL7psuxfQXPKzefJemcCE9WEMeBsaAWPNiwHs5lMUJQ4hgvq7BLesqnJ6yMn6UOgau1yXMekS2mhHh6xuQhRRk17GUSR";
    private string $url = "https://admin.paylama.io/api/api/";

    const RUB = 1;
    const USD = 2;
    const KZT = 3;
    const EUR = 4;

    const TYPE_WITHDRAW = 'withdraw';
    const TYPE_DEPOSIT = 'invoice';

    public function __construct()
    {
        $conf = PaySystemsConfig::getSystemConfig(Payin::TYPE_PAYLAMA);
        $this->api_key = $conf['api_key'];
        $this->secret_key = $conf['secret_key'];
        $this->url = $conf['url'];
    }

    public function getBalance()
    {
        $method = 'payment/get_balance';
        $http_method = 'post';
        $data = [];
        return $this->query($method, $http_method, $data);
    }

    public function depositForm(int $amount, string $method, int $tr_id)
    {
        $endpoint = 'payment/generate_invoice_h2h';
        if ($method === 'PayLama.qw') {
            $endpoint = 'payment/generate_invoice';
        }
        $hash = hash('sha256', $tr_id.$this->secret_key);

        $http_method = 'post';
        $data = [
            'amount' => $amount,
            'expireAt' => 600,
            'comment' => 'krakencassino deposit',
            'clientIP' => request()->ip(),
            'currencyID' => self::RUB,
            'callbackURL' => 'https://krakencassino.com/api/pay/paylama?tr_id='.$tr_id.'&hash='.$hash,
            'paySourcesFilter' => explode('.', $method)[1],
        ];
        return $this->query($endpoint, $http_method, $data);
    }

    public function payout(int $amount, string $method, $card)
    {
        $endpoint = 'payment/generate_withdraw';
//        $hash = hash('sha256', $tr_id.$this->secret_key);

        $http_method = 'post';
        $data = [
            'amount' => $amount,
            'expireAt' => 600,
            'comment' => 'krakencassino payout',
            'clientIP' => request()->ip(),
            'currencyID' => self::RUB,
//            'callbackURL' => 'https://krakencassino.com/api/pay/paylama?tr_id='.$tr_id.'&hash='.$hash,
            'paySourcesFilter' => explode('.', $method)[1],
            'recipient' => $card,
        ];
        return $this->query($endpoint, $http_method, $data);
    }

    public function checkSign(array $data): bool
    {
        $hash = hash('sha256', $data['tr_id'].$this->secret_key);
        return $hash === $data['hash']??'';
    }

    public function getPaymentInfo(int $id, string $extId, string $type)
    {
        $method = 'payment/get_order_details';
        $http_method = 'post';

        $data = [];
        if ($id !== 0) {
            $data['ID'] = $id;
        } else {
            $data['externalID'] = $extId;
        }
        $data['orderType'] = $type;

        return $this->query($method, $http_method, $data);
    }

    private function sign(array $data): string
    {
        return hash_hmac('sha512', json_encode($data, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES), $this->secret_key);
    }

    private function query($method, $httpMethod, array $data = [])
    {
        $url = $this->url . $method;
        Log::channel('pay')->info(__CLASS__.__METHOD__.' query url:'.$url);
        Log::channel('pay')->info(__CLASS__.__METHOD__.' query data:'.json_encode($data, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES));

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'API-Key: ' . $this->api_key,
            'Signature: ' . $this->sign($data),
        ],
        );

        if ($httpMethod === 'post') {

            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES));

        } else {

            $url .= '?' . http_build_query($data);
        }


        curl_setopt($ch, CURLOPT_HEADER, 0);

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        curl_setopt($ch, CURLOPT_USERAGENT, 'KrakenCassino');
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);

        $resp = json_decode(curl_exec($ch), true);
        Log::channel('pay')->info(__CLASS__.__METHOD__.' query resp:'.var_export($resp, true));
        return $resp;
    }
}
