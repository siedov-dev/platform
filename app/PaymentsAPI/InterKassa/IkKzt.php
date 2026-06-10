<?php


namespace App\PaymentsAPI\InterKassa;


use App\Helpers\Currency;
use App\Models\PaymentSystems;
use App\Models\PaySystemsConfig;
use App\MUser;
use App\Payin\Payin;
use CURLFile;
use Illuminate\Support\Facades\Log;

/**
 * Class InterKassa
 * @package App\PaymentsAPI\InterKassa
 * @see http://docs.interkassa.com/
 */
class IkKzt
{
    private string $user_id = '6002dac136affc51ea3fed70';
    private string $key = 'eQLlBS08pwg9xgeVrBbW5aJH9liCjLFk';

    private string $business_acc = '6002dae67cff1d57022b3518';
    private int $purse_id = 815011394805;
    private $_conf;

    private string $url = 'https://api.interkassa.com';
    private string $version = 'v1';

    private string $sci_url = 'https://sci.interkassa.com';

    private string $merchant = '62f0cbe77bd7c8400e74cc8d';
    private string $p2p_key = "268Ci5Y1DuRryUAb37pfOWGmQNmoRAuB";

    private string $check_1 = '62f0cbd8caf62630aa243a4a';
    private string $check_1_key = '1mlAPfRol2tJsbXVBiqIyD1ujYoo2rKC';
    private string $check_2 = '62f0c20dcaf62630aa2422e8';
    private string $check_2_key = '1F3SoyfwdJ3ONP582awehNOVUY13OBoy';
    private string $check_usdt = '62f0cbe74dfbdf1e1b552e43';
    private string $check_usdt_key = '1pT0L2hrkyuZ00Jnu9qajiTc5XFbnuS6';
    private string $check_apm = '62f0cbf54dfbdf1e1b552e7d';
    private string $check_apm_key = '2rFp7X27vKq0XATMiWQ1ldn6oMDZp8K6';

    public function __construct(string $method = '', MUser $user = null)
    {
        $conf = PaySystemsConfig::getSystemConfig(Payin::TYPE_IKKZT);
        $this->url = $conf['url'] ?? "https://api.interkassa.com";
        $this->sci_url = $conf['sci_url'] ?? "https://sci.interkassa.com";
        $this->version = $conf['version'] ?? "v1";
        $this->user_id = $conf['user_id'] ?? "6002dac136affc51ea3fed70";
        $this->key = $conf['key'] ?? "eQLlBS08pwg9xgeVrBbW5aJH9liCjLFk";
        $this->business_acc = $conf['business_acc'] ?? "6002dae67cff1d57022b3517";
        $this->purse_id = $conf['purse_id'] ?? "402958662039";
        $this->p2p_key = $conf['checkout_key_kzt1'] ?? "1mlAPfRol2tJsbXVBiqIyD1ujYoo2rKC";
        $this->merchant = $conf['checkout_id_kzt'] ?? "62f0cbd8caf62630aa243a4a";

        $this->check_1 = $conf['checkout_id_1'];
        $this->check_1_key = $conf['checkout_1_key'];
        $this->check_2 = $conf['checkout_id_2'];
        $this->check_2_key = $conf['checkout_2_key'];
        $this->check_usdt = $conf['checkout_id_usd'];
        $this->check_usdt_key = $conf['checkout_usd_key'];
        $this->check_apm = $conf['checkout_id_apm'];
        $this->check_apm_key = $conf['checkout_apm_key'];

        if (preg_match('#^.*\.(.*)$#', $method, $match)) {
            $method = $match[1];
        }

        if ($method === 'IkKzt.card' || $method === 'card') {
            $this->setCheckout('1');
            if ($user->getDepositsCount() >= 3) {
                $this->setCheckout('2');
            }
        }

        $apm = [
            'litecoin',
            'bitcoin',
            'ethereum',
            'dogecoin',
            'tether',
            'bnb',
            'usdttrc',

            'zcash',
            'ripple',
            'dash',
            'btccash',
            'apm',
            'Apm',
        ];

        if (in_array($method, $apm)) {
            $this->setCheckout('apm');
        }

        if (in_array($method, ['visa', 'mastercard'])) {
            $this->setCheckout('usdt');
        }
    }

