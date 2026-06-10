<?php

namespace App\PaymentsAPI\SarexPays;

use App\ApplicationForPayment;
use App\Helpers\Hosts;
use App\Models\PaymentMethodData;
use App\Models\PaymentSystems;
use App\MUser;
use App\Payin\Payin;
use App\PayoutTransaction;
use App\UserWallet;
use App\Wallet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * @docs https://documenter.getpostman.com/view/13931884/2sAYQdipUu#f29f5470-7895-4e4d-a982-149ee0d48685
 */
final class SarexPays
{
    private string $merchant_id = "GQQC9W8Qi1GD98SgI5tkNghx";
    private string $secret_key = "4c3qHne3DMOpl1OamzIXTRxi9JVApKRs";
    private string $token = "244|URq1ly83ZktWiHHl0Y3XeLfVR7WKGyK3UYuBCsSLe77cb2b9";
    private string $url = "https://sarexpays.com";

    public function balance()
    {
        return $this->query($this->url . '/api/v2/balance', 'GET');
    }

    public function methods(string $currency = 'RUB')
    {
        $payload = [
            'currency' => $currency,
        ];
        return $this->query($this->url . '/api/v2/methods', 'GET', $payload);
    }

    public function deposit(PayoutTransaction $tr, MUser $user, string $method, string $bank, array $requisites = [])
    {
        $payload = [
            'orderId' => (string)$tr->getId(),
            'merchantId' => $this->merchant_id,
            'amount' => $tr->getSum(),
            'assetOrBank' => $bank,
            'method' => $method,
            'currency' => $tr->getCurrency(),
            'callbackUri' => Hosts::getFinHost($tr->getCurrency()).'/api/pay/sarex/deposit',
            'successUri' => route('pay.success'),
            'failUri' => route('pay.fail'),
            'payer' => [
                'userId' => (string)$user->getId(),
                'userIp' => $user->last_ip,
                'customerName' => $requisites['name'],
                'phone' => $requisites['phone'],
                'email' => $requisites['email'],
                'fingerprint' => $user->getFingerprint(),
            ],
        ];
        return $this->query($this->url . '/api/v2/payments', 'POST', $payload);
    }


    public function payout(ApplicationForPayment $tr, string $method, string $bank, array $requisites = [])
    {
        $payload = [
            'orderId' => (string)'o'.$tr->getId(),
            'merchantId' => $this->merchant_id,
            'amount' => $tr->suma,
            'assetOrBank' => $bank,
            'currency' => Wallet::currencyName($tr->currency),
            'method' => $method,
            'callbackUri' => Hosts::getFinHost($tr->getCurrency()).'/api/pay/sarex/deposit',
            'recipient' => $requisites['name'],
            'address' => $requisites['phone'],
        ];
        return $this->query($this->url . '/api/v2/payouts', 'POST', $payload);
    }

    public function orderState(string $tr_id)
    {
        $payload = [
            'orderId' => $tr_id,
        ];
        return $this->query($this->url . '/api/v2/status', 'POST', $payload);
    }

    public function sign(string $content): string
    {
        return hash_hmac('sha256', $content, $this->secret_key);
    }

    public static function depositCallback(Request $request)
    {
        Log::channel('pay')->info(__CLASS__ . 'callback');
        Log::channel('pay')->info(var_export($request->all(), true));
        Log::channel('pay')->info('signature:'.$request->header('Signature'));

        $data = $request->all();

        if (!isset($data['order_id'])) {
            Log::channel('pay')->error('order_id field not set');
            return response('', 500);
        }
        if ($data['type'] !== 'payin') {
            Log::channel('pay')->info('payment type:' . $data['type']);
            return 'ok';
        }

        Log::channel('pay')->info('Start processing Sequoia transaction with id ' . $data['order_id']);

        /** @var PayoutTransaction $transaction */
        $transaction = PayoutTransaction::where('id', $data['order_id'])
            ->whereIn('status_id', [
                PayoutTransaction::STATUS_SAREX_PENDING,
                PayoutTransaction::STATUS_SAREX_FAIL,
            ])
            ->first();

        if ($transaction === null) {
            if (PayoutTransaction::where('id', $data['order_id'])
                    ->where('status_id', PayoutTransaction::STATUS_SAREX_SUCCESS)
                    ->first() !== null) {
                Log::channel('pay')->error('Transaction with id ' . $data['order_id'] . ' is already complete found. Request: ' . PHP_EOL . var_export($request->all(), true));
                return response('ok', 200);
            }
            Log::channel('pay')->error('Transaction with id ' . $data['order_id'] . ' is not found. Request: ' . PHP_EOL . var_export($request->all(), true));
            return response('transaction not found', 404);
        }

        // Find user which create this transaction.
        /** @var MUser $user $user */
        $user = MUser::where('id', $transaction->user_id)->first();

        if ($user === null) {
            Log::channel('pay')->error('User of the transaction is not found.');
            return response('user not found', 500);
        }

        if (in_array($data['status'], ['canceled', 'failed', 'expired'])) {
            Log::channel('pay')->error('status:' . $data['status']);
            $transaction->status_id = PayoutTransaction::STATUS_SAREX_FAIL;
            $transaction->save();
            return response('ok', 200);
        }

        if ($data['status'] !== 'finished') {
            return 'ok';
        }

        $amount = $data['amount'];

        $currency = UserWallet::getCurrencyCode(strtoupper($data['currency']));
        $transaction->fee = $data['commission']?? 0;

        Payin::completeTransaction($user, $transaction, $amount, PayoutTransaction::STATUS_SAREX_SUCCESS, $currency);

        return response('ok', 200);
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

        $headers = [
            'Authorization: Bearer ' . $this->token,
            'Content-Type: application/json',
        ];
        if (!empty($data)) {
            $headers[] = 'Signature: ' . $this->sign(json_encode($data));
        }

        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

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
}
