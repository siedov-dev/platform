<?php


namespace App\PaymentsAPI\InterKassa;


use App\Helpers\Currency;
use App\Models\PaySystemsConfig;
use App\Payin\Payin;
use Illuminate\Support\Facades\Log;

/**
 * Class InterKassa
 * @package App\PaymentsAPI\InterKassa
 * @see http://docs.interkassa.com/
 */
class InterKassa
{

    private string $user_id = '6002dac136affc51ea3fed70';
    private string $key = 'eQLlBS08pwg9xgeVrBbW5aJH9liCjLFk';

    private string $business_acc = '6002dae67cff1d57022b3517';
    private int $purse_id = 402958662039;

    private string $checkout_id = '606ac02a9afbbd4be8380b51';
    private string $checkout_inr = '6002dafe7e734a37f176ab35';
    private string $checkout_key = 'MRCn1R5k1un6Xsqz';

    private string $url = 'https://api.interkassa.com';
    private string $version = 'v1';

    private string $sci_url = 'https://sci.interkassa.com';

    public function __construct()
    {
        $conf = PaySystemsConfig::getSystemConfig(Payin::TYPE_IKASSA);
        $this->url = $conf['url'] ?? "https://api.interkassa.com";
        $this->sci_url = $conf['sci_url'] ?? "https://sci.interkassa.com";
        $this->version = $conf['version'] ?? "v1";
        $this->user_id = $conf['user_id'] ?? "6002dac136affc51ea3fed70";
        $this->key = $conf['key'] ?? "eQLlBS08pwg9xgeVrBbW5aJH9liCjLFk";
        $this->business_acc = $conf['business_acc'] ?? "6002dae67cff1d57022b3517";
        $this->purse_id = $conf['purse_id'] ?? "402958662039";
        $this->checkout_id = $conf['checkout_id'] ?? "606ac02a9afbbd4be8380b51";
        $this->checkout_key = $conf['checkout_key'] ?? "MRCn1R5k1un6Xsqz";
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

    public function getSciPayways($id, $amount, $currency = 'RUB')
    {
        $data = [
            'ik_co_id' => $currency === 'INR' ? $this->checkout_inr : $this->checkout_id,
            'ik_pm_no' => $id,
            'ik_am' => $amount,
            'ik_cur' => $currency,
            'ik_desc' => 'Пополнение счета',
            'ik_act' => 'payways',
            'ik_int' => 'json',
        ];

        return $this->querySci($data);
    }

    public function getSciPayCost($id, $amount, $currency = 'RUB')
    {
        $data = [
            'ik_co_id' => $currency === 'INR' ? $this->checkout_inr : $this->checkout_id,
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

    public function getSciPayinForm($id, $amount, $currency = 'INR')
    {
        $data = [
            'ik_co_id' => $currency === 'INR' ? $this->checkout_inr : $this->checkout_id,
            'ik_pm_no' => $id,
            'ik_am' => $amount,
            'ik_cur' => $currency,
            'ik_desc' => 'Пополнение счета',
            'ik_act' => 'process',
            'ik_int' => 'json',
            'ik_pw_via' => 'card_cpaytrz_merchant_inr',
            'ik_suc_u' => action('PayController@success'),
            'ik_fal_u' => action('PayController@fail'),
        ];

        return $this->querySci($data);
    }

    public function createWithdraw($amount, $tr_id, $card_no, $currency = 'RUB')
    {
        $method = 'withdraw';
        $httpMethod = 'post';
        $data = [
            'ik_int' => 'json',
            'paymentNo' => $tr_id,
            'amount' => $amount,
            'action' => 'process',
            'purseId' => $this->purse_id,
            'currency' => $currency,
//            'method' => 'card',
            'paywayId' => '53c3d6fdbf4efc7867441a0b',
            "calcKey" => "psPayeeAmount",
            'useShortAlias' => false,
            'details' => [
                'comment' => $card_no,
            ],
        ];

        return $this->query($method, $httpMethod, true, $data);
    }

    public function payoutStatus($id)
    {
        $method = 'withdraw/' . $id;
        $httpMethod = 'get';
        $data = [
        ];

        return $this->query($method, $httpMethod, true, $data);
    }

    public function redirectToSci($id, $amount, $currency = 'RUB')
    {
        if ($currency === 'INR') {
            $resp = $this->getSciPayinForm($id, $amount);
            Log::channel('pay')->info('InterKassa INR responce:' . var_export($resp, true));
            return $resp['resultData']['paymentForm']['action'];
        }
        $data = [
            'ik_co_id' => $currency === 'INR' ? $this->checkout_inr : $this->checkout_id,
            'ik_pm_no' => $id,
            'ik_am' => $amount,
            'ik_cur' => $currency,
            'ik_desc' => 'Пополнение счета',
            'ik_suc_u' => route('pay.success'),
            'ik_fal_u' => route('pay.fail'),
        ];

        $data['ik_sign'] = $this->sign($data, $this->checkout_key);

        $url = $this->sci_url . '?' . http_build_query($data);
        Log::channel('pay')->info('InterKassa url:' . $url);
        return $url;
    }

    public function checkSign(&$data)
    {
        $sign = $data['ik_sign'];
        Log::channel('pay')->info('ik_sign:' . $sign);
        Log::channel('pay')->info('sign:' . $this->sign($data));
        return $sign === $this->sign($data, $this->checkout_key);
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

        return json_decode(curl_exec($ch), true);
    }
}
