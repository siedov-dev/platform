<?php

namespace App\PaymentsAPI\Pay4x;

use App\MUser;
use App\Payin\Payin;
use App\PayoutTransaction;
use App\UserWallet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * @docs https://pay4x.io/docs/api_merchant
 */
final class Pay4x
{
    private string $merchantId = "190d13e2-fcb7-f9db-2c87-50c162371f9c";
    private string $login = "krakenUser";
    private string $secret = "0)RmS41q3";
    private string $url = "https://test.pay4x.io/api_merchant";

    public function accounts()
    {
        $method = "accounts";
        $data = [
        ];

        return $this->query($method, 'get', $data);
    }

    public function account(string $id)
    {
        $method = "account";
        $data = [
            'accountId' => $id
        ];

        return $this->query($method, 'get', $data);
    }

    public function transactions(?string $status = null)
    {
        $method = "transaction";
        $data = [];

        if ($status !== null) $data['status'] = $status;

        return $this->query($method, 'get', $data);
    }

    public function transaction(string $tr_id)
    {
        $method = "transaction";
        $data = [
            'transactionId' => $tr_id,
        ];

        return $this->query($method, 'get', $data);
    }

    private function sign(string $request_id): string
    {
        return hash('sha512', $this->secret . $this->merchantId . $this->login . $request_id);
    }

    private function query($method, $httpMethod, array $data = [])
    {
        $url = $method;
        if (!preg_match('/^http.*$/', $method)) {
            $url = $this->url . $method;
        }

        $request_id = time() . '-' . Str::random(6);

        $data['merchantId'] = $this->merchantId;
        $data['login'] = $this->login;
        $data['requestId'] = $request_id;
        $data['signature'] = $this->sign($request_id);

        $ch = curl_init();

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
        Log::channel('pay')->info(var_export($request->getContent(), true));

        $data = json_decode($request->getContent(), true);
        Log::channel('pay')->info(__CLASS__.' callback deposit decoded');
        Log::channel('pay')->info($data);

        if (!isset($data['transactionId'])) {
            Log::channel('pay')->error('id field not set');
            return response('', 500);
        }

        $api = new Pay4x();

        Log::channel('pay')->info('Start processing transaction with id ' . $data['transactionId']);

        $tr_data = $api->transaction($data['transactionId']);

        if (($tr_data['success']?? false) !== 'true') {
            Log::channel('pay')->error('no transaction data ' . $data['transactionId'] .PHP_EOL . var_export($request->all(), true));
            return response('no transaction data', 500);
        }

        if ($tr_data['data']['transaction_type'] !== 'Deposit') {
            Log::channel('pay')->info('not deposit');
            return response('ok', 200);
        }

        /** @var PayoutTransaction $transaction */
        $transaction = PayoutTransaction::where('o_tr_id', $data['transactionId'])
            ->where('status_id', PayoutTransaction::STATUS_PAY4X_PENDING)
            ->first();

        if ($transaction === null) {
            if (PayoutTransaction::where('o_tr_id', $data['transactionId'])
                    ->where('status_id', PayoutTransaction::STATUS_PAY4X_SUCCESS)
                    ->first() !== null) {
                Log::channel('pay')->error('Transaction with id ' . $data['transactionId'] . ' is already complete found. Request: ' . PHP_EOL . var_export($request->all(), true));
                return response('ok', 200);
            }
            Log::channel('pay')->error('Transaction with id ' . $data['transactionId'] . ' is not found. Request: ' . PHP_EOL . var_export($request->all(), true));
            return response('transaction not found', 404);
        }

        if ($tr_data['data']['status'] !== 'Agent Confirmed') {
            $transaction->status_id = PayoutTransaction::STATUS_PAY4X_FAIL;
            $transaction->save();
            Log::channel('pay')->info('status '. $tr_data['data']['status']);
            return response('ok', 200);
        }

        // Find user which create this transaction.
        /** @var MUser $user $user */
        $user = MUser::where('id', $transaction->user_id)->first();

        if ($user === null) {
            Log::channel('pay')->error('User of the transaction is not found.');
            return response('user not found', 500);
        }

        $amount = $tr_data['data']['amount'];

        $currency = UserWallet::getCurrencyCode(mb_strtoupper($data['fiat']));
        $transaction->fee = $tr_data['data']['commission']?? 0;

        Payin::completeTransaction($user, $transaction, $amount, PayoutTransaction::STATUS_PAY4X_SUCCESS, $currency);

        return 'OK';
    }
}
