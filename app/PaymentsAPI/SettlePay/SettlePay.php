<?php


namespace App\PaymentsAPI\SettlePay;


use App\Models\PaySystemsConfig;
use App\Payin\Payin;
use Illuminate\Support\Facades\Log;

/**
 * @see https://docs.settlepay.net/
 * Class SettlePay
 * @package App\PaymentsAPI
 */
class SettlePay
{
    /** @var bool $is_error */
    public $is_error = false;

    /** @var string $error_descr */
    public $error_descr = "";

    protected string $api_url = "https://api.settlepay.net";

    protected string $api_token = "4coLiov5KzF0y3ZbfuGC7ADmoCdVdMf-";
    protected int $account_id = 2986;
    protected int $wallet_id = 3667;
    protected int $point_id = 3028;

    protected int $payin_service = 1060; //unverified service
    protected int $payout_service = 0; //unverified service

    protected string $ver_api_token;
    protected int $ver_account_id;
    protected int $ver_wallet_id;
    protected int $ver_point_id;
    protected int $ver_payin_service; //verified service
    protected int $ver_payout_service; //verified service

    protected string $unrub_api_token;
    protected int $unrub_account_id;
    protected int $unrub_wallet_id;
    protected int $unrub_point_id;
    protected int $unrub_payin_service; //verified service
    protected int $unrub_payout_service; //verified service

    private array $_conf;

    const STATUS_NEW = 0;
    const STATUS_SUCCESS = 1;
    const STATUS_FAILED = 2;
    const STATUS_CANCELLED = 3;
    const STATUS_REVERSED = 4;
    const STATUS_EXPIRED = 5;
    const STATUS_HOLD = 6;
    const STATUS_PENDING = 10;

    const MODE_UNVERIFIED = 0;
    const MODE_VERIFIED = 1;
    const MODE_RUB_UNVERIFIED = 2;

    public function __construct()
    {
        $conf = PaySystemsConfig::getSystemConfig(Payin::TYPE_SETTLEPAY);
        $this->_conf = $conf;
        $this->api_url = $conf['api_url'] ?? "https://api.settlepay.net";
        //unverified
        $this->api_token = $conf['api_token'] ?? "4coLiov5KzF0y3ZbfuGC7ADmoCdVdMf-";
        $this->account_id = $conf['account_id'] ?? 2986;
        $this->wallet_id = $conf['wallet_id'] ?? 3667;
        $this->point_id = $conf['point_id'] ?? 3028;
        $this->payin_service = $conf['payin_service'] ?? 1060;
        $this->payout_service = $conf['payout_service'] ?? 0;
        //verified
        $this->ver_api_token = $conf['ver_api_token'] ?? "ivp3Ld8pntd6AOaorW2IwlW6rOxoDKsF";
        $this->ver_account_id = $conf['ver_account_id'] ?? 2986;
        $this->ver_wallet_id = $conf['ver_wallet_id'] ?? 3667;
        $this->ver_point_id = $conf['ver_point_id'] ?? 3027;
        $this->ver_payin_service = $conf['ver_payin_service'] ?? 646;
        $this->ver_payout_service = $conf['ver_payout_service'] ?? 264;
        //unverified rub
        $this->unrub_api_token = $conf['unrub_api_token'] ?? "ivp3Ld8pntd6AOaorW2IwlW6rOxoDKsF";
        $this->unrub_account_id = $conf['unrub_account_id'] ?? 2986;
        $this->unrub_wallet_id = $conf['unrub_wallet_id'] ?? 9684;
        $this->unrub_point_id = $conf['unrub_point_id'] ?? 3027;
        $this->unrub_payin_service = $conf['unrub_payin_service'] ?? 3467;
        $this->unrub_payout_service = $conf['unrub_payout_service'] ?? 3434;
    }

    /**
     * get accounts info
     * @return bool|mixed
     */
    public function accountInfo()
    {
        $method = '/account/info';
        $params = [];
        return $this->sendQuery($method, $params);
    }

