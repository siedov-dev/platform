<?php


namespace App\PaymentsAPI\SwiftPay;


use App\Models\PaySystemsConfig;
use App\Payin\Payin;
use Illuminate\Support\Facades\Log;

/**
 * Class SwiftPay
 * @package App\PaymentsAPI\SwiftPay
 * @see https://swiftpayru.docs.apiary.io/#reference/1//order/create
 * @see https://swiftpayp2p.docs.apiary.io/#reference/public-key/api/0
 */
class SwiftPay
{
    const SYSTEM_CARDS = 30;

    private string $token = "NcGHMfdh8WjpKFS9hQcWCdJtUEHAOwRV";
    private int $shop_id = 1043;
    private string $p2p_token = "1KjK06EO04E75VpMQAfb9yoY";

    private string $url = "https://api.swiftpay.ru/";
//    private string $url = "https://private-anon-50d31504f8-swiftpayru.apiary-proxy.com/";
//    private string $url = "https://private-anon-50d31504f8-swiftpayru.apiary-mock.com/";

    public function __construct()
    {
        $conf = PaySystemsConfig::getSystemConfig(Payin::TYPE_SWIFT);
        $this->url = $conf['url'] ?? "https://api.swiftpay.ru/";
        $this->token = $conf['token'] ?? "NcGHMfdh8WjpKFS9hQcWCdJtUEHAOwRV";
        $this->shop_id = $conf['shop_id'] ?? 1043;
        $this->p2p_token = $conf['p2p_token'] ?? '1KjK06EO04E75VpMQAfb9yoY';
    }

    public function genPublicToken(string $user)
    {
        $method = "p2p/jwt";
        $params = [
            'token' => $this->p2p_token,
            'user' => $user,
        ];

        return $this->sendQuery($method, $params);
    }

    public function p2pOrders()
    {
        $method = "p2p/orders";
        $params = [
            'token' => $this->p2p_token,
        ];

        return $this->sendQuery($method, $params);
    }

    public function p2pCreatePayin(string $user_id)
    {
        $p_token = $this->genPublicToken($user_id);
        if (!empty($p_token)) {
            return "https://p2p.swiftpay.ru/deposits/qiwi?token=" . $p_token['data'];
        }
        return '';
    }

    public function p2pCreatePayout(string $system, float $amount, string $user_id, string $card_no, string $tr_id)
    {
        $method = "p2p/payouts/create";
        $params = [
            'token' => $this->p2p_token,
            "system" => $system,
            "wallet" => $card_no,
            "amount" => $amount,
            "user" => '' . $user_id,
            "order_id" => '' . $tr_id,
//            "test" => 1,
        ];

        return $this->sendQuery($method, $params);
    }

    public function p2pCheckSign(array $data): bool
    {
        return $data['sign'] === md5($data['id'] . $data['amount']['value'] . $this->p2p_token);
    }

    /**
     * deposit
     * @param int $order_id
     * @param float $amount
     * @param int $tr_id
     * @return bool|mixed
     */
    public function orderCreate(int $order_id, float $amount, int $tr_id)
    {
        $method = "order/create";
        $params = [
            'token' => $this->token,
            'shop_id' => $this->shop_id,
            'order_id' => $order_id,
            'amount' => $amount,
            'description' => 'Пополнение счета',
            'data' => [
                'payout_tr' => $tr_id,
            ],
        ];

        return $this->sendQuery($method, $params);
    }

    public function checkSign($data)
    {
        return (hash('sha256', $data['order_id'] . number_format($data['amount'], 2, '.', '') . $this->token . $this->shop_id) !== $data['sign']);
    }

    public function createPayout($amount, $wallet)
    {
        $method = "createPayout";
        $params = [
            'token' => $this->token,
            'system_id' => self::SYSTEM_CARDS,
            'amount' => $amount,
            'wallet' => $wallet,
        ];

        return $this->sendQuery($method, $params);
    }

    public function payoutStatus($id)
    {
        $method = "payout/$id?token=" . $this->token;
        $params = [
        ];

        return $this->sendQuery($method, $params, false);
    }

    private function sendQuery(string $method, ?array $data = [], $post = true)
    {
        $this->is_error = false;

        $json = json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

        Log::channel('pay')->info('SwiftPay url ' . $this->url . $method);
        Log::channel('pay')->info('SwiftPay params' . var_export($json, true));

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_USERAGENT, 'KrakenCassino');
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_HEADER, FALSE);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Content-Type: application/json",
        ]);
        if ($post) {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
        }
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_URL, $this->url . $method);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($ch, CURLOPT_TIMEOUT, 20);

        $response = curl_exec($ch);

        curl_close($ch);

        Log::channel('pay')->info('SwiftPay response:' . $response);

        if (!$response) {
            Log::channel('pay')->error('Can`t connect to SwiftPay payment system');
            $this->is_error = true;
            $this->error_descr = "Can`t connect to SwiftPay payment system";
            return false;
        }

        $resp = json_decode($response, true);

        return $resp;
    }
}
