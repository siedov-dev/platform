<?php


namespace App\PaymentsAPI;


use App\Helpers\Currency;
use App\Models\PaySystemsConfig;
use App\MUser;
use App\Payin\Payin;
use App\PayoutTransaction;
use App\UserWallet;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

/**
 * Class BillLine
 * @package App\PaymentsAPI
 * @see https://billline.net/ru/api-ru/
 */
class BillLine
{

    const PAYOUT_TYPE_VISA_MASTER_UAH = 16;
    const PAYOUT_TYPE_BITCOIN = 2;
    const PAYOUT_TYPE_VISA_MASTER_RUB = 3;

    const STATUS_SUCCESS = 'Success';
    const STATUS_ERROR = 'Error';
    const STATUS_PENDING = 'Pending';
    const STATUS_BLOCKED = 'Blocked';

    const CODE_SUCCESS = 0;

    protected string $url = 'https://realpaysolutions.net/';
    protected string $secret_key = '0ea8cb57_3efdaf99';
    protected string $merchant_id = '6FP7HEAN9V4T3';

    protected static string $merchant_3 = '5CVKI7LPOCSCE';
    protected static string $merchant_4 = 'JPOH4M8K7BTVA';
    protected static string $merchant_5 = 'CIAJOSOKHRMOY';

    private int $thread = 1;

    public function __construct(int $thread = 1)
    {
        $conf = PaySystemsConfig::getSystemConfig(Payin::TYPE_BILLINE);
        $this->url = $conf['url'] ?? 'https://realpaysolutions.net/';

        if ($thread === 5) {
            $this->secret_key = '54b90b74_cc039b03';
            $this->merchant_id = 'CIAJOSOKHRMOY';
        } elseif ($thread === 4) {
            $this->secret_key = '9276c461_ebc3fd98';
            $this->merchant_id = 'JPOH4M8K7BTVA';
        } elseif ($thread === 3) {
            $this->secret_key = '695f0b61_8623036f';
            $this->merchant_id = '5CVKI7LPOCSCE';
        } elseif ($thread === 2) {
            $this->secret_key = $conf['secret_key_2'] ?? '0ea8cb57_3efdaf99';
            $this->merchant_id = $conf['merchant_id_2'] ?? '6FP7HEAN9V4T3';
        } else {
            $this->secret_key = $conf['secret_key'] ?? '4460d8fb_8d489b6d';
            $this->merchant_id = $conf['merchant_id'] ?? 'U8UXWYU0OHBLG';
        }
        $this->thread = $thread;
    }

    public static function getInstanceByMerchant(string $merchant): BillLine
    {
        $conf = PaySystemsConfig::getSystemConfig(Payin::TYPE_BILLINE);
        if ($merchant === self::$merchant_3) return new BillLine(3);
        if ($merchant === self::$merchant_4) return new BillLine(4);
        if ($merchant === self::$merchant_5) return new BillLine(5);
        return new BillLine($merchant === $conf['merchant_id_2']? 2 : 1);
    }


    /**
     * @param array $data
     * @return array|string
     * @throws \Throwable
     */
    public function viewRedirectForm(array $data, string $method, array $requsites = [])
    {
        $amount = $data['amount'];
        $currency = $data['currency'];

        if ($this->thread === 3) {
            $currency = 'EUR';
            $amount = round(Currency::convert($amount, 'UAH', 'EUR'), 2);
        }

        if ($this->thread === 4) {
            $currency = 'USD';
            $amount = round(Currency::convert($amount, 'UAH', 'USD'), 2);

            $view = view('pay.billLine-usd-form',
                [
                    'url' => $this->url,
                    'merchant' => $this->merchant_id,
                    'order' => $data['id'],
                    'amount' => $amount,
                    'currency' => $currency,
                    'custom' => $this->thread,
                    'user_id' => Auth::id(),
                    'email' => $requsites['email'],
                    'from_first_name' => $requsites['from_first_name'],
                    'from_last_name' => $requsites['from_last_name'],
                    'billing_street' => $requsites['billing_street'],
                    'billing_town' => $requsites['billing_town'],
                    'from_country' => $requsites['from_country'],
                    'billing_post_code' => $requsites['billing_post_code'],
                ])->render();

            Log::channel('pay')->info('Billine form:' . $view);
            return $view;
        }

        $view = view('pay.billLine-form',
            [
                'url' => $this->url,
                'merchant' => $this->merchant_id,
                'order' => $data['id'],
                'amount' => $amount,
                'currency' => $currency,
                'custom' => $this->thread,
                'user_id' => Auth::id(),
                'first_name' => $requsites['first_name'],
                'last_name' => $requsites['last_name'],
            ])->render();

        Log::channel('pay')->info('Billine form:' . $view);
        return $view;
    }

