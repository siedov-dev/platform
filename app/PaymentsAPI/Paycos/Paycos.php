<?php


namespace App\PaymentsAPI\Paycos;


use App\Models\PaySystemsConfig;
use App\MUser;
use App\Payin\Payin;
use App\PayoutTransaction;
use App\UserWallet;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Class Paycos
 * @package App\PaymentsAPI\Paycos
 * @link https://docs.dialup56k.xyz/#introduction
 */
class Paycos
{
    private string $key = "2d1844634a8f4c5e9abe";
    private string $url = "";

    public function __construct($currency = 'INR', $method = '')
    {
        $conf = PaySystemsConfig::getSystemConfig(Payin::TYPE_PAYCOS);
        $this->key = $conf['key'];
        if ($currency === 'RUB') $this->key = $conf['rub_key']??'1032d4bbc640eeecaed1';//qiwi, yoomoney

        if ($method === 'ob_card') $this->key = $conf['card_key']??'da73a47690d3997c2fe8';// cards
        if ($method === 'Paycos.ob_card') $this->key = $conf['card_key']??'da73a47690d3997c2fe8';// cards

        if ($method === 'ob_card2') $this->key = $conf['card_key2']??'8ca6116b76fef3599cee';// cards2
        if ($method === 'Paycos.ob_card2') $this->key = $conf['card_key2']??'8ca6116b76fef3599cee';// cards2

        if ($method === 'payout_card') $this->key = $conf['card_po_key']??'20e46603ddbfa33bd0de'; //payout cards
        if ($method === 'Paycos.payout_card') $this->key = $conf['card_po_key']??'20e46603ddbfa33bd0de'; //payout cards

        if ($method === 'd_cards') $this->key = $conf['rub_d_cards']??'5b8a3ac878662c682b66'; //rub cards
        if ($method === 'Paycos.d_cards') $this->key = $conf['rub_d_cards']??'5b8a3ac878662c682b66'; //rub cards

        //74d3c8cf35e0c4bea8d3
        if ($method === 'ob_card3') $this->key = $conf['ob_card3']??'74d3c8cf35e0c4bea8d3'; //rub cards
        if ($method === 'Paycos.ob_card3') $this->key = $conf['ob_card3']??'74d3c8cf35e0c4bea8d3'; //rub cards

        if ($method === 'kz_mobile') $this->key = $conf['kz_mobile']??'7124ff98af41b83f163b';
        if ($method === 'Paycos.kz_mobile') $this->key = $conf['kz_mobile']??'7124ff98af41b83f163b';

        if ($currency === 'UAH') $this->key = $conf['uah_key']??'662ab777ca6b1fdb9852';//4757bd917a8e05036f01 payout
        if ($method === 'Paycos.payout_card-uah') $this->key = $conf['uah_key_po']??'4757bd917a8e05036f01'; //payout cards uah

        if ($method === 'Paycos.visa-eur') $this->key = $conf['euro_visa_po']??'7fc2037119dedbd7d739'; //payout cards visa euro
        if ($method === 'Paycos.mc-eur') $this->key = $conf['euro_mc_po']??'40dbd088d6669cc29066'; //payout cards mastercard euro

        if ($method === 'qr_sber') $this->key = $conf['qr_sber']??'ea0949915d764b61ce89'; //rub sber
        if ($method === 'qr_tinkoff') $this->key = $conf['qr_tinkoff']??'5cda53adbacd3084805d'; //rub tinkoff

        if ($method === 'inr_95') $this->key = $conf['inr_95']??'6c5f77eab5ffcce194d8'; //inr #95
        if ($method === 'Paycos.inr_95') $this->key = $conf['Paycos.inr_95']??'6c5f77eab5ffcce194d8'; //inr #95
    }

    public function balance(string $currency = 'INR')
    {
        $method = 'https://business.paycos.com/api/v1/balance';
        $data = [
            'currency' => $currency,
        ];
        return $this->query($method, 'get', $data);
    }

    public function getPayment($payment_token)
    {
        $method = 'https://business-api.paycos.com/api/v2/merchant/payment/'.$payment_token;
        $data = [
        ];

        return $this->query($method, 'get', $data);
    }

    public function initPay(string $orderId, int $cents, array $customer, string $currency = 'INR', $p_method = 'default')
    {
        Log::channel('pay')->info('paycos init pay key:'.$this->key);
        $method = 'https://gateway-router.paycos.com/api/v1/init/pay';
        $data = [
            'product' => 'karkencassino refill',
            'amount' => $cents,
            'currency' => $currency,
            'orderNumber' => $orderId,
            "platform" => "web",
            'locale' => Auth::user()->locale??'en',
            'redirectSuccessUrl' => action('PayController@success'),
            'redirectFailUrl' => action('PayController@fail'),
            'callbackUrl' => 'https://krakenfn.com/api/paycos/pay',
            'extraReturnParam' => $p_method,
        ];

        if (!empty($customer['_token'])) unset($customer['_token']);
        if (!empty($customer['payment-method'])) unset($customer['payment-method']);
        if (!empty($customer['amount'])) unset($customer['amount']);

        if (!empty($customer['mop_type'])) {
            $data['mop_type'] = $customer['mop_type'];
            unset($customer['mop_type']);
        }
        if (!empty($customer['payer_address'])) {
            $data['payer_address'] = $customer['payer_address'];
            unset($customer['payer_address']);
        }

        if (!empty($customer)) $data['customer'] = $customer;
        $data['customer']['client_id'] = "".Auth::id();
        $data['customer']['email'] = Auth::user()->email;
        $data['customer']['ip'] = request()->ip();

        return $this->query($method, 'post', $data);
    }

