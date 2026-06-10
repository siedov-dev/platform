<?php

namespace App\PaymentsAPI\ShadowPays;

use App\Helpers\Hlog;
use App\Helpers\Hosts;
use App\MUser;
use App\Payin\Payin;
use App\PayoutTransaction;
use App\UserWallet;
use App\Wallet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * @docs https://doc.shadowpays.com
 */
final class ShadowPays
{
    private string $api_url = 'https://api.shadowpays.com';
    private string $login = 'API587451200495ID';
    private string $key = '1d6bXzDyJ0RzhP4wRWQz6yvZU9fKu9nckaggn74hNbLKFPiADsFZ5TJVNe3XGKWmHaXXPdDsxER8SMpKrMyQkgu3eDxZECcGkGS9ah6P1piMxHgpf3LNPGrL48PpjYVyMZQ8MT5bGef7iZ5FpEvgswr7eAUptvShHsuzfTV01PJk2cuA0hRdF3C3BbKx2tK3XhkktEQ6Wt88fxXqSqwqpKdT70ExtevCMTcLXdpSi25FMkTDM4Gqx4UAk2nzxt4R';

    public function signature(string $jsonRequest) : string
    {
        return base64_encode(hash_hmac('sha1', $jsonRequest, $this->key, true));
    }

    public function balances()
    {
        return $this->send_query('/api/get-balances');
    }

    public function deposit(PayoutTransaction $tr, string $ClientPhoneNumber): array
    {
        $payload = [
            'ExtId' => (string)$tr->id,
            'Amount' => floor($tr->sum * 100),
            'CountryCode2' => 'KZ',
            'Currency' => 'KZT',
            'ReturnSuccessUrl' => route('pay.success', [], true),
            'ReturnFailedUrl' => route('pay.fail', [], true),
            'ReturnPendingUrl' => route('pay.close-tab', [], true),
            'ClientId' => (string)$tr->user_id,
            'ClientPhoneNumber' => $ClientPhoneNumber,
            'CallbackUrl' => Hosts::getFinHost('RUB') . '/api/pay/shadowpays/callback',
            'PaymentType' => 'Card',
        ];
        return $this->send_query('/api/cdr-transfer', $payload);
    }

    public function payout(string $user_id, string $tr_id, float $amount, string $receiver)
    {
        $payload = [
            'ExtId' => $tr_id,
            'Amount' => floor($amount * 100),
            'CountryCode2' => 'KZ',
            'Currency' => 'KZT',
            'ReceiverPan' => $receiver,
            'PaymentType' => 'CARD',
            'ClientId' => $user_id,
        ];
        return $this->send_query('/api/credit-dr', $payload);
    }

    public function orderState(string $ext_id)
    {
        $payload = [
            'ExtId' => $ext_id,
        ];
        return $this->send_query('/api/order-state', $payload);
    }

    public function callback(Request $request)
    {
        Log::channel('pay')->info(__CLASS__ . ' callback deposit request');
        Log::channel('pay')->info(__CLASS__.var_export($request->getUri(), true));
        Log::channel('pay')->info(__CLASS__.var_export($request->getContent(), true));
        Log::channel('pay')->info(__CLASS__.var_export($request->all(), true));

        if (!$request->has('OrderInfo')) {
            return response('Payload error', status: 500);
        }
        $data = $request->input('OrderInfo');
        $transaction_id = $data['ExtId'];

        try {
            DB::beginTransaction();
            /** @var PayoutTransaction $transaction */
            $transaction = PayoutTransaction::where('id', $transaction_id)
                ->where('id', $transaction_id)
                ->lockForUpdate()
                ->first();

            if ($transaction === null) {
                return response('transaction not found', status: 404);
            }
            if ($transaction->status_id == PayoutTransaction::STATUS_SHADOWPAYS_SUCCESS) {
                Log::channel('pay')->info(__CLASS__ . 'transaction already completed #' . $transaction_id);
                return response('OK', status: 200);
            }

            if ($transaction->status_id == PayoutTransaction::STATUS_SHADOWPAYS_FAIL) {
                Log::channel('pay')->info(__CLASS__ . 'processing failed transaction #' . $transaction_id);
            }

            // Find user which create this transaction.
            /** @var MUser $user $user */
            $user = MUser::where('id', $transaction->user_id)->first();

            if ($user === null) {
                Log::channel('pay')->error('User of the transaction is not found.');
                return response('user not found', 500);
            }

            if ($data['Status'] === 'Failed') {
                $transaction->status_id = PayoutTransaction::STATUS_SHADOWPAYS_FAIL;
                $transaction->save();
                return response('OK', status: 200);
            }

            if ($data['Status'] !== 'Success') {
                return response('OK', status: 200);
            }

            $amount = $data['AmountUsed'] / 100;
            $fee = $data['Fee'] / 100;
            $currency_name = $data['Currency'];
            $currency = Wallet::getCurrencyCode($currency_name);

            $transaction->fee = $fee;
            $transaction->card_number = $data['PAN']?? '';

            return Payin::completeTransaction($user, $transaction, $amount, PayoutTransaction::STATUS_SHADOWPAYS_SUCCESS, $currency);
        } catch (\Exception $e) {
            Hlog::logException($e);
        } finally {
            DB::commit();
        }

        return response('OK', 200);
    }

    private function send_query(string $method, array $data = [])
    {
        $jsonRequest = json_encode($data);
        Log::channel('pay')->info(__CLASS__ . ' send query ' . $this->api_url.$method . ' ' . $jsonRequest);

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Header-Login: ' . $this->login,
            'Header-Sign: ' . $this->signature($jsonRequest),
            'Content-Type: application/json',
        ]);
        $url = $this->api_url . $method;

        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonRequest);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);

        curl_setopt($ch, CURLOPT_USERAGENT, 'KrakenCassino');
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

        Log::channel('pay')->info(__CLASS__ . ' raw response ' . $text);
        Log::channel('pay')->info(__CLASS__ . ' response ' , $resp);
        return $resp;


//        $resp =  Http::asJson()->withHeaders([
//            'Header-Login' => $this->login,
//            'Header-Sign' => $this->signature($jsonRequest),
//        ])
//            ->post($this->api_url . $method, $data)
//            ->body();
//        Log::channel('pay')->info(__CLASS__ . ' response ' . $resp);
//        return json_decode($resp, true);
    }
}