    public function setMode($mode)
    {
        if ($mode == self::MODE_UNVERIFIED) {
            $this->api_token = $this->_conf['api_token'] ?? "4coLiov5KzF0y3ZbfuGC7ADmoCdVdMf-";
            $this->account_id = $this->_conf['account_id'] ?? 2986;
            $this->wallet_id = $this->_conf['wallet_id'] ?? 3667;
            $this->point_id = $this->_conf['point_id'] ?? 3028;
            $this->payin_service = $this->_conf['payin_service'] ?? 1060;
            $this->payout_service = $this->_conf['payout_service'] ?? 0;

        }

        if ($mode == self::MODE_VERIFIED) {
            $this->api_token = $this->_conf['ver_api_token'] ?? "ivp3Ld8pntd6AOaorW2IwlW6rOxoDKsF";
            $this->account_id = $this->_conf['ver_account_id'] ?? 2986;
            $this->wallet_id = $this->_conf['ver_wallet_id'] ?? 3667;
            $this->point_id = $this->_conf['ver_point_id'] ?? 3027;
            $this->payin_service = $this->_conf['ver_payin_service'] ?? 646;
            $this->payout_service = $this->_conf['ver_payout_service'] ?? 264;
        }

        if ($mode == self::MODE_RUB_UNVERIFIED) {
            $this->api_token = $this->_conf['unrub_api_token'] ?? "ivp3Ld8pntd6AOaorW2IwlW6rOxoDKsF";
            $this->account_id = $this->_conf['unrub_account_id'] ?? 2986;
            $this->wallet_id = $this->_conf['unrub_wallet_id'] ?? 9684;
            $this->point_id = $this->_conf['unrub_point_id'] ?? 3027;
            $this->payin_service = $this->_conf['unrub_payin_service'] ?? 3467;
            $this->payout_service = $this->_conf['unrub_payout_service'] ?? 3434;
        }

//        if ($mode == self::MODE_UNVERIFIED) {
//            $this->account_id = 2986;
//            $this->wallet_id = 3667;
//            $this->point_id = 3028;
//            $this->api_token = "4coLiov5KzF0y3ZbfuGC7ADmoCdVdMf-";
//            $this->payin_service = 1060; //unverified service
//            $this->payout_service = 0; //unverified service
//        }
//        if($mode == self::MODE_VERIFIED){
//            $this->account_id = 2986;
//            $this->wallet_id = 3667;
//            $this->point_id = 3027;
//            $this->api_token = "ivp3Ld8pntd6AOaorW2IwlW6rOxoDKsF";
//
//            $this->payin_service = 646; //verified service
//            $this->payout_service = 264; //verified service
//        }
//
//        if($mode == self::MODE_RUB_UNVERIFIED){
//            $this->account_id = 2986;
//            $this->wallet_id = 9684;
//            $this->point_id = 3027;
//            $this->api_token = "ivp3Ld8pntd6AOaorW2IwlW6rOxoDKsF";
//
//            $this->payin_service = 3467; //verified service
//            $this->payout_service = 3434; //verified service
//        }
    }

    public function getPayinService()
    {
        return $this->payin_service;
    }

    public function getPayoutService()
    {
        return $this->payout_service;
    }

    public function getBalance($currency_name)
    {
        $info = $this->accountInfo();

        if (empty($info['wallets'])) return false;

        foreach ($info['wallets'] as $wallet) {
            if ($wallet['currency'] == $currency_name) return $wallet['balance'];
        }

        return false;
    }

    public function cardCheck($card_number)
    {
        $method = '/cards/check';
        $params = [
            'card_number' => $card_number
        ];
        return $this->sendQuery($method, $params);
    }

    public function serviceList()
    {
        $method = '/service/list';
        $params = [];
        return $this->sendQuery($method, $params);
    }

