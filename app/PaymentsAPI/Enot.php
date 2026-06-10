<?php


namespace App\PaymentsAPI;


use App\Models\PaySystemsConfig;
use App\Payin\Payin;
use Illuminate\Support\Facades\Log;

class Enot
{
    private int $id = 54741;
    private string $key = "5COLN_U9EopSh0C0MzbEVh5S5P8Tg-e1";
    private string $key2 = "WitXo72_6WVPvStDxJR5KzejTW6GF05p";
    private string $url = "";
    private string $email = "payboxultra@gmail.com";

    private int $id_main = 54741;
    private int $id_p2p = 54745;
    private string $key_main = "5COLN_U9EopSh0C0MzbEVh5S5P8Tg-e1";
    private string $key_main2 = "WitXo72_6WVPvStDxJR5KzejTW6GF05p";
    private string $key_ptp1 = "pl_P-cIneSS_lVpsqBG8ZtzUggjTVhfU";
    private string $key_ptp2 = "b0sMOaFMfOLo1f7CZDtvsFt72Vc6hcfK";

    private string $api_key = "0538fb2137ed60a06b3069eac6703958";

    private array $service_names = [
        'qw' => 'QIWI',
        'wm' => 'Webmoney',
        'ap' => 'Apple Pay',
        'cd' => 'Банковские карты',
        'ya' => 'Яндекс.Деньги',
        'pa' => 'Payeer',
        'pm' => 'Perfect Money',
        'bt' => 'Bitcoin',
        'gp' => 'Google Pay',
        'sp' => 'Samsung Pay ',
    ];

    const MODE_MAIN = 1;
    const MODE_P2P = 2;

    public function __construct(int $mode = self::MODE_MAIN)
    {
        $conf = PaySystemsConfig::getSystemConfig(Payin::TYPE_ENOT);
        $this->id_main = $conf['id_main'];
        $this->key_main = $conf['key_main'];
        $this->key_main2 = $conf['key_main2'];

        $this->id_p2p = $conf['id_p2p'];
        $this->key_ptp1 = $conf['key_ptp1'];
        $this->key_ptp2 = $conf['key_ptp2'];

        $this->setMode($mode);
    }

    /**
     * switch kassa
     * @param int $mode
     */
    public function setMode(int $mode): void
    {
        switch ($mode) {
            case self::MODE_P2P:
                $this->id = $this->id_p2p;
                $this->key = $this->key_ptp1;
                $this->key2 = $this->key_ptp2;
                break;
            case self::MODE_MAIN:
            default:
            $this->id = $this->id_main;
            $this->key = $this->key_main;
            $this->key2 = $this->key_main2;
                break;
        }
    }

    /**
     * get available payment methods
     * @return array
     * @throws \Exception
     */
    public function getPaymentMethods(): array
    {
        $response = $this->send_query('payment-methods', [
            'merchant_id' => $this->id,
            'secret_key' => $this->key,
        ]);

        if ($response !== false) {
            return $response['methods'];
        }

        return [];
    }

    /**
     * get payment service full name
     * @param $service
     * @return string
     */
    public function getServiceFullName($service): string
    {
        if (key_exists($service, $this->service_names)) {
            return $this->service_names[$service];
        }
        return 'Undifened';
    }

    /**
     * generates redirect url to enot payment page
     * @param float $order_amount
     * @param int $transaction_id
     * @param string $serice_name
     * @return string
     */
    public function genRedirectUrl(float $order_amount, int $transaction_id, string $serice_name): string
    {
//        return 'https://enot.io/pay?' . http_build_query([
        return 'https://Billing.cx/pay/step-one?' . http_build_query([
                'm' => $this->id,
                'oa' => $order_amount,
                'o' => $transaction_id,
                's' => $this->sign($order_amount, $transaction_id),
                'c' => 'Пополнение счета на ' . $order_amount . ' руб.',
                'p' => $serice_name,
                'cf' => ($this->id === $this->id_main? 'main' : 'p2p'),
                'success_url' => action('PayController@success'),
                'fail_url' => action('PayController@fail'),
            ]);
    }

    /**
     * kassa balance
     * @return false|mixed
     * @throws \Exception
     */
    public function balance()
    {
        return $this->send_query('balance', [
            'api_key' => $this->api_key,
            'email' => $this->email,
        ]);
    }

    /**
     * withdraw funds
     * @return false|mixed
     * @throws \Exception
     */
    public function payoff(string $method, float $amount, string $wallet_no, string $tr_id)
    {
        return $this->send_query('payoff', [
            'api_key' => $this->api_key,
            'email' => $this->email,
            'service' => $method,
            'wallet' => $wallet_no,
            'amount' => $amount,
            'orderid' => $tr_id,
        ]);
    }

    /**
     * payout info
     * @param string $order_id
     * @return false|mixed
     * @throws \Exception
     */
    public function payoffInfo(string $order_id)
    {
        return $this->send_query('payoff-info', [
            'api_key' => $this->api_key,
            'email' => $this->email,
            'orderid' => $order_id,
        ]);
    }

    /**
     * sign request
     * @param $order_amount
     * @param $payment_id
     * @return string
     */
    public function sign(float $order_amount, int $payment_id): string
    {
        return md5($this->id . ':' . $order_amount . ':' . $this->key . ':' . $payment_id);
    }

    public function sign2(string $order_amount, int $payment_id): string
    {
        return md5($this->id . ':' . $order_amount . ':' . $this->key2 . ':' . $payment_id);
    }

    /**
     * @param float $order_amount
     * @param int $payment_id
     * @param string $sign
     * @return bool
     */
    public function checkSign(float $order_amount, int $payment_id, string $sign): bool
    {
        return $sign === $this->sign($order_amount, $payment_id);
    }

    public function checkSign2(string $order_amount, int $payment_id, string $sign2): bool
    {
        return $sign2 === $this->sign2($order_amount, $payment_id);
    }

    /**
     * send query to enot api
     * @param array $data
     * @return mixed
     * @throws \Exception
     */
    private function send_query(string $method, ?array $data)
    {
        $json = json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

        Log::channel('pay')->info('Enot query:'.'https://enot.io/request/' . $method . '?' . http_build_query($data));

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_USERAGENT, 'KrakenCassino');
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_POST, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_URL, 'https://enot.io/request/' . $method . '?' . http_build_query($data));
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($ch, CURLOPT_TIMEOUT, 20);

        curl_setopt($ch, CURLOPT_VERBOSE, true);

        $verbose = fopen('php://temp', 'w+');
        curl_setopt($ch, CURLOPT_STDERR, $verbose);

        $response = curl_exec($ch);
        $err = curl_error($ch);
        $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        rewind($verbose);
        $verboseLog = stream_get_contents($verbose);

        Log::channel('pay')->info('curl verbose');
        Log::channel('pay')->info(htmlspecialchars($verboseLog));

        if ($err) {
            Log::channel('pay')->error($err);
        }

        curl_close($ch);

        Log::channel('pay')->info('Enot response code:'.$httpcode);
        Log::channel('pay')->info('Enot response:'.$response);

        return json_decode($response, true);
    }

}
