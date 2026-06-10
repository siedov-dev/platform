<?php

namespace App\PaymentsAPI\Sandbox;

use App\MUser;
use App\Payin\Payin;
use App\PaymentsAPI\Inwizo\Inwizo;
use App\PayoutTransaction;
use App\UserWallet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class Sandbox
{
    private string $_token = "02a998e707c2db93a7fd0b74ec2856ef";
    private string $_endpoint = "https://cashier.quickpayments.eu/";
    private string $_secret = "02a998e707c2db93a7fd0b74ec2856ef";

    public function paymentSession(float $amount, string $tr_id, array $requisites, string $currency = 'KZT')
    {
        $url = $this->_endpoint . '/api/v3/site_api/payment_sessions';
        $data = [
            'email' => $requisites['email']??'',
            'amount' => $amount,
            'currency' => $currency,
            'return_url' => route('pay.close-tab'),
            'callback_url' => action([Sandbox::class, 'depositCallback']),
        ];

        return $this->query($url, 'post', $data);
    }

    public function paymentSessionInfo(string $id)
    {
        $url = $this->_endpoint . '/api/v3/site_api/payment_sessions/'.$id;
        $data = [
        ];

        return $this->query($url, 'get', $data);
    }

    public function checkSign($data, $sign): bool
    {
        return hash_hmac('sha256', $data, $this->_secret) === $sign;
    }

    private function query($method, $httpMethod, array $data = [])
    {
        $url = $method;
        if (!preg_match('/^http.*$/', $method)) {
            $url = $this->_endpoint . $method;
        }

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Token token=' . $this->_token,
        ]);

        if ( strtolower($httpMethod) === 'post') {

            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));

        } else {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
            if (!empty($data)) {
                $url .= '?' . http_build_query($data);
            }
        }

        Log::channel('pay')->info(__CLASS__.' request: '.$url);
        Log::channel('pay')->info(var_export($data, true));

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
        $resp_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $resp = json_decode($text, true);

        rewind($verbose);
        $verboseLog = stream_get_contents($verbose);

        Log::channel('pay')->info('curl verbose');
        Log::channel('pay')->info(htmlspecialchars($verboseLog));

        if ($err) {
            Log::channel('pay')->error($err);
        }

        Log::channel('pay')->info(__CLASS__.' resp:');
        Log::channel('pay')->info('code:'.$resp_code);
        Log::channel('pay')->info($text);
        Log::channel('pay')->info(var_export($resp, true));
        return $resp;
    }

    public static function depositCallback(Request $request)
    {
        Log::channel('pay')->info(__CLASS__.' callback deposit request');
        Log::channel('pay')->info(var_export($request->all(), true));

        $data = $request->all();

        if (!isset($data['transaction']['uuid'])) {
            Log::channel('pay')->error('number field not set');
            return response('', 500);
        }

        Log::channel('pay')->info('Start processing Sandbox transaction with id ' . $data['transaction']['uuid']);

        $api = new Sandbox();
        if (!$api->checkSign($request->getContent(), $request->header('X-Signature'))) {
            Log::channel('pay')->error('check sign fail');
//            return response('wrong sign', 500);
        }

        /** @var PayoutTransaction $transaction */
        $transaction = PayoutTransaction::where('coment', $data['payment_session']['id'])
            ->where('status_id', PayoutTransaction::STATUS_SANDBOX_PENDING)
            ->first();

        if ($transaction === null) {
            if (PayoutTransaction::where('coment', $data['payment_session']['id'])
                    ->where('status_id', PayoutTransaction::STATUS_SANDBOX_SUCCESS)
                    ->first() !== null) {
                Log::channel('pay')->error('Transaction with id ' . $data['payment_session']['id'] . ' is already complete found. Request: ' . PHP_EOL . var_export($request->all(), true));
                return response('ok', 200);
            }
            Log::channel('pay')->error('Transaction with id ' . $data['payment_session']['id'] . ' is not found. Request: ' . PHP_EOL . var_export($request->all(), true));
            return response('transaction not found', 404);
        }

        // Find user which create this transaction.
        /** @var MUser $user $user */
        $user = MUser::where('id', $transaction->user_id)->first();

        if ($user === null) {
            Log::channel('pay')->error('User of the transaction is not found.');
            return response('user not found', 500);
        }

        if ($data['transaction']['status'] === 'purchase_declined') {
            $transaction->status_id = PayoutTransaction::STATUS_SANDBOX_FAIL;
            $transaction->save();
            return response('OK', 200);
        }

        if ($data['transaction']['status'] !== 'purchase_complete') {
            return response('OK', 200);
        }

        $amount = $data['transaction']['amount'];

        $currency = UserWallet::getCurrencyCode(mb_strtoupper($data['transaction']['currency']));
        $transaction->fee = $amount * 0.1;
        $transaction->card_number = $data['PAN']??'';

        Payin::completeTransaction($user, $transaction, $amount, PayoutTransaction::STATUS_SANDBOX_SUCCESS, $currency);

        return 'OK';
    }

}