    public function transactionCreate($trans_id, $user_id, $ip, $cents, $curr_name, $service_id)
    {
        $method = '/transaction/create';
        $params = [
            'locale' => 'ru',
            'external_transaction_id' => $trans_id,
            'external_customer_id' => $user_id,
            'customer_ip_address' => $ip,
            'amount' => $cents,
            'amount_currency' => $curr_name,
            'service_id' => $service_id,
            'account_id' => $this->account_id,
            'wallet_id' => $this->wallet_id,
            'description' => 'Пополнение Krakencassino ' . number_format($cents / 100, 2) . $curr_name,
            'point' => [
                'callback_url' => 'https://krakencassino.com/api/settlepay/pay?transaction=' . $trans_id,
                'success_url' => action('PayController@success'),
                'fail_url' => action('PayController@fail'),
                'cancel_url' => 'https://krakencassino.com/api/settlepay/pay?transaction=' . $trans_id . '&cancel="true"',
                'return_url' => route('pay.close-tab'),
            ]
        ];
        return $this->sendQuery($method, $params);
    }

    public function transactionPay($trans_id, $apl_id, $user_id, $ip, $cents, $curr_name, $service_id, $card_number)
    {
        $method = '/transaction/pay';
        $params = [
            'locale' => 'ru',
            'external_transaction_id' => $trans_id,
            'external_customer_id' => $user_id,
            'customer_ip_address' => $ip,
            'amount' => $cents,
            'amount_currency' => $curr_name,
            'service_id' => $service_id,
            'account_id' => $this->account_id,
            'wallet_id' => $this->wallet_id,
            'description' => 'Выплата Krakencassino ' . number_format($cents / 100, 2) . $curr_name,
            'fields' => [
                'card_number' => $card_number,
            ],
            'point' => [
                'callback_url' => 'https://krakencassino.com/api/settlepay/payout?transaction=' . $trans_id . '&app_id=' . $apl_id,
                'return_url' => action('AuthController@status'),
            ]
        ];
        return $this->sendQuery($method, $params);
    }

    public function isFinalStatus($status)
    {
        return !in_array($status, [self::STATUS_NEW, self::STATUS_HOLD, self::STATUS_PENDING]);
    }

    public function transactionFind($trans_id)
    {
        $method = '/transaction/find';
        $params = [
            'external_transaction_id' => $trans_id,
        ];
        return $this->sendQuery($method, $params);
    }

    /**
     * Get auth object
     * @return \stdClass
     */
    private function getAuthObj(): \stdClass
    {
        $auth = new \stdClass();
        $auth->point = $this->point_id;
        $auth->key = time();
        $auth->hash = md5($this->point_id . $this->api_token . $auth->key);
        return $auth;
    }

    /**
     * Converts array to http headers
     * @param array $ar_headers array with headers
     * @return array headers format "header-name: value"
     */
    private function arrayToHeaders(array $ar_headers)
    {
        $curl_headers = [];
        foreach ($ar_headers as $k => $v) {
            $curl_headers[] = '' . $k . ': ' . $v;
        }
        return $curl_headers;
    }

    /**
     * Send query to SettlePay
     * @param string $method
     * @param array|null $data
     * @return bool|mixed
     */
    private function sendQuery(string $method, ?array $data = [])
    {
        $this->is_error = false;
        $data['auth'] = $this->getAuthObj();

        $json = json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

        Log::channel('pay')->info('SettlePay url ' . $this->api_url . $method);
        Log::channel('pay')->info('SettlePay query:' . $json);

        $headers = [
            'Content-Type' => 'application/json',
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_USERAGENT, 'KrakenCassino');
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $this->arrayToHeaders($headers));
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_URL, $this->api_url . $method);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($ch, CURLOPT_TIMEOUT, 20);

        $response = curl_exec($ch);

        curl_close($ch);

        Log::channel('pay')->info('SettlePay response:' . $response);

        if (!$response) {
            Log::channel('pay')->error('Can`t connect to SettlePay payment system');
            $this->is_error = true;
            $this->error_descr = "Can`t connect to SettlePay payment system";
            return false;
        }

        $resp = json_decode($response, true);

        if ($resp['error']['code'] != 0) {
            Log::channel('pay')->error('SettlePay error');
            Log::channel('pay')->error(var_export($resp, true));
            $this->is_error = true;
            $this->error_descr = $resp['error']['title'];
            return false;
        }

        return $resp['response'];
    }

}
