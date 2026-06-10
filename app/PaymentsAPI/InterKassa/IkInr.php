<?php


namespace App\PaymentsAPI\InterKassa;


use App\Helpers\Currency;
use App\Models\PaymentSystems;
use App\Models\PaySystemsConfig;
use App\MUser;
use App\Payin\Payin;
use App\PayoutTransaction;
use Illuminate\Support\Facades\Log;

/**
 * Class InterKassa
 * @package App\PaymentsAPI\InterKassa
 * @see http://docs.interkassa.com/
 */
class IkInr
{

    private string $user_id = '6002dac136affc51ea3fed70';
    private string $key = 'eQLlBS08pwg9xgeVrBbW5aJH9liCjLFk';

    private string $business_acc = '6002dae67cff1d57022b3518';
    private int $purse_id = 815011394805;
    private $_conf;

//    private string $checkout_id = '6002dafe7e734a37f176ab35';
//    private string $checkout_key = 'aNSmQK1gzleu6BCs';
    private string $checkout_id = '61dd5ac94526787f7e0e20b0';
    private string $checkout_key = '9iRi1VxU6f2mFj5B';

    private string $url = 'https://api.interkassa.com';
    private string $version = 'v1';

    private string $sci_url = 'https://sci.interkassa.com';

    public function __construct()
    {
        $conf = PaySystemsConfig::getSystemConfig(Payin::TYPE_IKINR);
        $this->url = $conf['url'] ?? "https://api.interkassa.com";
        $this->sci_url = $conf['sci_url'] ?? "https://sci.interkassa.com";
        $this->version = $conf['version'] ?? "v1";
        $this->user_id = $conf['user_id'] ?? "6002dac136affc51ea3fed70";
        $this->key = $conf['key'] ?? "eQLlBS08pwg9xgeVrBbW5aJH9liCjLFk";
        $this->business_acc = $conf['business_acc'] ?? "6002dae67cff1d57022b3518";
        $this->purse_id = $conf['purse_id'] ?? 815011394805;
        $this->checkout_id = $conf['checkout_id'] ?? "61dd5ac94526787f7e0e20b0";
        $this->checkout_key = $conf['checkout_key'] ?? "9iRi1VxU6f2mFj5B";
        $this->_conf = $conf;
    }

