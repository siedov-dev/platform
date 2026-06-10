<?php

namespace App\PaymentsAPI\B2Pay;

use App\Helpers\Hosts;
use App\Models\PaymentSystems;
use App\MUser;
use App\Payin\Payin;
use App\PayoutTransaction;
use App\UserWallet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * @url https://pay.b2pay.io/merchant/api.php
 */
final class B2Pay
{
//    private string $url = "https://api-v2.b2pay.now/sandbox";
    private string $url = "https://api-v2.b2pay.now/sandbox";
    private string $auth_token = "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwczovL2FwaS12Mi5iMnBheS5ub3ciLCJzdWIiOiJlOTA5Njk2MC1jMzBhLTVkOTMtYTk5MS0xOWZmMDZiNzkwMmMiLCJpYXQiOjE3NjU3OTU1NzMsImV4cCI6MTc5NzI5MjgwMCwibmFtZSI6ImtyYWtlbnJ1X2NvbSJ9._hEdJRtDyr9VcBwzmium4lgQU01lNO-UJfWpCdzw5k0";
    private string $sign_token = "378b12ea4a3d11b82f100ab25a1f6073cb52cd1169b10127e103f45275805c9fafb97655fffb5cc532573dab4b80108fd02e038be3a5202a6f6e29f4f7428509";


    public function deposit(float $amount, string $currency, string $tr_id)
    {
        $payment = [];
        $payment['amount'] = (string) round($amount, 2); //required; allowed up to two decimal places
        $payment['currency'] = $currency; //required
        $payment['description'] = "Kraken deposit"; //required
        $payment['orderNumber'] = $tr_id; //required; unique variable
        $payment['customerType'] = "new";
        $payment['customerIP'] = \request()->ip();
        $payment['callbackUrl'] = Hosts::getFinHost($currency)."/api/pay/b2pay"; //required; URL address for callback
        $payment['successUrl'] = action('PayController@success', [], true); //URL for button on successful payment page
        $payment['failUrl'] = action('PayController@fail', [], true); //URL for button on error payment page

        Log::channel('pay')->info('b2pay deposit data:'. var_export($payment, true));

        return $this->sendApiRequest("/payments", $payment);
    }

    /**
     * Sends a POST request to the API
     *
     * @param array $data The data to send in the request
     * @return array|null Response data or null if an error occurred
     */
    private function sendApiRequest(string $method, array $data) {
        Log::channel('pay')->info('b2pay url ' . $this->url . $method);
        Log::channel('pay')->info('b2pay request:'. var_export($data, true));
        $ch = curl_init();

        // Set cURL options
        curl_setopt_array($ch, [
            CURLOPT_URL => $this->url . $method,
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => [
                'Accept: application/json',
                'Authorization: Bearer ' . $this->auth_token,
                'Content-Type: application/json',
                'User-Agent: Kraken',
            ],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POSTFIELDS => json_encode($data),
        ]);

        // Execute the request
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $contentType = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);

        // Check for cURL errors
        if (curl_errno($ch)) {
            echo 'cURL Error: ' . curl_error($ch) . PHP_EOL;
            curl_close($ch);
            return null;
        }

        curl_close($ch);

        Log::channel('pay')->info('b2pay response code:'. $httpCode);
        Log::channel('pay')->info('b2pay response:'. $response);
        return json_decode($response, true);
    }

    public static function depositCallback(Request $request)
    {
        Log::channel('pay')->info('B2pay callback');
        Log::channel('pay')->info(var_export($request->all(), true));
        $api = new B2Pay();

        $data = $request->all();

        // Signature Verification
        $signature = $api->sign($data);

        if ($data['sign'] != $signature) {
            Log::channel('pay')->error('wring sign, sign: ' . $data['sign'] . ' calculated sign: ' . $signature);
            return response('wrong sign', 403);
        }

        if (!isset($data['orderNumber'])) {
            Log::channel('pay')->error('orderNumber field not set');
            return response('', 500);
        }

        Log::channel('pay')->info('Start processing B2Pay transaction with id ' . $data['orderNumber']);

        /** @var PayoutTransaction $transaction */
        $transaction = PayoutTransaction::where('id', $data['orderNumber'])
            ->whereIn('status_id', [
                PayoutTransaction::STATUS_B2PAY_PENDING,
                PayoutTransaction::STATUS_B2PAY_FAIL,
            ])
            ->first();

        if ($transaction === null) {
            if (PayoutTransaction::where('id', $data['orderNumber'])
                    ->where('status_id', PayoutTransaction::STATUS_B2PAY_SUCCESS)
                    ->first() !== null) {
                Log::channel('pay')->error('Transaction with id ' . $data['orderNumber'] . ' is already complete found. Request: ' . PHP_EOL . var_export($request->all(), true));
                return response('ok', 200);
            }
            Log::channel('pay')->error('Transaction with id ' . $data['orderNumber'] . ' is not found. Request: ' . PHP_EOL . var_export($request->all(), true));
            return response('transaction not found', 404);
        }

        // Find user which create this transaction.
        /** @var MUser $user $user */
        $user = MUser::where('id', $transaction->user_id)->first();

        if ($user === null) {
            Log::channel('pay')->error('User of the transaction is not found.');
            return response('user not found', 500);
        }

        if ($data['status'] !== 'approved') {
            if ($data['status'] !== 'pending') {
                Log::channel('pay')->error('status:' . $data['status']);
                $transaction->status_id = PayoutTransaction::STATUS_B2PAY_FAIL;
                $transaction->save();
            }
            return 'ok';
        }

        $amount = $data['amount'];

        $currency = UserWallet::getCurrencyCode($data['currency']);
        $transaction->fee = 0;
        $transaction->card_number = $data['sanitizedMask']??'';

        Payin::completeTransaction($user, $transaction, $amount, PayoutTransaction::STATUS_B2PAY_SUCCESS, $currency);

        return response('ok', 200);
    }

    public function sign(array $data)
    {
        unset($data['sign']);
        ksort($data);

        $sign_string = implode(':', $data).":".$this->sign_token;
        return base64_encode(md5($sign_string, true));
    }

    public function testSign()
    {
        $data = array (
            'pay_id' => '21336033',
            'token' => '85f66ea9-2fb5-4055-b365-715611b57f9f',
            'type' => 'pay',
            'status' => 'approved',
            'orderNumber' => '7091976',
            'sanitizedMask' => '4444XXXXXXXX1111',
            'amount' => '1507.00',
            'currency' => 'RUB',
            'gatewayRate' => '0.012594880000',
            'gatewayAmount' => '18.98',
            'gatewayCurrency' => 'USD',
            'sign' => 'PJEHz7i0Ris2pyxtRaxs9w==',
        );
        return $this->sign($data);
    }
}
/*
 * [2025-12-16 16:29:23] production.INFO: B2pay callback
[2025-12-16 16:29:23] production.INFO: array (
  'pay_id' => '21336033',
  'token' => '85f66ea9-2fb5-4055-b365-715611b57f9f',
  'type' => 'pay',
  'status' => 'approved',
  'orderNumber' => '7091976',
  'sanitizedMask' => '4444XXXXXXXX1111',
  'amount' => '1507.00',
  'currency' => 'RUB',
  'gatewayRate' => '0.012594880000',
  'gatewayAmount' => '18.98',
  'gatewayCurrency' => 'USD',
  'sign' => 'PJEHz7i0Ris2pyxtRaxs9w==',
)

 */