    public function payOut(string $orderId, int $cents, $requisites, $pay_method, string $currency = 'RUB')
    {
        $method = 'https://gateway-router.paycos.com/api/v1/init/payout';
        $data = [
            'product' => 'karkencassino payout#'.$orderId,
            'amount' => $cents,
            'currency' => $currency,
            'orderNumber' => $orderId,
            "platform" => "web",
            'callbackUrl' => 'https://krakenfn.com/api/paycos/pay',
            'redirectSuccessUrl' => action('PayController@success'),
            'redirectFailUrl' => action('PayController@fail'),
            'extraReturnParam' => $pay_method,
        ];

        if ($currency === "EUR") $data['locale'] = 'en';

        if (!empty($requisites['transfer_type'])) $data['transfer_type'] = $requisites['transfer_type'];

        if (!empty($requisites['qiwi_phone'])) $data['customer']['phone'] = $requisites['qiwi_phone'];

        if (!empty($requisites['cardNumber'])) $data['card']['pan'] = $requisites['cardNumber'];
        if (!empty($requisites['month'])) $data['card']['expires'] = $requisites['month'].'/'.$requisites['year'];
        if (!empty($requisites['card_holder'])) $data['card']['holder'] = $requisites['card_holder'];

        if (!empty($requisites['birthday'])) $data['customer']['birth_date'] = Carbon::parse($requisites['birthday'])->format('Y-m-d');

        if (!empty($requisites['bank_account'])) $data['bank_account'] = $requisites['bank_account'];
        if (!empty($requisites['customer'])) $data['customer'] = $requisites['customer'];

        return $this->query($method, 'post', $data);
    }

    public function payOutInr(string $orderId, int $cents, $requisites, $pay_method, string $currency = 'INR')
    {
        $customer = $requisites;
        $method = 'https://gateway-router.paycos.com/api/v1/init/payout';
        $data = [
            'product' => 'karkencassino payout#'.$orderId,
            'amount' => $cents,
            'currency' => $currency,
            'orderNumber' => $orderId,
            "platform" => "web",
            'callbackUrl' => 'https://krakenfn.com/api/paycos/pay',
            'redirectSuccessUrl' => action('PayController@success'),
            'redirectFailUrl' => action('PayController@fail'),
        ];

        if (!empty($customer['_token'])) unset($customer['_token']);
        if (!empty($customer['payment-method'])) unset($customer['payment-method']);
        if (!empty($customer['amount'])) unset($customer['amount']);

        if (!empty($customer['ifsc_code'])) {
            $data['ifsc_code'] = $customer['ifsc_code'];
            unset($customer['ifsc_code']);
        }
        if (!empty($customer['bank_account_number'])) {
            $data['bank_account_number'] = $customer['bank_account_number'];
            unset($customer['bank_account_number']);
        }
        if (!empty($customer['bank_name'])) {
            $data['bank_name'] = $customer['bank_name'];
            unset($customer['bank_name']);
        }
        if (!empty($customer['transfer_type'])) {
            $data['transfer_type'] = $customer['transfer_type'];
            unset($customer['transfer_type']);
        }

        if (!empty($customer)) $data['customer'] = $customer;

        return $this->query($method, 'post', $data);
    }

    public function h2hConfirm($url, $data = [])
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'authorization: Bearer ' . $this->key,
            'Content-Type: application/json',
        ]);

        Log::channel('pay')->info('Paycos h2h request: '.$url);

        curl_setopt($ch, CURLOPT_HEADER, false);

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);

        curl_setopt($ch, CURLOPT_USERAGENT, 'KrakenCassino');
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_MAXREDIRS, 10);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));


//        curl_setopt($ch, CURLOPT_VERBOSE, true);
//
//        $verbose = fopen('php://temp', 'w+');
//        curl_setopt($ch, CURLOPT_STDERR, $verbose);

        $text = curl_exec($ch);
        $err = curl_error($ch);
        $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $resp = json_decode($text, true);
        if ($err) {
            Log::channel('pay')->error($err);
        }