    public function setCheckout($user_id)
    {
        $payed = PayoutTransaction::selectRaw('user_id, sum(`sum`) as sum, count(id) as cnt')
            ->where('user_id', $user_id)
            ->whereIn('status_id', PayoutTransaction::getIncomingStatuses())
            ->groupBy('user_id')
            ->first();

        if (!$payed || $payed->cnt > 3) {
            $this->checkout_id = $this->_conf['checkout_id_checked'] ?? "6002dafe7e734a37f176ab35";
        } else {
            $this->checkout_id = $this->_conf['checkout_id'] ?? "61dd5ac94526787f7e0e20b0";
        }
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

    public function getPayinForm($id, float $amount, string $method, array $data, MUser $user)
    {
        $pay_system = PaymentSystems::getPayinSystemForMethod($method);
        $form_data = [
            'ik_co_id' => $this->checkout_id,
            'ik_pm_no' => $id,
            'ik_am' => $amount,
            'ik_cur' => 'INR',
            'ik_desc' => 'Пополнение счета',
//            'ik_x_customer_phone' => $user->phone,
//            'ik_x_customer_email' => $user->email,
            'ik_x_customer_phone' => '+918928592722',
            'ik_x_customer_email' => 'ktsk0419@gmail.com',
            'ik_suc_u' => action('PayController@success'),
            'ik_fal_u' => action('PayController@fail'),
        ];

        if ($pay_system !== null) {
            $form_data['ik_pw_via'] = $pay_system->bank_alias;
        } else {
            Log::channel('pay')->error('PaymentSystems not found for alias '.$method);
            throw new \Exception('PaymentSystems not found for alias '.$method);
        }

        Log::channel('pay')->info('ik method:'.$method);
        if ($method === 'IkInr.UPI') {
            $form_data['ik_x_upi_number'] = $data['vpa'];
            $form_data['ik_x_mop_type'] = $data['mop'];
//            $form_data['ik_pw_via'] = 'indupi_pkm_merchant_inr';
            $form_data['ik_cur'] = 'inr';
            $form_data['ik_desc'] = 'Kraken wallet refill';
            $form_data['ik_sign'] = $this->sign($form_data, $this->checkout_key);
            Log::channel('pay')->info('ik inr form data');
            Log::channel('pay')->info(var_export($form_data, true));
            return view('pay.IkInr.upi-form', $form_data)->render();
        }

        if ($method === 'IkInr.Bank') {
//            $form_data['ik_pw_via'] = 'inps_cpaytrz_merchantNetBanking_inr';
            $form_data['ik_cur'] = 'inr';
            $form_data['ik_desc'] = 'Kraken wallet refill';
            $form_data['ik_x_mop_type'] = $data['mop'];
            $form_data['ik_sign'] = $this->sign($form_data, $this->checkout_key);
            Log::channel('pay')->info('ik inr form data');
            Log::channel('pay')->info(var_export($form_data, true));
            return view('pay.IkInr.net-banking-form', $form_data)->render();
        }

        if ($method === 'IkInr.Wallet') {
            $form_data['ik_x_mop_type'] = $data['mop'];
//            $form_data['ik_pw_via'] = 'inps_cpaytrz_merchantWallet_inr';
//            if ($method == 'IkInr.paytm') $form_data['ik_pw_via'] = 'indpaytm_pkm_merchant_inr';
//            if ($method == 'IkInr.PhonePe') $form_data['ik_pw_via'] = 'indfphonepe_pkm_merchant_inr';
            $form_data['ik_cur'] = 'inr';
            $form_data['ik_desc'] = 'Kraken wallet refill';
            $form_data['ik_sign'] = $this->sign($form_data, $this->checkout_key);
            Log::channel('pay')->info('ik inr form data');
            Log::channel('pay')->info(var_export($form_data, true));
            return view('pay.IkInr.wallet-form', $form_data)->render();
        }

        Log::channel('pay')->info('using default method: IkInr.Wallet');
//        $form_data['ik_pw_via'] = 'inps_cpaytrz_merchantWallet_inr';
        $form_data['ik_x_mop_type'] = $data['mop'];
        $form_data['ik_cur'] = 'inr';
        $form_data['ik_desc'] = 'Kraken wallet refill';
//        if ($method == 'IkInr.paytm') $form_data['ik_pw_via'] = 'indpaytm_pkm_merchant_inr';
//        if ($method == 'IkInr.PhonePe') $form_data['ik_pw_via'] = 'indfphonepe_pkm_merchant_inr';
        $form_data['ik_sign'] = $this->sign($form_data, $this->checkout_key);
        Log::channel('pay')->info('ik inr form data');
        Log::channel('pay')->info(var_export($form_data, true));
        return view('pay.IkInr.wallet-form', $form_data)->render();

        return '';
    }

    public function getSciPayways($id, $amount, $currency = 'INR')
    {
        $data = [
            'ik_co_id' => $this->checkout_id,
            'ik_pm_no' => $id,
            'ik_am' => $amount,
            'ik_cur' => $currency,
            'ik_desc' => 'Пополнение счета',
            'ik_act' => 'payways',
            'ik_int' => 'json',
        ];

        $data['ik_sign'] = $this->sign($data, $this->checkout_key);

        return $this->querySci($data);
    }

    public function getSciPayCost($id, $amount, $currency = 'RUB')
    {
        $data = [
            'ik_co_id' => $this->checkout_id,
            'ik_pm_no' => $id,
            'ik_am' => $amount,
            'ik_cur' => $currency,
            'ik_desc' => 'Пополнение счета',
            'ik_act' => 'payways',
            'ik_int' => 'json',
            'ik_pw_via' => 'card_cpaytrz_merchant_inr',
        ];

        $data['ik_sign'] = $this->sign($data, $this->checkout_key);

        return $this->querySci($data);
    }

    public function getSciPayinForm($id, $amount, $currency = 'INR')
    {
        $data = [
            'ik_co_id' => $this->checkout_id,
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

        $data['ik_sign'] = $this->sign($data, $this->checkout_key);

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

        switch ($method) {
            case 'IkInr.UPI':
                return 'inps_cpaytrz_transferUpi_inr';
            case 'IkInr.Bank':
                return 'inps_cpaytrz_transferBank_inr';
        }
        return 'unknown';
    }

    public function payoutStatus($id)
    {
        $method = 'withdraw/' . $id;
        $httpMethod = 'get';
        $data = [
        ];

        return $this->query($method, $httpMethod, true, $data);
    }

    public function redirectToSci($id, $amount, $currency = 'INR')
    {
        if ($currency !== 'RUB') {
            $resp = $this->getSciPayinForm($id, $amount, $currency);
            Log::channel('pay')->info('InterKassa INR responce:' . var_export($resp, true));

            return view('pay.ikinr-visa', [
                'method' => $resp['resultData']['paymentForm']['method'],
                'action' => $resp['resultData']['paymentForm']['action'],
                'x_request_id' => $resp['resultData']['paymentForm']['parameters']['x-request-id'],
                'ik_suc_u' => route('pay.success'),
                'ik_fal_u' => route('pay.fail'),
            ])->render();

            return $resp['resultData']['paymentForm']['action'];
        }
        $data = [
            'ik_co_id' => $this->checkout_id,
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
        Log::channel('pay')->info('inr sign:' . $this->sign($data, $this->checkout_key));
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

        curl_setopt($ch, CURLOPT_VERBOSE, true);

        $verbose = fopen('php://temp', 'w+');
        curl_setopt($ch, CURLOPT_STDERR, $verbose);

        $result = curl_exec($ch);

        rewind($verbose);
        $verboseLog = stream_get_contents($verbose);

        Log::channel('pay')->info('curl verbose');
        Log::channel('pay')->info(htmlspecialchars($verboseLog));

        return json_decode($result, true);
    }

    private function querySci(array $data = [])
    {
        $url = $this->sci_url . '/';
        $data['ik_int'] = 'json';

        Log::channel('pay')->info('InterKassa sci query data:');
        Log::channel('pay')->info(var_export($data, true));

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
