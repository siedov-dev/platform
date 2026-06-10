<?php

namespace App\PaymentsAPI\PaymentCenter;

use App\Models\PaySystemsConfig;
use App\MUser;
use App\Payin\Payin;
use App\PayoutTransaction;
use App\UserWallet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * @see https://doc.payment.center/ru/#tag/Zaprosy/operation/WebBlock%20(webform)
 */
final class PaymentCenter
{
    const MODE_DEFAULT = 1;
    const MODE_TRUSTED = 2;
    const MODE_PAYOUT = 3;

    private string $secret_key = "83pLcp7GzNb3ajBB";
    private string $url = "https://payliveonly.com";
    private int $service_id = 3493;

    private int $ftd_service_id;
    private int $trust_service_id;
    private int $payout_service_id;

    private string $ftd_key;
    private string $trust_key;
    private string $payout_key;

    public function __construct()
    {
        $conf = PaySystemsConfig::getSystemConfig(Payin::TYPE_PAYCENTER);
        $this->service_id = $conf['id_ftd'];
        $this->secret_key = $conf['key_ftd'];

        $this->ftd_service_id = $conf['id_ftd'];
        $this->trust_service_id = $conf['id_trusted'];
        $this->payout_service_id = $conf['id_payout'];

        $this->ftd_key = $conf['key_ftd'];
        $this->trust_key = $conf['key_trusted'];
        $this->payout_key = $conf['key_payout'];
    }

    public function setMode(int $mode): void
    {
        switch ($mode) {
            default:
            case self::MODE_DEFAULT:
                $this->service_id = $this->ftd_service_id;
                $this->secret_key = $this->ftd_key;
                $this->url = "https://payliveonly.com";
                break;
            case self::MODE_TRUSTED:
                $this->service_id = $this->trust_service_id;
                $this->secret_key = $this->trust_key;
                $this->url = "https://wayforpayer.com";
                break;
            case self::MODE_PAYOUT:
                $this->service_id = $this->payout_service_id;
                $this->secret_key = $this->payout_key;
                $this->url = "https://offpaygo.com";
                break;
        }
    }

    public function balance()
    {
        $api_method = '/v2/limit';
        $payload = [
            'serviceId' => $this->service_id,
        ];

        return $this->query($api_method, 'post', $payload);
    }

    public function webform(string $tr_id, int $amount, string $curr_name ='KZT')
    {
        $api_method = '/v2/webpay';
        $payload = [
            'serviceId' => $this->service_id,
            'orderId' => $tr_id,
            'amount' => $amount,
            'currency' => $curr_name,
            'description' => 'Kraken deposit',
        ];

        return $this->redirectUrl($api_method, 'post', $payload);
    }

    public function status(string $id)
    {
        $api_method = '/v2/status';
        $payload = [
            'serviceId' => $this->service_id,
            'orderId' => $id,
        ];

        return $this->redirectUrl($api_method, 'post', $payload);
    }

    public function payout(string $card, string $tr, int $amount, string $holder, string $currency = 'KZT')
    {
        $api_method = '/v2/payout';
        $payload = [
            'serviceId' => $this->service_id,
            'cardNumber' => $card,
            'cardHolder' => $holder,
            'orderId' => $tr,
            'amount' => $amount,
            'currency' => $currency,
        ];

        return $this->redirectUrl($api_method, 'post', $payload);
    }

    /**
     * crreate signature
     * @param string $body
     * @return string
     */
    public function sign(string $body): string
    {
        $hash = hash_hmac('sha256', $body, $this->secret_key, false);
        return base64_encode($hash);
    }

