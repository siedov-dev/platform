<?php


namespace App\PaymentsAPI\InterKassa;


use App\Helpers\Currency;
use App\Models\PaymentSystems;
use App\Models\PaySystemsConfig;
use App\Payin\Payin;
use Illuminate\Support\Facades\Log;

/**
 * Class InterKassa
 * @package App\PaymentsAPI\InterKassa
 * @see http://docs.interkassa.com/
 */
class IkInrPtp
{
    private string $user_id = '6002dac136affc51ea3fed70';
    private string $key = 'eQLlBS08pwg9xgeVrBbW5aJH9liCjLFk';

    private string $business_acc = '6002dae67cff1d57022b3518';
    private int $purse_id = 815011394805;
    private $_conf;

    private string $url = 'https://api.interkassa.com';
    private string $version = 'v1';

    private string $sci_url = 'https://sci.interkassa.com';

    private string $p2p_merchant = '62f0cbe77bd7c8400e74cc8d';
    private string $p2p_key = "268Ci5Y1DuRryUAb37pfOWGmQNmoRAuB";

    public function __construct()
    {
        $conf = PaySystemsConfig::getSystemConfig(Payin::TYPE_IKINR_PTP);
        $this->url = $conf['url'] ?? "https://api.interkassa.com";
        $this->sci_url = $conf['sci_url'] ?? "https://sci.interkassa.com";
        $this->version = $conf['version'] ?? "v1";
        $this->user_id = $conf['user_id'] ?? "6002dac136affc51ea3fed70";
        $this->key = $conf['key'] ?? "eQLlBS08pwg9xgeVrBbW5aJH9liCjLFk";
        $this->business_acc = $conf['business_acc'] ?? "6002dae67cff1d57022b3517";
        $this->purse_id = $conf['purse_id'] ?? "402958662039";
        $this->p2p_merchant = $conf['checkout_id'] ?? "62f0cbe77bd7c8400e74cc8d";
        $this->p2p_key = $conf['checkout_key'] ?? "268Ci5Y1DuRryUAb37pfOWGmQNmoRAuB";
    }

    public function account()
    {
        $method = 'account';
        $httpMethod = 'get';
        $data = [
            'ik_int' => 'json',

        ];
        return $this->query($method, $httpMethod, true, $data);
    }

    public function checkout()
    {
        $method = 'checkout';
        $httpMethod = 'get';
        $data = [
            'ik_int' => 'json',

        ];
        return $this->query($method, $httpMethod, true, $data);
    }

    public function purse()
    {
        $method = 'purse';
        $httpMethod = 'get';
        $data = [
            'ik_int' => 'json',

        ];
        return $this->query($method, $httpMethod, true, $data);
    }

    public function payoutMethods()
    {
        $method = 'paysystem-output-payway';
        $httpMethod = 'get';
        $data = [
            'ik_int' => 'json',
            'purseId' => $this->purse_id,
        ];

        return $this->query($method, $httpMethod, true, $data);
    }

    public function getSciPayways($id, $amount, $currency = 'INR')
    {
        $data = [
            'ik_co_id' => $this->p2p_merchant,
            'ik_pm_no' => $id,
            'ik_am' => $amount,
            'ik_cur' => $currency,
            'ik_desc' => 'Пополнение счета',
            'ik_act' => 'payways',
            'ik_int' => 'json',
        ];

        return $this->querySci($data);
    }

    public function getSciPayCost($id, $amount, $currency = 'INR')
    {
        $data = [
            'ik_co_id' => $this->p2p_merchant,
            'ik_pm_no' => $id,
            'ik_am' => $amount,
            'ik_cur' => $currency,
            'ik_desc' => 'Пополнение счета',
            'ik_act' => 'payways',
            'ik_int' => 'json',
            'ik_pw_via' => 'card_cpaytrz_merchant_inr',
        ];

        return $this->querySci($data);
    }

    public function getSciPayinForm($id, $amount, $method, $currency = 'INR')
    {
        $data = [
            'ik_co_id' => $this->p2p_merchant,
            'ik_pm_no' => $id,
            'ik_am' => $amount,
            'ik_cur' => $currency,
            'ik_desc' => 'Пополнение счета',
            'ik_act' => 'process',
            'ik_int' => 'json',
            'ik_payment_currency' => $currency,
            'ik_payment_method' => $method,
            'ik_suc_u' => action('PayController@success'),
            'ik_fal_u' => action('PayController@fail'),
        ];

        return $this->querySci($data);
    }