    public function setCheckout(string $type): bool
    {
        switch ($type) {
            case '1':
                $this->merchant = $this->check_1;
                $this->p2p_key = $this->check_1_key;
                return true;
            case '2':
                $this->merchant = $this->check_2;
                $this->p2p_key = $this->check_2_key;
                return true;
            case 'usdt':
                $this->merchant = $this->check_usdt;
                $this->p2p_key = $this->check_usdt_key;
                return true;
            case 'apm':
                $this->merchant = $this->check_apm;
                $this->p2p_key = $this->check_apm_key;
                return true;
        }
        return false;
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

    public function payoutMethods($purse = null)
    {
        $method = 'paysystem-output-payway';
        $httpMethod = 'get';
        $data = [
            'ik_int' => 'json',
            'purseId' => $purse?? $this->purse_id,
        ];

        return $this->query($method, $httpMethod, true, $data);
    }

    public function getSciPayways($id, $amount, $currency = 'KZT')
    {
        $data = [
            'ik_co_id' => $this->merchant,
            'ik_pm_no' => $id,
            'ik_am' => $amount,
            'ik_cur' => $currency,
            'ik_desc' => 'Пополнение счета',
            'ik_act' => 'payways',
            'ik_int' => 'json',
        ];

        return $this->querySci($data);
    }

    public function getSciPayCost($id, $amount, $currency = 'KZT')
    {
        $data = [
            'ik_co_id' => $this->merchant,
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

    public function getSciPayinForm($id, $amount, $method, $currency = 'KZT')
    {
        if ($method === 'visa' || $method === 'mastercard') {
            $amount = round(Currency::convert($amount, 'KZT', 'USD'), 2);
            $currency = 'USD';
        }

        $p_currency = $currency;

        if (in_array($method, [
            'zcash',
            'ripple',
            'dash',
            'btccash',

//            'litecoin',
//            'bitcoin',
//            'ethereum',
//            'dogecoin',
//            'tether',
//            'bnb',
//            'usdttrc',
        ])) {
            $p_currency = 'USD';
        }

        $data = [
            'ik_co_id' => $this->merchant,
            'ik_pm_no' => $id,
            'ik_am' => $amount,
            'ik_cur' => $currency,
            'ik_desc' => 'Пополнение счета',
            'ik_act' => 'process',
            'ik_int' => 'json',
            'ik_payment_currency' => $p_currency,
            'ik_payment_method' => $method,
            'ik_suc_u' => action('PayController@success'),
            'ik_fal_u' => action('PayController@fail'),
        ];

        return $this->querySci($data);
    }

    public function createWithdraw($amount, $tr_id, array $data, string $method)
    {
        $requisites = $data;
        $payway = $this->getPaywayId($method);
        $curr = 'KZT';

        if ($method === 'IkKzt.apm') {
            $repl = $this->replenishInfo($data['phone'], 'kz');
            Log::channel('payout')->info('replenish info:' . var_export($repl, true));
            if (empty($repl) || $repl['status'] !== 'ok' || empty($repl['data'][0] ?? [])) {
                Log::channel('payout')->info('replenish info error: no resp or empty payout methods ' . var_export($repl, true));
                return false;
            }
            $curr = 'eur';
            $amount = Currency::convert($amount, 'KZT', 'EUR');
            $requisites = [
                'accountNumber' => $data['phone'],
                'skuCode' => $repl['data'][0]['skuCode'],
            ];
        }

        if (in_array($method, ['IkKzt.visa', 'IkKzt.mastercard'])) {
            $httpMethod = 'post';
            $data = [
                'ik_int' => 'json',
                'paymentNo' => $tr_id,
                'amount' => $amount,
                'action' => 'process',
                'purseId' => 451300823021,
                'currency' => $curr,
                "calcKey" => "psPayeeAmount",
                'useShortAlias' => true,
                'method' => explode('.', $method)[1],
                'details' => $requisites,
            ];
            Log::channel('payout')->info('ik_kzt payout params:' . var_export($data, true));
            return $this->query('withdraw', $httpMethod, true, $data);
        }

        $method = 'withdraw';
        $httpMethod = 'post';
        $data = [
            'ik_int' => 'json',
            'paymentNo' => $tr_id,
            'amount' => $amount,
            'action' => 'process',
            'purseId' => $this->purse_id,
            'currency' => $curr,
            'paywayId' => $payway,
            "calcKey" => "psPayeeAmount",
            'useShortAlias' => false,
            'details' => $requisites,
        ];
        Log::channel('payout')->info('ik_kzt payout params:' . var_export($data, true));
        return $this->query($method, $httpMethod, true, $data);
    }

    public function getPaywayId($method)
    {
        $pay_system = PaymentSystems::getPayoutSystemForMethod($method);
        if ($pay_system !== null) {
            return $pay_system->bank_alias;
        } else {
            Log::channel('pay')->error('PaymentSystems not found for alias ' . $method);
            throw new \Exception('PaymentSystems not found for alias ' . $method);
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

    public function replenishInfo(string $phone, string $country, bool $match = false)
    {
        $method = 'replenish-info';
        $httpMethod = 'get';
        $data = [
            'accountNumber' => $phone,
            'countryIsos' => mb_strtoupper($country),
            'nearestMatches' => $match,
        ];
        Log::channel('payout')->info('ik_kzt replenishInfo:' . var_export($data, true));
        return $this->query($method, $httpMethod, true, $data);
    }

    public function redirectToSci($id, $amount, $method, $currency = 'KZT')
    {
        $resp = $this->getSciPayinForm($id, $amount, $method, $currency);
        Log::channel('pay')->info('InterKassa KZT responce:' . var_export($resp, true));
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


    private function build_post_fields($data, $existingKeys = '', &$returnArray = [])
    {
        if (($data instanceof CURLFile) or !(is_array($data) or is_object($data))) {
            $returnArray[$existingKeys] = $data;
            return $returnArray;
        } else {
            foreach ($data as $key => $item) {
                $this->build_post_fields($item, $existingKeys ? $existingKeys . "[$key]" : $key, $returnArray);
            }
            return $returnArray;
        }
    }

    private function query($method, $httpMethod, $isPrivate = false, array $data = [])
    {
        $url = $this->url . '/' . $this->version . '/' . $method;

        $ch = curl_init();

        Log::channel('pay')->info(__CLASS__ . __METHOD__ . " method " . PHP_EOL . var_export($url, true));
        Log::channel('pay')->info(__CLASS__ . __METHOD__ . " data " . PHP_EOL . var_export($data, true));


        if ($httpMethod === 'post') {

            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $this->build_post_fields($data));

        } else {

            $url .= '?' . http_build_query($data);
        }

        if ($isPrivate) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Authorization: Basic ' . base64_encode($this->user_id . ':' . $this->key),
                'Ik-Api-Account-Id: ' . $this->business_acc,
                "Content-Type: multipart/form-data",
            ],
            );
        }

//        curl_setopt($ch, CURLOPT_HEADER, 0);

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        curl_setopt($ch, CURLOPT_USERAGENT, 'KrakenCassino');
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);

        $resp = json_decode(curl_exec($ch), true);

        Log::channel('pay')->info(__CLASS__ . __METHOD__ . " resp " . PHP_EOL . var_export($resp, true));

        return $resp;
    }

    private function querySci(array $data = [])
    {
        $url = $this->sci_url . '/';
        $data['ik_int'] = 'json';
        $data['ik_sign'] = $this->sign($data, $this->p2p_key);


        Log::channel('pay')->info(__CLASS__ . __METHOD__ . " request " . PHP_EOL . var_export($data, true));
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
