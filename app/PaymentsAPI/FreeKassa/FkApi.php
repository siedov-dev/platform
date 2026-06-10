<?php


namespace App\PaymentsAPI\FreeKassa;


use App\Models\PaySystemsConfig;
use App\Payin\Payin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Class FkApi
 * @package App\PaymentsAPI\FreeKassa
 * @see https://docs.freekassa.ru/
 */
class FkApi
{

    const PAYMENT_STATUS_NEW = 0;
    const PAYMENT_STATUS_PAYED = 1;
    const PAYMENT_STATUS_ERROR = 8;
    const PAYMENT_STATUS_CANCELED = 9;

    private string $api_url = "https://api.freekassa.ru/v1/";
    private string $key = "b4cb322d2287172857e5aa906039a60c";
    private string $password1 = "";
    private string $password2 = "";
    private int $shop_id = 0;

    private bool $is_error;
    private string $error_descr;

    public function __construct()
    {
        $conf = PaySystemsConfig::getSystemConfig(Payin::TYPE_FREEKASSA);
        $this->api_url = $conf['api_url'] ?? "https://api.freekassa.ru/v1/";
        $this->password1 = $conf['password1'] ?? "";
        $this->password2 = $conf['password2'] ?? "";
        $this->shop_id = $conf['shop_id'] ?? 0;
    }

    public function isError() : bool
    {
        return $this->is_error;
    }

    public function getErrorDescription()
    {
        return $this->error_descr;
    }

    public function getBalance($currency_name = null)
    {
        $method = "/balance";
        $data = [
            'shopId' => $this->shop_id,
        ];

        $balances =  $this->query($method, $data);
        if(empty($balances) || $this->isError()) return 0;

        $data = collect($balances['balance'])->pluck('value', 'currency');

        if($currency_name === null) return $data;

        if(isset($data[$currency_name])) return $data[$currency_name];
        return 0;
    }

    public function currencies()
    {
        $method = "/currencies";
        $data = [
            'shopId' => $this->shop_id,
        ];

        return $this->query($method, $data);
    }

    public function outCurrencies()
    {
        $method = "/withdrawals/currencies";
        $data = [
            'shopId' => $this->shop_id,
        ];

        return $this->query($method, $data);
    }

    public function createOrder($order_id, float $amount, string $currency_name, $pay_system_id, $user_email, $user_ip)
    {
        if ($currency_name === 'mBTC') $currency_name = 'BTC';
        $method = "/orders/create";
        $data = [
            'shopId' => $this->shop_id,
            'paymentId' => $order_id,
            'i' => $pay_system_id,
            'email' => $user_email,
            'ip' => $user_ip,
            'amount' => $amount,
            'currency' => $currency_name,
            'success_url' => route('pay.success', [], true),
            'failure_url' => route('pay.fail', [], true),
        ];

        return $this->query($method, $data);
    }

    public function createPayout($order_id, float $amount, string $currency_name, $pay_system_id, $account)
    {
        $method = "/withdrawals/create";
        $data = [
            'shopId' => $this->shop_id,
            'paymentId' => $order_id,
            'i' => $pay_system_id,
            'account' => $account,
            'amount' => $amount,
            'currency' => $currency_name,
        ];

        return $this->query($method, $data);
    }

    public function getPayoutStatus($order_id)
    {
        $method = "/withdrawals";
        $data = [
            'shopId' => $this->shop_id,
            'paymentId' => $order_id,
            'orderId' => $order_id,
        ];

        return $this->query($method, $data);
    }

    public function checkSign(string $amount, string $order_id, string $sign)
    {
        return $sign === md5($this->shop_id.':'.$amount.':'.$this->password2.':'.$order_id);
    }

    public function query($method, $data = [])
    {
        $this->is_error = false;
        $this->error_descr = '';

        Log::channel('pay')->info('FreeKassa query:' . var_export($data, true));
        $data['nonce'] = time();

        ksort($data);
        $sign = hash_hmac('sha256', implode('|', $data), $this->key);
        $data['signature'] = $sign;

        $request = json_encode($data);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->api_url.$method);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $request);
        $response = trim(curl_exec($ch));
        $c_errors = curl_error($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        Log::channel('pay')->info('FreeKassa response:' . $http_code.': ' . $response);

        if (!$response) {
            Log::channel('pay')->error('Can`t connect to FreeKassa payment system');
            Log::channel('pay')->error('curl error: '. var_export($c_errors, true));
            $this->is_error = true;
            $this->error_descr = "Can`t connect to FreeKassa payment system";
            throw new \Exception('Can`t connect to FreeKassa payment system');
            return false;
        }

        $resp = json_decode($response, true);

        if ($resp['type'] != 'success') {
            Log::channel('pay')->error('FreeKassa error');
            Log::channel('pay')->error(var_export($resp, true));
            $this->is_error = true;
            $this->error_descr = $resp['type'];
            return false;
        }

        return $resp;
    }

    public static function depositCallback(Request $request)
    {
        Log::channel('pay')->info(__CLASS__ . ' callback deposit request: ' . $request->fullUrl());
        Log::channel('pay')->info('getContent: ' . var_export($request->getContent(), true));
        Log::channel('pay')->info('all: ' . var_export($request->all(), true));

        $targetUrl = "http://127.0.0.1:23106/pay/freekassa";
        $response = Http::withHeaders($request->headers->all())
            ->withBody($request->getContent(), $request->header('Content-Type'))
            ->send($request->method(), $targetUrl);
        Log::channel('pay')->info('freekassa callback response ' . var_export($response->body(), true));
        return response($response->body(), $response->status())->withHeaders($response->headers());
    }
}
