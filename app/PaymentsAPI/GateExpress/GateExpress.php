<?php

namespace App\PaymentsAPI\GateExpress;

use App\Models\Pay3dsData;
use App\MUser;
use App\Payin\Payin;
use App\PayoutTransaction;
use App\UserWallet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * looks similar to Payment Center
 * @url https://api.gate.express/#operation/Create
 */
final class GateExpress
{
    const MODE_DEFAULT = 'default';
    const MODE_TRUSTED = 'trusted';
    const MODE_PAYOUT = 'payout';

    private string $secret_key = "zwvCrG7Xrb4fLgmg";
    private string $public_key = "0478d91b007bbd2b006cf622a4c908e7edc518aa62536e47f5c9abaa82bb1c278d19b4d76bc7e0e50a44155685a32b580b4ef6b9a0dc441a08a7da95d55963e73b";
    private int $service_id = 13002;
    private string $url = "https://ep.gate.express";

    private array $service_config = [
        13002 => [
            'key' => "zwvCrG7Xrb4fLgmg",
            'pub_key' => "0478d91b007bbd2b006cf622a4c908e7edc518aa62536e47f5c9abaa82bb1c278d19b4d76bc7e0e50a44155685a32b580b4ef6b9a0dc441a08a7da95d55963e73b",
        ],
        13003 => [
            'key' => "peTRjACW6sXjg8pK",
            'pub_key' => "040476c22e85af6addb64c3243be0e63ed9d4cace0c2c6306a25468e561f615edfdb05f2f822c658226f365006a860db4d67d200e2d72f7a23c398edd438ce700a",
        ],
        13004 => [
            'key' => "J8gXBgfZv5KGN3Jp",
            'pub_key' => "048dbfe5e741a9fc8c1d4d848b2eaeb8cfed8f66a8830ed4ccd5fec9a127bed109a3dbf5664767f7582be466c6049808c51c2e61a7b64ef2aaa301b515d855aa48",
        ],
        13005 => [
            'key' => "uknVdwDPSyApWQ7t",
            'pub_key' => "0406cd490af6ae2f705282f0de9f11ac7ccd4bb38bd48f528ec5adae27692050a7883bde7f7bccb10bce64b01ad9304a7e70784bc61711124d544f38180398e84f",
        ],
        13006 => [
            'key' => "EUPwPFx5yy2MQb7R",
            'pub_key' => "04ab8bf8a02fba7414d91799adb566d2e0945743cacbabeaed56e8a185e52f424289a1e56e8b66a79c1b29d239eb4416383719e28f6378c81916ffcf76ac41bf8a",
        ],
        13007 => [
            'key' => "BHt7nkJF3fxt2Wdt",
            'pub_key' => "045abc245bbffb8b0f324b240b7e58244540c139f1daa38cf4cdd79b89636c47cd7afb9a101a3959282447d46b980435e9c6a5733555bcc766369508e933eda66b",
        ],
        13008 => [
            'key' => "6zeTy6x4Wb6ZneNL",
            'pub_key' => "04233f01123d1a189a69000fba7e200a31160a676fc97a38a8d3bf225fdb66b4ae59a44207bca41810b117d872a9620dbd5f927b05d48431d1fd68b5892d9dca3a",
        ],
        19066 => [
            'key' => "WRMvrWWCdNHL45TB",
            'pub_key' => "04d0a65e4175139f241986dd2b08a0a89c581625c0087255377a67c19b5b34659648778d879003ac753edb855caa36f2b2e66a9ef35f2eacb978c2d1105dd3e2c0",
        ],
        19068 => [
            'key' => "WDr93A2WdZrcs5fy",
            'pub_key' => "04c5270ecfa9f9831d37bd292f32ee70be18e14facfc7c2edb0010dd57d217c12f982ffb11da8cde9e5e2f2bbac6714eada14dca9912d43c88f9dabce1e179817d",
        ],
    ];

    private array $services = [
        'RUB' => [
            'default' => 13002,
            'trusted' => 13003,
            'payout' => 13004,
        ],
        'KZT' => [
            'default' => 13005,
            'trusted' => 13006,
            'payout' => 13007,
        ],
    ];

    public function __construct(int $service_id = 13002)
    {
        $this->service_id = $service_id;
        $this->secret_key = $this->service_config[$this->service_id]['key'];
        $this->public_key = $this->service_config[$this->service_id]['pub_key'];
    }

    public function setServiceId(int $service_id)
    {
        $this->service_id = $service_id;
        $this->secret_key = $this->service_config[$this->service_id]['key'];
        $this->public_key = $this->service_config[$this->service_id]['pub_key'];
    }