//        rewind($verbose);
//        $verboseLog = stream_get_contents($verbose);
//
//        Log::channel('pay')->info('curl verbose');
//        Log::channel('pay')->info(htmlspecialchars($verboseLog));

        Log::channel('pay')->info('Paycos resp:');
        Log::channel('pay')->info('code:'.$httpcode);
        Log::channel('pay')->info($text);
        Log::channel('pay')->info(var_export($resp, true));
        return $resp;
    }

    public function checkSign($data): bool
    {
        $sign = $data['signature'];
        unset($data['signature']);
        $json = json_encode($data);
        Log::channel('pay')->info('json for sign:'. $json.$this->key);
        Log::channel('pay')->info('hash:'. hash('sha512', $json.$this->key));
        return hash('sha512', $json.$this->key) === $sign;
    }

    private function query($method, $httpMethod, array $data = [])
    {
        $url = $this->url . $method;

        $ch = curl_init();
        if ( strtolower($httpMethod) === 'post') {

            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'authorization: Bearer ' . $this->key,
                'Content-Type: application/json',
            ]);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

        } else {

            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Authorization: Bearer ' . $this->key,
                'Content-Type: application/json',
            ]);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
            curl_setopt($ch, CURLOPT_ENCODING, "");
            if (!empty($data)) {
                $url .= '?' . http_build_query($data);
            }
        }

        Log::channel('pay')->info('Paycos request: '.$url);
        Log::channel('pay')->info(json_encode($data));
        Log::channel('pay')->info(var_export($data, true));

        curl_setopt($ch, CURLOPT_HEADER, false);

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);

        curl_setopt($ch, CURLOPT_USERAGENT, 'KrakenCassino');
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_MAXREDIRS, 10);

        $text = curl_exec($ch);
        $err = curl_error($ch);
        $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $resp = json_decode($text, true);
        if ($err) {
            Log::channel('pay')->error($err);
        }

        Log::channel('pay')->info('Paycos resp:');
        Log::channel('pay')->info('code:'.$httpcode);
        Log::channel('pay')->info($text);
        Log::channel('pay')->info(var_export($resp, true));
        return $resp;
    }

    public static function depositCallback(Request $request)
    {
        Log::channel('pay')->info('Start processing paycos ' . PHP_EOL . var_export($request->getContent(), true));
        Log::channel('pay')->info('Start processing paycos ' . PHP_EOL . var_export(json_decode($request->getContent(), true), true));

        $trans_id = $request->get('orderNumber');
        if (!$trans_id) {
            Log::channel('pay')->error('orderId field not set');
            return response('Платеж не найден', 200);
        }

        $data = json_decode($request->getContent(), true);

        $currency = UserWallet::getCurrencyCode($data['currency']);
        $method = $data['extraReturnParam']??'';

        $api = new Paycos(mb_strtoupper($data['currency']), $method);
        if (!$api->checkSign($data)) {
            Log::channel('pay')->error('Wrong sign, possible hack');
//            return response('Wrong sign', 500);
        }

        DB::beginTransaction();

        // Find transaction.
        /** @var PayoutTransaction $transaction */
        $transaction = PayoutTransaction::where('id', $trans_id)
            ->whereIn('status_id', [
                PayoutTransaction::STATUS_PAYCOS_PENDING,
                PayoutTransaction::STATUS_PAYCOS_FAIL,
            ])
            ->lockForUpdate()
            ->first();

        if ($transaction->status_id === PayoutTransaction::STATUS_PAYCOS_FAIL) Log::channel('pay')
            ->info(__CLASS__." transaction was in fail state");

        if ($transaction === null) {
            Log::channel('pay')->error('Transaction with id ' . $trans_id . ' is not found. Request: ' . PHP_EOL . var_export($request->all(), true));
            DB::commit();
            return response('OK', 200);
        }

        if ($data['status'] === 'declined') {
            $transaction->status_id = PayoutTransaction::STATUS_PAYCOS_FAIL;
            $transaction->save();
            DB::commit();
            return response('OK', 200);
        }

        if ($data['status'] !== 'approved') {
            DB::commit();
            return response('OK', 200);
        }

//        if payout
        if ($data['type'] !== 'pay') {
            DB::commit();
            return response('OK', 200);
        }


        // Find user which create this transaction.
        /** @var MUser $user $user */
        $user = MUser::where('id', $transaction->user_id)->first();

        if ($user === null) {
            Log::channel('pay')->error('User of the transaction is not found.');
            DB::commit();
            return response('User not found.', 500);
        }

        $amount = $data['amount'] / 100;


        $fee = $amount * 0.075;

//        if ($currency == UserWallet::CURRENCY_RUB) {
//            $fee = $amount * 0.14;
//            if ($method === 'Paycos.ob_card' || $method === 'ob_card') {
//                $fee = $amount * 0.1;
//            }
//        }

        $transaction->sum = $amount;
        $transaction->fee = round($fee, 2);
        $transaction->total_sum = $amount + $fee;
        $transaction->card_number = $data['sanitizedMask']??'';

        try {
            Payin::completeTransaction($user, $transaction, $amount, PayoutTransaction::STATUS_PAYCOS_SUCCESS, $currency);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::channel('pay')->error($e->getMessage());
        }

        DB::commit();

        return response('OK', 200);
    }
}
