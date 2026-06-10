<?php

namespace App\PaymentsAPI\Passimpay;

use App\Models\PaySystemsConfig;
use App\MUser;
use App\Payin\Payin;
use App\PayoutTransaction;
use App\UserWallet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * @docs https://passimpay.io/en/developers/getaddress
 */
class Passimpay
{
//    private int $platform_id = 648;
    private int $platform_id = 649;
//    private string $key = "83cfe6-3ff9e2-bdaca2-f5666d-232332";
    private string $key = "75d69f-f99e31-0085c3-956abf-03ae85";
    private string $_url = 'https://api.passimpay.io';

    const TRON = 90;
    const USDT_ERC20 = 70;
    const USDT_TRC20 = 71;
    const USDD = 233;

    public function __construct()
    {
        $conf = PaySystemsConfig::getSystemConfig(Payin::TYPE_PASSIMPAY);
        $this->key = $conf['key']?? "75d69f-f99e31-0085c3-956abf-03ae85";
        $this->platform_id = $conf['platform_id']?? 649;
        $this->_url = $conf['url']?? "https://api.passimpay.io";
    }

    public function getBalance()
    {
        $endpoint = "/balance";
        $data = [
            'platform_id' => $this->platform_id,
        ];

        return $this->query($endpoint, 'post', $data);
    }

    public function getCurrencies()
    {
        $endpoint = "/currencies";
        $data = [
            'platform_id' => $this->platform_id,
        ];

        return $this->query($endpoint, 'post', $data);
    }

    public function getAddress(int $order_id, int $payment_id)
    {
        $endpoint = "/getpaymentwallet";
        $data = [
            'payment_id' => $payment_id,
            'platform_id' => $this->platform_id,
            'order_id' => $order_id,
        ];

        return $this->query($endpoint, 'post', $data);
    }

    public function orderStatus(string $order_id)
    {
        $endpoint = "/orderstatus";
        $data = [
            'platform_id' => $this->platform_id,
            'order_id' => $order_id,
        ];

        return $this->query($endpoint, 'post', $data);
    }

    public function createInvoiceLink(int $order_id, float $amount)
    {
        $endpoint = "/createorder";
        $data = [
            'platform_id' => $this->platform_id,
            'order_id' => $order_id,
            'amount' => number_format($amount, 2, '.', ''),
//            'currencies' => $currencies, optional list of currencies
        ];

        return $this->query($endpoint, 'post', $data);
    }

    public function withdraw(float $amount, string $address, int $out_currency)
    {
        $endpoint = "/withdraw";
        $data = [
            'payment_id' => $out_currency,
            'platform_id' => $this->platform_id,
            'amount' => number_format($amount, 2, '.', ''),
            'address_to' => $address,
        ];

        return $this->query($endpoint, 'post', $data);
    }

    public function hash(string $payload): string
    {
        return hash_hmac('sha256', $payload, $this->key);
    }

    private function query($method, $httpMethod, array $data = [])
    {
        $payload = http_build_query($data);
        $hash = $this->hash($payload);
        $data['hash'] = $hash;

        $post_data = http_build_query($data);

        $url = $method;
        if (!preg_match('/^http.*$/', $method)) {
            $url = $this->_url . $method;
        }

        $ch = curl_init();

        if (strtolower($httpMethod) === 'post') {

            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);

        } else {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
            if (!empty($data)) {
                $url .= '?' . http_build_query($data);
            }
        }

        Log::channel('pay')->info(__CLASS__ . ' request: ' . $url);
        Log::channel('pay')->info(var_export($data, true));

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/x-www-form-urlencoded']);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        curl_setopt($ch, CURLOPT_ENCODING, 'gzip');

        curl_setopt($ch, CURLOPT_USERAGENT, 'KrakenCassino');
        curl_setopt($ch, CURLOPT_TIMEOUT, 90);
        curl_setopt($ch, CURLOPT_MAXREDIRS, 10);

        curl_setopt($ch, CURLOPT_VERBOSE, true);

        $verbose = fopen('php://temp', 'w+');
        curl_setopt($ch, CURLOPT_STDERR, $verbose);

        $text = curl_exec($ch);
        $err = curl_error($ch);
        $resp_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $resp = json_decode($text, true);

