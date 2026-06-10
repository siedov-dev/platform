<?php

namespace App\PaymentsAPI\MayfPay;

use App\MUser;
use App\Payin\Payin;
use App\PayoutTransaction;
use App\UserWallet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class MayfPay
{
    private string $key = "MP_aa6x2kz8g53i67q5idj218x3krmhmba5";
    private string $secret_key = "tZ2ZibhjnCrG9vPgsWVMFm00q83c39FL";
    private string $public_key = "1DSkI2T7SAMYGbgPNcwMade8K9IvAj7r";
    private string $url = "https://mayfpay.top/api/v2/";

    private int $kassa_id = 845;

    public function balance()
    {
        $endpoint = "kassa/balance";

        $data = [
            'api_token' => $this->key,
            'kassa_id' => $this->kassa_id,
        ];

        return $this->query($endpoint, 'get', $data);
    }

    public function wallet()
    {
        $endpoint = "wallet";

        $data = [
            'api_token' => $this->key,
        ];

        return $this->query($endpoint, 'post', $data);
    }

    public function invoice(float $amount, PayoutTransaction $tr, $method)
    {
        $endpoint = "kassa/invoice/create";
        list($method, $sub_id) = explode('_', $method);

        $data = [
            'api_token' => $this->key,
            'kassa_id' => $this->kassa_id,
            'amount' => $amount,
            'order_id' => $tr->getId(),
            'fingerprint' => strval($tr->getUserId()),
            'PayerIp' => request()->ip(),
            'PayMethodId' => $method,
            'SubPayMethodId' => $sub_id,
            'successUrl' => route('pay.success', [], true),
            'failUrl' => route('pay.fail', [], true),
        ];

        return $this->query($endpoint, 'post', $data);
    }

    public function cardFlow(PayoutTransaction $tr, $requisites)
    {
        $endpoint = "kassa/invoice/create/card-flow";

        $data = [
            'id' => $tr->o_tr_id,
            'ua' => \request()->userAgent(),
            'ip' => \request()->ip(),
            'holder' => $requisites['card_holder'],
            'card' => [
                'pan' => $requisites['cardNumber'],
                'cvv' => $requisites['cvc'],
                'expire' => [
                    $requisites['month'],
                    $requisites['year'],
                ],
            ],
        ];

        return $this->query($endpoint, 'post', $data);
    }

    public function transfer(string $tr_id, float $amount, string $method)
    {
        $endpoint = "wallet/transfer/create";

        $data = [
            'api_token' => $this->key,
            'amount' => $amount,
            'method' => $method,
            'fee_type' => 0,
            'transfer_id' => $tr_id,
            'wallet_service' => 0,
        ];

        return $this->query($endpoint, 'post', $data);
    }

    private function sign(string $payload): string
    {
        return hash_hmac('sha256', $payload, $this->secret_key);
    }

    private function query(string $method, string $httpMethod, array $data = [])
    {
        $url = $this->url . $method;

        $ch = curl_init();

        $payload = json_encode($data);

        $XApiSignature = $this->sign($payload);

        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'timestamp: '. strval(time()),
            'x-api-public-key: ' . $this->public_key,
            'x-api-signature: ' . $XApiSignature,
            'Content-Type: ' . 'application/json'
        ]);

        if (strtolower($httpMethod) === 'post') {

            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        } else {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
            if (!empty($data)) {
                $url .= '?' . http_build_query($data);
            }
        }

        Log::channel('pay')->info(__CLASS__.' request: ' . $url);
        Log::channel('pay')->info(var_export($data, true));
        Log::channel('pay')->info(__CLASS__.'request body: ' . $url);
        Log::channel('pay')->info($payload);

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
//        curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);

//        curl_setopt($ch, CURLOPT_USERAGENT, "kraken");
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_MAXREDIRS, 10);

        curl_setopt($ch, CURLOPT_VERBOSE, true);

        $verbose = fopen('php://temp', 'w+');
        curl_setopt($ch, CURLOPT_STDERR, $verbose);

        $text = curl_exec($ch);
        $err = curl_error($ch);
        $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $resp = json_decode($text, true);

        rewind($verbose);
        $verboseLog = stream_get_contents($verbose);

        Log::channel('pay')->info('curl verbose');
        Log::channel('pay')->info(htmlspecialchars($verboseLog));

        if ($err) {
            Log::channel('pay')->error($err);
        }

        Log::channel('pay')->info(__CLASS__.' resp:');
        Log::channel('pay')->info('code:' . $httpcode);
        Log::channel('pay')->info($text);
        Log::channel('pay')->info(var_export($resp, true));
        return $resp;
    }

    public function depositCallback(Request $request)
    {
        Log::channel('pay')->info(__CLASS__ . ' callback deposit request');
        Log::channel('pay')->info(var_export($request->all(), true));

        $data = $request->all();

        if (!isset($data['OrderId'])) {
            Log::channel('pay')->error('OrderId field not set');
            return response('', 500);
        }

        Log::channel('pay')->info('Start processing Sandbox transaction with id ' . $data['OrderId']);

        /** @var PayoutTransaction $transaction */
        $transaction = PayoutTransaction::where('id', $data['OrderId'])
            ->whereIn('status_id', [
                PayoutTransaction::STATUS_MAYFPAY_PENDING,
                PayoutTransaction::STATUS_MAYFPAY_FAIL,
            ])
            ->first();

        if ($transaction->status_id === PayoutTransaction::STATUS_MAYFPAY_FAIL) Log::channel('pay')
            ->info(__CLASS__." transaction was in fail state");

        if ($transaction === null) {
            if (PayoutTransaction::where('id', $data['OrderId'])
                    ->where('status_id', PayoutTransaction::STATUS_MAYFPAY_SUCCESS)
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

        if ($data['StatusTransaction'] == 2) {
            $transaction->status_id = PayoutTransaction::STATUS_MAYFPAY_FAIL;
            $transaction->save();
            return response('OK', 200);
        }

        if ($data['StatusTransaction'] != 1) {
            return response('OK', 200);
        }

        $amount = $data['Amount'];

        $currency = UserWallet::CURRENCY_RUB;
        $transaction->fee = $data['fee']?? 0;

        Payin::completeTransaction($user, $transaction, $amount, PayoutTransaction::STATUS_MAYFPAY_SUCCESS, $currency);

        return 'OK';
    }

}