    private function query($method, $httpMethod, array $data = [])
    {
        $url = $this->url . $method;

        $ch = curl_init();

        $payload = http_build_query($data);

        $sign = $this->sign($payload);

        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Signature: ' . $sign,
        ]);

        if ( strtolower($httpMethod) === 'post') {

            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);

        } else {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
            if (!empty($data)) {
                $url .= '?' . $data;
            }
        }

        Log::channel('pay')->info('PaymentCenter request: '.$url);
        Log::channel('pay')->info(var_export($data, true));
        Log::channel('pay')->info('PaymentCenter request body: '.$url);
        Log::channel('pay')->info($payload);

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);

        curl_setopt($ch, CURLOPT_USERAGENT, 'KrakenCassino');
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_MAXREDIRS, 10);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

        curl_setopt($ch, CURLOPT_VERBOSE, true);

        $verbose = fopen('php://temp', 'w+');
        curl_setopt($ch, CURLOPT_STDERR, $verbose);

        $text = curl_exec($ch);
        $err = curl_error($ch);
        $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        @$xml = simplexml_load_string($text, "SimpleXMLElement", LIBXML_NOCDATA);
        $json = json_encode($xml);
        $resp = json_decode($json, true);

        rewind($verbose);
        $verboseLog = stream_get_contents($verbose);

        Log::channel('pay')->info('curl verbose');
        Log::channel('pay')->info(htmlspecialchars($verboseLog));

        if ($err) {
            Log::channel('pay')->error($err);
        }

        Log::channel('pay')->info('PaymentCenter resp:');
        Log::channel('pay')->info('code:'.$httpcode);
        Log::channel('pay')->info($text);
        Log::channel('pay')->info(var_export($resp, true));
        return $resp;
    }

    private function redirectUrl($method, $httpMethod, array $data = [])
    {
        $url = $this->url . $method;

        $ch = curl_init();

        $payload = http_build_query($data);

        $sign = $this->sign($payload);

        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Signature: ' . $sign,
        ]);

        if ( strtolower($httpMethod) === 'post') {

            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);

        } else {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
            if (!empty($data)) {
                $url .= '?' . $data;
            }
        }

        Log::channel('pay')->info('PaymentCenter request: '.$url);
        Log::channel('pay')->info(var_export($data, true));
        Log::channel('pay')->info('PaymentCenter request body: '.$url);
        Log::channel('pay')->info($payload);

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);

        curl_setopt($ch, CURLOPT_USERAGENT, 'KrakenCassino');
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_MAXREDIRS, 10);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

        curl_setopt($ch, CURLOPT_VERBOSE, true);

        $verbose = fopen('php://temp', 'w+');
        curl_setopt($ch, CURLOPT_STDERR, $verbose);

        $text = curl_exec($ch);
        $err = curl_error($ch);
        $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $last_url = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);

        rewind($verbose);
        $verboseLog = stream_get_contents($verbose);

        Log::channel('pay')->info('curl verbose');
        Log::channel('pay')->info(htmlspecialchars($verboseLog));

        if ($err) {
            Log::channel('pay')->error($err);
        }

        Log::channel('pay')->info('PaymentCenter resp:');
        Log::channel('pay')->info('code:'.$httpcode);
        Log::channel('pay')->info('url:'.$last_url);
        Log::channel('pay')->info($text);
        Log::channel('pay')->info(var_export($text, true));
        if ($httpcode != 200) return false;
        return $last_url;
    }

    public function getFee(int $serviceId, float $amount): float
    {
        if ($serviceId === $this->trust_service_id) {
            if ($amount < 5455) return 300;
            return $amount * 0.055;
        }
        if ($amount < 3160) return 300;
        return $amount * 0.095;
    }

    public static function depositCallback(Request $request)
    {
        Log::channel('pay')->info('PayCenter callback deposit request');
        Log::channel('pay')->info(var_export($request->all(), true));
        $api = new PaymentCenter();

        $data = $request->all();

        if (!isset($data['Order_Id'])) {
            Log::channel('pay')->error('Order_Id field not set');
            return response('', 500);
        }

        $api->setMode($data['Service_Id'] == 3493? PaymentCenter::MODE_DEFAULT : PaymentCenter::MODE_TRUSTED);
        if ($api->sign($request->getContent()) !== $request->header('X-REQUEST-SIGNATURE')) {
            Log::channel('pay')->error('wrong hash');
            Log::channel('pay')->error('signature ' . $request->header('X-REQUEST-SIGNATURE'));
//            return response('wrong hash', 500);
        }

        Log::channel('pay')->info('Start processing PayCenter transaction with id ' . $data['Order_Id']);

        /** @var PayoutTransaction $transaction */
        $transaction = PayoutTransaction::where('id', $data['Order_Id'])
            ->where('status_id', PayoutTransaction::STATUS_PAYCENTER_PENDING)
            ->first();

        if ($transaction === null) {
            if (PayoutTransaction::where('id', $data['Order_Id'])
                    ->where('status_id', PayoutTransaction::STATUS_PAYCENTER_SUCCESS)
                    ->first() !== null) {
                Log::channel('pay')->error('Transaction with id ' . $data['Order_Id'] . ' is already complete found. Request: ' . PHP_EOL . var_export($request->all(), true));
                return response('ok', 200);
            }
            Log::channel('pay')->error('Transaction with id ' . $data['Order_Id'] . ' is not found. Request: ' . PHP_EOL . var_export($request->all(), true));
            return response('transaction not found', 404);
        }

        // Find user which create this transaction.
        /** @var MUser $user $user */
        $user = MUser::where('id', $transaction->user_id)->first();

        if ($user === null) {
            Log::channel('pay')->error('User of the transaction is not found.');
            return response('user not found', 500);
        }
        if ($data['Event'] !== 'Payment')
        {
            return 'ok';
        }

        if ($data['Event'] !== 'Payment' && $data['Status'] !== 'CHARGED') {
            Log::channel('pay')->error('event:' . $data['event']);
            $transaction->status_id = PayoutTransaction::STATUS_PAYCENTER_FAIL;
            $transaction->coment = $data['event']??' event error';
            $transaction->save();
            return 'ok';
        }

        $amount = $data['Amount'];


        $currency = UserWallet::getCurrencyCode($data['Currency']);
        $transaction->fee = $api->getFee($data['Service_Id']??0, $amount);
        $transaction->card_number = $data['CardMasked'];

        Payin::completeTransaction($user, $transaction, $amount, PayoutTransaction::STATUS_PAYCENTER_SUCCESS, $currency);

        return response('ok', 200);
    }
}