        rewind($verbose);
        $verboseLog = stream_get_contents($verbose);

        Log::channel('pay')->info('curl verbose');
        Log::channel('pay')->info(htmlspecialchars($verboseLog));

        if ($err) {
            Log::channel('pay')->error($err);
        }

        Log::channel('pay')->info(__CLASS__ . ' resp:');
        Log::channel('pay')->info('code:' . $resp_code);
        Log::channel('pay')->info($text);
        Log::channel('pay')->info(var_export($resp, true));
        return $resp;
    }

    public static function depositCallback(Request $request)
    {
        Log::channel('pay')->info(__CLASS__.' callback deposit request');
        Log::channel('pay')->info(var_export($request->all(), true));

        $hash = $_POST['hash'];

        $data = [
            'platform_id' => (int) $_POST['platform_id'], // ID платформы
            'payment_id' => (int) $_POST['payment_id'], // ID валюты
            'order_id' => (int) $_POST['order_id'], // Payment ID Вашей платформы
            'amount' => $_POST['amount'], // сумма транзакции
            'txhash' => $_POST['txhash'], // Хэш или идентификатор транзакции. ID транзакции можно найти в истории транзакций PassimPay в вашем аккаунте.
            'address_from' => $_POST['address_from'], // адрес отправителя
            'address_to' => $_POST['address_to'], // адрес получателя
            'fee' => $_POST['fee'], // комиссия сети
        ];

        if (isset($_POST['confirmations']))
        {
            $data['confirmations'] = $_POST['confirmations']; // количество подтверждений сети (Bitcoin, Litecoin, Dogecoin, Bitcoin Cash)
        }

        if (isset($_POST['tag']))
        {
            $data['tag'] = $_POST['tag']; // For Ripple XRP
        }

        $payload = http_build_query($data);

        $api = new Passimpay();

        $payment_status = $api->orderStatus($data['order_id']);
        if ($payment_status['status'] === 'wait' && $payment_status['amount_paid'] == 0) {
            return 'OK';
        }

        if (!isset($hash) || $api->hash($payload) !== $hash)
        {
            Log::channel('pay')->error('check sign fail');
            return response('wrong sign', 500);
        }

        if (!isset($data['order_id'])) {
            Log::channel('pay')->error('order_id field not set');
            return response('', 500);
        }

        Log::channel('pay')->info('Start processing Passimpay transaction with id ' . $data['order_id']);

        /** @var PayoutTransaction $transaction */
        $transaction = PayoutTransaction::where('id', $data['order_id'])
            ->where('status_id', PayoutTransaction::STATUS_PASSIMPAY_PENDING)
            ->first();

        if ($transaction === null) {
            if (PayoutTransaction::where('id', $data['order_id'])
                    ->where('status_id', PayoutTransaction::STATUS_PASSIMPAY_SUCCESS)
                    ->first() !== null) {
                Log::channel('pay')->error('Transaction with id ' . $data['order_id'] . ' is already complete found. Request: ' . PHP_EOL . var_export($request->all(), true));
                return response('OK', 200);
            }
            Log::channel('pay')->error('Transaction with id ' . $data['order_id'] . ' is not found. Request: ' . PHP_EOL . var_export($request->all(), true));
            return response('transaction not found', 404);
        }

        // Find user which create this transaction.
        /** @var MUser $user $user */
        $user = MUser::where('id', $transaction->user_id)->first();

        if ($user === null) {
            Log::channel('pay')->error('User of the transaction is not found.');
            return response('user not found', 500);
        }

        if ($payment_status['status'] === 'error') {
            $transaction->status_id = PayoutTransaction::STATUS_PASSIMPAY_FAIL;
            $transaction->save();
            return 'OK';
        }

        $amount = $payment_status['amount_paid']?? $data['amount'];

        $currency = UserWallet::CURRENCY_USDT;
        $transaction->fee = 0 + ($data['fee']?? 0);
        if ($transaction->fee === 0) $transaction->fee = $amount * 0.01 + 1.6;

        Payin::completeTransaction($user, $transaction, $amount, PayoutTransaction::STATUS_PASSIMPAY_SUCCESS, $currency);

        return 'OK';
    }
}