    public function createWithdraw($amount, $tr_id, array $requisites, string $method)
    {
        $payway = $this->getPaywayId($method);
        $method = 'withdraw';
        $httpMethod = 'post';
        $data = [
            'ik_int' => 'json',
            'paymentNo' => $tr_id,
            'amount' => $amount,
            'action' => 'process',
            'purseId' => $this->purse_id,
            'currency' => 'inr',
            'paywayId' => $payway,
            "calcKey" => "psPayeeAmount",
            'useShortAlias' => false,
            'details' => $requisites,
        ];
        Log::channel('payout')->info('ik_inr payout params:'. var_export($data, true));
        return $this->query($method, $httpMethod, true, $data);
    }

    public function getPaywayId($method)
    {
        $pay_system = PaymentSystems::getPayoutSystemForMethod($method);
        if ($pay_system !== null) {
            return $pay_system->bank_alias;
        } else {
            Log::channel('pay')->error('PaymentSystems not found for alias '.$method);
            throw new \Exception('PaymentSystems not found for alias '.$method);
        }
    }

    public function payoutStatus($id)
    {
        $method = 'withdraw/' . $id;
        $httpMethod = 'get';
        $data = [
        ];

        return $this->query($method, $httpMethod, true, $data);
    }

    public function redirectToSci($id, $amount, $method, $currency = 'RUB')
    {
        $resp = $this->getSciPayinForm($id, $amount, $method, $currency);
        Log::channel('pay')->info('InterKassa INR p2p responce:' . var_export($resp, true));
        return $resp['resultData']['paymentForm']['action'];
    }

    public function checkSign(&$data)
    {
        $sign = $data['ik_sign'];
        Log::channel('pay')->info('ik_sign:' . $sign);
        Log::channel('pay')->info('sign:' . $this->sign($data));
        return $sign === $this->sign($data, $this->p2p_key);
    }

    private function sign($dataSet, $key = null)
    {
        unset($dataSet['ik_sign']); // Delete string with signature from dataset
        ksort($dataSet, SORT_STRING); // Sort elements in array by var names in alphabet queue
        array_push($dataSet, $key ?? $this->key); // Adding secret key at the end of the string
        $signString = implode(':', $dataSet); // Concatenation calues using symbol ":"
        $sign = base64_encode(hash('sha256', $signString, true)); // Get sha256 hash as binare view using generate string and code it in BASE64
        return $sign; // Return the result
    }

    private function query($method, $httpMethod, $isPrivate = false, array $data = [])
    {
        $url = $this->url . '/' . $this->version . '/' . $method;

        $ch = curl_init();

        if ($httpMethod === 'post') {

            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));

        } else {

            $url .= '?' . http_build_query($data);
        }

        if ($isPrivate) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Authorization: Basic ' . base64_encode($this->user_id . ':' . $this->key),
                'Ik-Api-Account-Id: ' . $this->business_acc,
            ],
            );
        }

        curl_setopt($ch, CURLOPT_HEADER, 0);

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        curl_setopt($ch, CURLOPT_USERAGENT, 'KrakenCassino');
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);

        return json_decode(curl_exec($ch), true);
    }

    private function querySci(array $data = [])
    {
        $url = $this->sci_url . '/';
        $data['ik_int'] = 'json';
        $data['ik_sign'] = $this->sign($data, $this->p2p_key);


        Log::channel('pay')->info(__CLASS__ . __METHOD__ . "request " . PHP_EOL . var_export($data, true));
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Basic ' . base64_encode($this->user_id . ':' . $this->key),
            'Ik-Api-Account-Id: ' . $this->business_acc,
        ],
        );

        curl_setopt($ch, CURLOPT_HEADER, 0);

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        curl_setopt($ch, CURLOPT_USERAGENT, 'KrakenCassino');
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);

        $resp = json_decode(curl_exec($ch), true);
        Log::channel('pay')->info(__CLASS__ . __METHOD__ . "response " . PHP_EOL . var_export($resp, true));
        return $resp;
    }
}