    public function setMode(string $currency, string $mode = 'default')
    {
        $this->service_id = $this->services[$currency][$mode];
        $this->setServiceId($this->service_id);
    }

    public function balance()
    {
        $api_method = '/balance';
        $payload = [
        ];

        return $this->query($api_method, 'GET', $payload);
    }

    public function transactionStatus($transaction_id)
    {
        $api_method = '/transactions/'.$transaction_id;
        $payload = [
        ];

        return $this->query($api_method, 'GET', $payload);
    }

    public function pay(string $tr_id, string $amount, string $curr_name, string $card_number, string $exp_month, string $exp_year, string $holder, string $cvc)
    {
        $api_method = '/payments/requests/single';
        $payload = [
            'OrderId' => $tr_id,
            'Amount' => $amount,
            'Currency' => $curr_name,
            'Description' => 'Kraken deposit',
            'CustomerInfo' => [
                'IP' => request()->ip(),
            ],
            'PaymentMethod' => 'Card',
            'PaymentDetails' => [
                'CardNumber' => $card_number,
                'ExpMonth' => $exp_month,
                'ExpYear' => $exp_year,
                'CardholderName' => $holder,
                'CVC' => $cvc,
            ],
            'ExtraData' => [
                'ReturnUrl' => route('pay.success', [], true),
                'WebhookUrl' => 'https://krakenfn.com/api/pay/gateexpress',
            ],
        ];

        return $this->query($api_method, 'POST', $payload);
    }

    public function charge(string $tr_id, string $amount, string $curr_name)
    {
        $api_method = '/payments/charge';
        $payload = [
            'TransactionId' => $tr_id,
            'Amount' => $amount,
            'Currency' => $curr_name,
            'WebhookUrl' => 'https://krakenfn.com/api/pay/gateexpress',
        ];

        return $this->query($api_method, 'POST', $payload);
    }

    public function confirm(string $tr_id, string $order_id, string $pa_res, string $md)
    {
        $api_method = '/payments/confirmation';
        $payload = [
            'TransactionId' => $tr_id,
            'OrderId' => $order_id,
            'PaRes' => $pa_res,
            'MD' => $md,
        ];

        return $this->query($api_method, 'POST', $payload);
    }

    public function payout(string $order_id, string $amount, string $currency, string $card_number, string $holder)
    {
        $api_method = '/payouts';
        $payload = [
            'OrderId' => $order_id,
            'Amount' => number_format($amount, 2, '.', ''),
            'Currency' => $currency,
            'Description' => 'kraken payout',
            'PayoutMethod' => 'Card',
            'PayoutDetails' => [
                'CardNumber' => $card_number,
                'CardholderName' => $holder,
            ],
        ];

        return $this->query($api_method, 'POST', $payload);
    }

    public function webpay(string $tr_id, string $amount, string $curr_name ='RUB')
    {
        $this->url = "https://webform.gate.express";
        $api_method = '/webpayments/create';
        $payload = [
            'PaymentRequest' => [
                'OrderId' => $tr_id,
                'Amount' => $amount,
                'Currency' => $curr_name,
                'Description' => 'Kraken deposit',
            ],
//            'MetaData' => [
//                'PaymentType' => 'Block',
//            ],
        ];

        return $this->query($api_method, 'POST', $payload);
    }
    /**
     * crreate signature
     * @param string $body
     * @return string
     */
    public function sign(string $body): string
    {
        $hash = hash_hmac('sha256', $body, $this->secret_key, false);
        return $hash;
    }

