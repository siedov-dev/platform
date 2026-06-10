<?php

namespace App\PaymentsAPI\BestPay24;

use App\MUser;
use App\Payin\Payin;
use App\PayoutTransaction;
use App\UserWallet;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Illuminate\Support\Arr;

class BestPay24 
{

    private string $testApiUrl = "https://bestpay24cashier.cash/developertestgateway/api/";
    private string $apiUrl = "https://bestpay24cashier.cash/gateway/api/";
    private string $apiId = "9f5cd3ff-5410-403d-916d-ff8c5dfd2a51";
    private string $apiKey = "1QYAq+PhYzRpOJZSCH5qwDhKTWUOOjn9dk/9vrVYn/o=";
    private string $merchantNum = "BP0084";
    private bool   $test = false; 

    public function deposit($orderId, $amount, $email, $formData = null){
        
        $apiUrl = $this->apiUrl;
        if($this->test){
            $apiUrl = $this->testApiUrl;
        }

        $requestData = [
            'MerchantOrderNo' => $orderId,
            'MerchantNo' => $this->merchantNum,
            'Amount' => $amount,
            'SuccessURL' => route('pay.success', [], true),
            'FailureURL' => route('pay.fail', [], true),
            'Currency' => 'INR',
            'CallBackURL' => 'https://krakenfn.com/api/pay/bestpay24/deposit',
            'Details' => [
                'ClientIP' => $this->getUserIp()
            ]
        ];
       
        $sign = $this->creteSign('pay', $requestData, $apiUrl);

        $response = Http::withHeaders([
            'Accept' => 'application/json',
            'Authorization' => 'hmacauth '.$this->apiId.':'.$sign,
            'MerchantNo' => $this->merchantNum
        ])->post($apiUrl.'pay', $requestData);

        return json_decode($response->body(), true);
    }

    public function payout($transactionId, $amount, $currency, $cardNum, $paySys, $requisites)
    {
        $apiUrl = $this->apiUrl;
        if($this->test){
            $apiUrl = $this->testApiUrl;
        }
        
        $requestData = [
            'MerchantOrderNo' => $transactionId,
            'MerchantNo' => $this->merchantNum,
            'Amount' => $amount,
            'SuccessURL' => route('pay.success', [], true),
            'FailureURL' => route('pay.fail', [], true),
            'Currency' => 'INR',
            'PayOutMethod' => 'BankAccount',
            'CallBackURL' => 'https://krakenfn.com/api/pay/bestpay24/withdraw',
            'Details' => [
                'AccountNo' => $requisites['accountNum'],
                'AccountName' => $requisites['accountName'],
                'AccountIFSC' => $requisites['accountIFSC'],
                'BankCode' => $requisites['bankCode'],
                'ClientIP' => $this->getUserIp()
            ]
        ];

        $sign = $this->creteSign('withdraw_v2', $requestData, $apiUrl);

        $response = Http::withHeaders([
            'Accept' => 'application/json',
            'Authorization' => 'hmacauth '.$this->apiId.':'.$sign,
            'MerchantNo' => $this->merchantNum
        ])->post($apiUrl.'pay', $requestData);

        return json_decode($response->body(), true);
    }

    function creteSign($method, $requestData, $apiUrl) {
        $requestContentBase64String = '';
        //Get the Request URI
        $requestUri=strtolower(urlencode($apiUrl.$method));
        $requestHttpMethod = 'POST';

        $content=json_encode($requestData);
        $requestContentHash = md5($content, true);
        $requestContentBase64String = base64_encode($requestContentHash);

        $signatureRawData = $this->apiId . $requestHttpMethod . $requestUri . $requestContentBase64String;
        $secretKeyByteArray = base64_decode($this->apiKey);

        //Generate the hmac signature and set it in the Authorization header
        $requestSignatureBase64String = hash_hmac('sha256', $signatureRawData, $secretKeyByteArray, true);
        $requestSignatureBase64String2 = base64_encode($requestSignatureBase64String);

        return $requestSignatureBase64String2;
    }