    /**
     * @param array $data
     * @return bool
     */
    public function checkSign(array $data): bool
    {
        $dataSet = $data;
        $sign = $dataSet['co_sign'];
        unset($dataSet['co_sign']);
        ksort($dataSet, SORT_STRING);
        array_push($dataSet, $this->secret_key);
        $signString = implode(':', $dataSet);
        $calc_sign = base64_encode(md5($signString, true));

        if ($sign != $calc_sign) {
            return false;
        }

        return true;
    }

    /**
     * get balance
     * @param string $currency_name currency name (UAH, RUB)
     * @return bool|mixed
     */
    public function getBalance(string $currency_name)
    {

        $request = [
            'merchant' => $this->merchant_id,
            'currency' => $currency_name,
        ];

        $result = $this->send_query('/payment/balance', $request);
        if (!empty($result) && $result['status'] == self::STATUS_SUCCESS) return $result['balance'];

        return false;
    }

    /**
     * get payment status
     * @param $order
     * @param $co_inv_id
     * @return bool|mixed
     */
    public function paymentStatus($order, $co_inv_id)
    {

        $request = [
            'merchant' => $this->merchant_id,
            'order' => $order,
            'co_inv_id' => $co_inv_id,
        ];

        return $this->send_query('/payment/status', $request);
    }

    /**
     * @param string $account
     * @param float $amount
     * @param int $id
     * @param int $currency currency code (UserWaller)
     * @return bool|mixed
     */
    public function payout(string $account, float $amount, int $id, int $currency)
    {

        $request = [
            'merchant' => $this->merchant_id,
            'method' => $currency == UserWallet::CURRENCY_UAH ? self::PAYOUT_TYPE_VISA_MASTER_UAH : self::PAYOUT_TYPE_VISA_MASTER_RUB,
            'payout_id' => $id,
            'account' => $account,
            'amount' => $amount,
            'currency' => UserWallet::currencyName($currency),
        ];

        return $this->send_query('/merchant/api/payout_send', $request);
    }

    /**
     * @param int $id
     * @return bool|mixed
     */
    public function getPayoutStatus(int $id)
    {
        $request = [
            'merchant' => $this->merchant_id,
            'payout_id' => $id,
        ];

        return $this->send_query('/merchant/api/payout_status', $request);
    }

    /**
     * @param $data
     * @return array
     */
    private function sign($data): array
    {
        $dataSet = $data;
        ksort($dataSet, SORT_STRING);
        array_push($dataSet, $this->secret_key);
        $signString = implode(':', $dataSet);
        $calc_sign = base64_encode(md5($signString, true));
        $data['sign'] = $calc_sign;
        return $data;
    }

    /**
     * sign and send query to BillLine
     * @param string $method
     * @param array|null $data
     * @return bool|mixed
     */
    private function send_query(string $method, ?array $data)
    {
        $data = $this->sign($data);

        Log::channel('pay')->info('BillLine url ' . $this->url . $method);
        Log::channel('pay')->info('BillLine query data:' . var_export($data, true));

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_USERAGENT, 'KrakenCassino');
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_URL, $this->url . $method);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
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
        curl_close($ch);

        Log::channel('pay')->info('BillLine code:' . $httpcode);
        Log::channel('pay')->info('BillLine response:' . $response);

        if (!$response) {
            Log::error('Can`t connect to BL payment system');
            return false;
        }

        $resp = json_decode($response, true);

        return $resp;
    }

}
