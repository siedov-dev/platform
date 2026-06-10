<?php

namespace App\PaymentsAPI\Pspy;

use App\ApplicationForPayment;
use App\Helpers\Hosts;
use App\MUser;
use App\Payin\Payin;
use App\PayoutTransaction;
use App\UserWallet;
use App\Wallet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

final class PspyApi
{
    private string $api_key = "h9LkoXpFxJfLWZkJtSOkGQRo686Ctyco";
    private string $api_url = "https://engine.pspy.io";
    private string $sign_key = "4L25YEVqGNe6";

    public function balances()
    {
        return $this->query($this->api_url . '/api/v1/balances', 'GET');
    }

    public function deposit(PayoutTransaction $tr, float $amount)
    {
        $payload = [
            'referenceId' => $tr->id,
            'amount' => $amount,
            'currency' => $tr->currency,
            'paymentType' => 'DEPOSIT',
            'returnUrl' => route('pay.close-tab'),
            'webhookUrl' => Hosts::getFinHost($tr->currency).'/api/pay/pspy/deposit',
        ];
        return $this->query($this->api_url . '/api/v1/payments', 'POST', $payload);
    }

    public function payout(ApplicationForPayment $tr, string $method, array $requisites = [])
    {
        $payload = [
            'referenceId' => 'o'.$tr->id,
            'paymentType' => 'WITHDRAWAL',
            'amount' => $tr->suma,
            'currency' => Wallet::currencyName($tr->currency),
            'card' => [
                'cardNumber' => $requisites['cardNumber'],
                'cardholderName' => $requisites['card_holder'],
                'expiryMonth' => $requisites['month'],
                'expiryYear' => $requisites['year'],
            ],
        ];
        return $this->query($this->api_url . '/api/v1/payments', 'POST', $payload);
    }

    public function getPayment(string $tr_id)
    {
        return $this->query($this->api_url . '/api/v1/payments/' . $tr_id, 'GET');
    }

    public function sign(string $body): string
    {
        return hash_hmac('sha256', $body, $this->sign_key);
    }

    private function query($url, $httpMethod, array $data = [])
    {
        $ch = curl_init();

        if (strtolower($httpMethod) === 'post') {

            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

        } else {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
            if (!empty($data)) {
                $url .= '?' . http_build_query($data);
            }
        }

        Log::channel('pay')->info(__CLASS__ . 'request: ' . $url);
        Log::channel('pay')->info(var_export($data, true));

        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $this->api_key,
        ]);

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);

        curl_setopt($ch, CURLOPT_USERAGENT, 'KrakenCassino');
        curl_setopt($ch, CURLOPT_TIMEOUT, 90);
        curl_setopt($ch, CURLOPT_MAXREDIRS, 10);

        curl_setopt($ch, CURLOPT_VERBOSE, true);

        $verbose = fopen('php://temp', 'w+');
        curl_setopt($ch, CURLOPT_STDERR, $verbose);

        $text = curl_exec($ch);
        $err = curl_error($ch);
        $resp_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $resp = json_decode($text, true);

        rewind($verbose);
        $verboseLog = stream_get_contents($verbose);

        Log::channel('pay')->info('curl verbose');
        Log::channel('pay')->info(htmlspecialchars($verboseLog));

        if ($err) {
            Log::channel('pay')->error($err);
        }

        Log::channel('pay')->info(__CLASS__ . 'resp: ');
        Log::channel('pay')->info('code:' . $resp_code);
        Log::channel('pay')->info($text);
        Log::channel('pay')->info(var_export($resp, true));
        return $resp;
    }

    public static function depositCallback(Request $request)
    {
        Log::channel('pay')->info(__CLASS__ . " callback:", $request->all());
        Log::channel('pay')->info(__CLASS__ . " callback raw:". var_export($request->getContent(), true));
        $data = $request->all();
        Log::channel('pay')->info(__CLASS__ . " callback data:", $data);

        $targetUrl = "http://127.0.0.1:23106/pay/pspy/deposit";
        $response = Http::withHeaders($request->headers->all())
            ->withBody($request->getContent(), $request->header('Content-Type'))
            ->send($request->method(), $targetUrl);
        Log::channel('pay')->info('pspy callback response ' . var_export($response->body(), true));
        return response($response->body(), $response->status())->withHeaders($response->headers());

//        Log::channel('pay')->info(__CLASS__ . 'callback');
//        Log::channel('pay')->info(var_export($request->all(), true));
//
//        $data = $request->all();
//
//        if (!isset($data['referenceId'])) {
//            Log::channel('pay')->error('referenceId field not set');
//            return response('', 500);
//        }
//        if ($data['paymentType'] !== 'DEPOSIT') {
//            Log::channel('pay')->info('payment type:' . $data['paymentType']);
//            return 'ok';
//        }
//
//        Log::channel('pay')->info('Start processing Sequoia transaction with id ' . $data['referenceId']);
//
//        $api = new PspyApi();
//        if ($api->sign($request->getContent()) !== $request->header('SignatureAuth')) {
//            Log::channel('pay')->error('Invalid signature request signature:'.
//                $request->header('SignatureAuth') .
//                ' calculated:' . $api->sign($request->getContent()));
////            return response('invalid signature', 403);
//        }
//
//        /** @var PayoutTransaction $transaction */
//        $transaction = PayoutTransaction::where('id', $data['referenceId'])
//            ->whereIn('status_id', [
//                PayoutTransaction::STATUS_PSPY_PENDING,
//                PayoutTransaction::STATUS_PSPY_FAIL,
//            ])
//            ->first();
//
//        if ($transaction === null) {
//            if (PayoutTransaction::where('id', $data['order_id'])
//                    ->where('status_id', PayoutTransaction::STATUS_PSPY_SUCCESS)
//                    ->first() !== null) {
//                Log::channel('pay')->error('Transaction with id ' . $data['referenceId'] . ' is already complete found. Request: ' . PHP_EOL . var_export($request->all(), true));
//                return response('ok', 200);
//            }
//            Log::channel('pay')->error('Transaction with id ' . $data['referenceId'] . ' is not found. Request: ' . PHP_EOL . var_export($request->all(), true));
//            return response('transaction not found', 404);
//        }
//
//        // Find user which create this transaction.
//        /** @var MUser $user $user */
//        $user = MUser::where('id', $transaction->user_id)->first();
//
//        if ($user === null) {
//            Log::channel('pay')->error('User of the transaction is not found.');
//            return response('user not found', 500);
//        }
//
//        if (in_array($data['state'], ['CANCELLED', 'DECLINED'])) {
//            Log::channel('pay')->error('status:' . $data['state']);
//            $transaction->status_id = PayoutTransaction::STATUS_PSPY_FAIL;
//            $transaction->save();
//            return response('ok', 200);
//        }
//
//        if ($data['state'] !== 'COMPLETED') {
//            return 'ok';
//        }
//
//        $amount = $data['amount'];
//
//        $currency = UserWallet::getCurrencyCode(strtoupper($data['currency']));
//        $transaction->fee = $data['commission']?? $amount * 0.069;
//
//        Payin::completeTransaction($user, $transaction, $amount, PayoutTransaction::STATUS_PSPY_SUCCESS, $currency);
//
//        return response('ok', 200);
    }
}