    public static function withdrawCallback(Request $request){

        Log::channel('payout')->info(__CLASS__ . ' callback payout request');
        Log::channel('payout')->info(var_export($request->all(), true));

        $data = $request->all();

        if (!isset($data['MerchantOrderNo'])) {
            Log::channel('payout')->error('MerchantOrderNo field not set');
            return response('', 500);
        }

        $applicationForPayment = ApplicationForPayment::where('id', $data['MerchantOrderNo'])
            ->where('confirmed', ApplicationForPayment::STATUS_AUTO_PAY_PENDING)
            ->first();

        
        if(!$applicationForPayment){
            Log::channel('payout')->error('Transaction with id ' . $data['MerchantOrderNo'] . ' is not found. Request: ' . PHP_EOL . var_export($request->all(), true));
            return response('transaction not found', 404);
        }

        if ($data['Status'] == 3) {
            $applicationForPayment->confirmed = ApplicationForPayment::STATUS_AUTO_PAY_ERROR;
            $applicationForPayment->save();
            return response('OK', 200);
        }

        if ($data['Status'] == 2) {
            $applicationForPayment->confirmed = ApplicationForPayment::STATUS_AUTO_PAY_SUCCESS;
            $applicationForPayment->comentar = 'Заявка завершена';
            $applicationForPayment->save();

            DB::table('payout_transactions')
                ->insert([
                    'user_id' => $applicationForPayment->user_id,
                    'hall_id' => $applicationForPayment->hall_id,
                    'date' => date('Y-m-d H:i:s'),
                    'currency' => UserWallet::currencyName($applicationForPayment->currency),
                    'operation' => 'Автовыплата BestPay24',
                    'sum' => $applicationForPayment->money,
                    'fee' => $applicationForPayment->fee,
                    'total_sum' => $applicationForPayment->money+$applicationForPayment->fee,
                    'status_id' => 45
                ]);

            
            return response('OK', 200);
        }

    }

    public static function depositCallback(Request $request)
    {
        Log::channel('pay')->info('BestPay24 callback');
        Log::channel('pay')->info(var_export($request->all(), true));

        $data = $request->all();
       
        if (!isset($data['MerchantOrderNo'])) {
            Log::channel('pay')->error('MerchantOrderNo field not set');
            return response('', 500);
        }

        if (!isset($data['Status']) || $data['Status'] != 2){
            Log::channel('pay')->error('Status decline');
            return response('', 500);
        }

        $transaction = PayoutTransaction::where('id', $data['MerchantOrderNo'])
            ->where('status_id', PayoutTransaction::STATUS_BESTPAY24_PENDING)
            ->first();

        if ($transaction === null) {
            if (PayoutTransaction::where('id', $data['MerchantOrderNo'])
                    ->where('status_id', PayoutTransaction::STATUS_BESTPAY24_SUCCESS)
                    ->first() !== null) {
                Log::channel('pay')->error('Transaction with id ' . $data['MerchantOrderNo'] . ' is already complete found. Request: ' . PHP_EOL . var_export($request->all(), true));
                return response('ok', 200);
            }
            
            Log::channel('pay')->error('Transaction with id ' . $data['MerchantOrderNo'] . ' is not found. Request: ' . PHP_EOL . var_export($request->all(), true));
            return response('transaction not found', 404);
        }

        // Find user which create this transaction.
        /** @var MUser $user $user */
        $user = MUser::where('id', $transaction->user_id)->first();

        if ($user === null) {
            Log::channel('pay')->error('User of the transaction is not found.');
            return response('user not found', 500);
        }

        if ($data['Status'] == 3) {
            $transaction->status_id = PayoutTransaction::STATUS_BESTPAY24_FAIL;
            $transaction->save();
            return response('OK', 200);
        }


        if ($data['Status'] == 2) {
            $amount = $data['Amount'];

            $currency = 5; 
            Payin::completeTransaction($user, $transaction, $amount, PayoutTransaction::STATUS_BESTPAY24_SUCCESS, $currency);
        }

        return response('OK', 200);
    }

    function getUserIp() : String {
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else {
            $ip = $_SERVER['REMOTE_ADDR'];
        }

        return $ip;
    }

}