    private function query($method, $httpMethod, array $data = [])
    {
        $url = $this->url . $method;

        $ch = curl_init();

        $payload = json_encode((Object)$data);
        $request_id = 't'.time().Str::random(5);
        if (empty($data) && $httpMethod === 'GET') $payload = '';

        $sign = $this->sign($httpMethod.PHP_EOL.$method.PHP_EOL.$this->service_id.PHP_EOL.$request_id.PHP_EOL.$payload);

        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'X-SITE-ID: ' . $this->service_id,
            'X-REQUEST-ID: ' . $request_id,
            'X-REQUEST-SIGNATURE: ' . $sign,
        ]);

        if ( strtolower($httpMethod) === 'post') {

            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);

        } else {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
            if (!empty($data)) {
                $url .= '?' . http_build_query($data);
            }
        }

        Log::channel('pay')->info('GateExpress request: '.$url);
        Log::channel('pay')->info(var_export($data, true));
        Log::channel('pay')->info('GateExpress request body: '.$url);
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

        $json = curl_exec($ch);
        $err = curl_error($ch);
        $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $resp = json_decode($json, true);

        rewind($verbose);
        $verboseLog = stream_get_contents($verbose);

        Log::channel('pay')->info('curl verbose');
        Log::channel('pay')->info(htmlspecialchars($verboseLog));

        if ($err) {
            Log::channel('pay')->error($err);
        }

        Log::channel('pay')->info('GateExpress resp:');
        Log::channel('pay')->info('code:'.$httpcode);
        Log::channel('pay')->info($json);
        Log::channel('pay')->info(var_export($resp, true));
        return $resp;
    }

    public static function depositCallback(Request $request)
    {
        Log::channel('pay')->info(__CLASS__ . ' callback deposit request');
        Log::channel('pay')->info(var_export($request->all(), true));

        $data = $request->all();

        if (!isset($data['OrderId'])) {
            Log::channel('pay')->error('OrderId field not set');
            return response('', 500);
        }

        Log::channel('pay')->info('Start processing GateExpress transaction with id ' . $data['OrderId']);

//        $api = new GateExpress();
//        if (!$api->checkSign($request->getContent(), $request->header('X-Signature'))) {
//            Log::channel('pay')->error('check sign fail');
//            return response('wrong sign', 500);
//        }

        /** @var PayoutTransaction $transaction */
        $transaction = PayoutTransaction::where('id', $data['OrderId'])
            ->where('status_id', PayoutTransaction::STATUS_GATEEXPRESS_PENDING)
            ->first();

        if ($transaction === null) {
            if (PayoutTransaction::where('id', $data['OrderId'])
                    ->where('status_id', PayoutTransaction::STATUS_GATEEXPRESS_SUCCESS)
                    ->first() !== null) {
                Log::channel('pay')->error('Transaction with id ' . $data['OrderId'] . ' is already complete found. Request: ' . PHP_EOL . var_export($request->all(), true));
                return response('ok', 200);
            }
            Log::channel('pay')->error('Transaction with id ' . $data['OrderId'] . ' is not found. Request: ' . PHP_EOL . var_export($request->all(), true));
            return response('transaction not found', 404);
        }

        // Find user which create this transaction.
        /** @var MUser $user $user */
        $user = MUser::where('id', $transaction->user_id)->first();

        if ($user === null) {
            Log::channel('pay')->error('User of the transaction is not found.');
            return response('user not found', 500);
        }

        if ($data['Event'] === 'Fail' || $data['Event'] === 'Abandoned' || $data['Event'] === 'Cancel') {
            $transaction->status_id = PayoutTransaction::STATUS_GATEEXPRESS_FAIL;
            $transaction->save();
            return response('OK', 200);
        }

        if ($data['Event'] === 'Fail') {
            $transaction->status_id = PayoutTransaction::STATUS_GATEEXPRESS_FAIL;
            $transaction->save();
            return response('OK', 200);
        }

        if ($data['Event'] === '3DS') {
            $transaction->status = PayoutTransaction::STATUS_3DS;
            $transaction->save();

            $data_3ds = new Pay3dsData();
            $data_3ds->md = $data['MD']??'';
            $data_3ds->params = $data['PaReq']??'';
            $data_3ds->url = $data['AcsUrl']??'';
            $data_3ds->transaction_id = $transaction->id;
            $data_3ds->save();

            return response('OK', 200);
        }

        if ($data['Event'] === 'Redirect3DS') {
            $transaction->status = PayoutTransaction::STATUS_3DS_REDIRECT;
            $transaction->save();

            $data_3ds = new Pay3dsData();
            $data_3ds->method = $data['RedirectMethod']??'';
            $data_3ds->url = $data['RedirectUrl']??'';
            $data_3ds->transaction_id = $transaction->id;
            $data_3ds->save();

            return response('OK', 200);
        }

        if ($data['Event'] !== 'Payment') {
            return response('OK', 200);
        }

        $amount = $data['Amount'];

        $transaction->fee = 0;

        $currency = UserWallet::getCurrencyCode($data['Currency']);

        if ($currency === UserWallet::CURRENCY_KZT) {
            if ($data['Service_Id'] == 13006) {
                $transaction->fee = $amount * 0.055;
            } else {
                $transaction->fee = $amount * 0.1;
            }
        }

        if ($data['Service_Id'] == 13003) {
            $transaction->fee = $amount * 0.125;
        }

        if ($data['Service_Id'] == 13002) {
            $transaction->fee = $amount * 0.125 + 30;
        }

        Payin::completeTransaction($user, $transaction, $amount, PayoutTransaction::STATUS_GATEEXPRESS_SUCCESS, $currency);

        return 'OK';
    }
}
