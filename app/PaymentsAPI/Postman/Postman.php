<?php

namespace App\PaymentsAPI\Postman;

use App\Models\PaySystemsConfig;
use App\MUser;
use App\Payin\Payin;
use App\PaymentsAPI\PaymentCenter\PaymentCenter;
use App\PayoutTransaction;
use App\UserWallet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * @see https://documenter.getpostman.com/view/23174491/2s8YzRxhWA#10e7c138-6ef4-4446-b837-910f16196b33
 */
final class Postman
{
    private string $name = "demo@merchant.com";
    private string $uuid = "5a10d482c813b6b64dc2efeac6d4cf94fc97354f";
    private string $token = "243b9f6b2106a07663a734a5dcfc94a2cda1bca1";
    private string $refresh_token = "3af8b34ee1ccb7931dc64c81be6fcfd0e5219de7";
    private string $url = "https://back.trunk.test-leantech.space";

    private int $resp_code;

    public function __construct()
    {
        $conf = PaySystemsConfig::getSystemConfig(Payin::TYPE_POSTMAN);
        $this->name = $conf['name'];
        $this->uuid = $conf['uuid'];
        $this->refresh_token = $conf['refresh_token'];
        $this->url = $conf['url'];
        Log::info($this->refresh_token);
    }

    public function createBuy(string $amount, string $tr_id, string $user_id)
    {
        $method = "/tapi/createbuy";
        $data = [
            'amount' => $amount,
            'callback' => 'https://krakencassino.com/api/pay/postman',
            'invoiceId' => $tr_id,
            'currency' => 'rub',
//            'redirect' => action('PayController@success'),
            'successRedirect' => action('PayController@success'),
            'cancelRedirect' => action('PayController@fail'),
            'clientId' => $user_id,
        ];

        return $this->query($method, 'post', $data);
    }

    public function refreshToken()
    {
        $method = "/api/merchant-renew-token";
        $data = [
            'refreshToken' => $this->refresh_token,
            'oldUuid' => $this->uuid,
        ];

        $resp = $this->query($method, 'post', $data);
        if (!empty($resp['uuid'])) $this->uuid = $resp['uuid'];
        PaySystemsConfig::setSystemConfig(Payin::TYPE_POSTMAN, 'uuid', $resp['uuid']);
    }

    public function revokeToken()
    {
        $method = "/api/merchant-revoke-token";
        $data = [
            'uuid' => $this->uuid,
        ];

        return $this->query($method, 'post', $data);
    }

    private function query($method, $httpMethod, array $data = [])
    {
        $url = $this->url . $method;

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'name: ' . $this->name,
            'uuid: ' . $this->uuid,
            'Content-Type: application/json',
        ]);

        if ( strtolower($httpMethod) === 'post') {

            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data, JSON_UNESCAPED_UNICODE));

        } else {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
            if (!empty($data)) {
                $url .= '?' . http_build_query($data);
            }
        }

        Log::channel('pay')->info('Postman request: '.$url);
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
        $this->resp_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $resp = json_decode($text, true);

        rewind($verbose);
        $verboseLog = stream_get_contents($verbose);

        Log::channel('pay')->info('curl verbose');
        Log::channel('pay')->info(htmlspecialchars($verboseLog));

        if ($err) {
            Log::channel('pay')->error($err);
        }

        Log::channel('pay')->info('Postman resp:');
        Log::channel('pay')->info('code:'.$this->resp_code);
        Log::channel('pay')->info($text);
        Log::channel('pay')->info(var_export($resp, true));
        return $resp;
    }

    public function depositCallback(Request $request)
    {
        Log::channel('pay')->info('Postman callback deposit request');
        Log::channel('pay')->info(var_export($request->all(), true));
        if (!isset($request->all()['transaction'])) {
            Log::channel('pay')->error('transaction field not set');
            return response('', 500);
        }

        $data = $request->all()['transaction'];

        if (!isset($data['invoiceId'])) {
            Log::channel('pay')->error('invoiceId field not set');
            return response('', 500);
        }

        Log::channel('pay')->info('Start processing Postman transaction with id ' . $data['invoiceId']);

        /** @var PayoutTransaction $transaction */
        $transaction = PayoutTransaction::where('id', $data['invoiceId'])
            ->where('status_id', PayoutTransaction::STATUS_POSTMAN_PENDING)
            ->first();

        if ($transaction === null) {
            if (PayoutTransaction::where('id', $data['invoiceId'])
                    ->where('status_id', PayoutTransaction::STATUS_POSTMAN_SUCCESS)
                    ->first() !== null) {
                Log::channel('pay')->error('Transaction with id ' . $data['invoiceId'] . ' is already complete found. Request: ' . PHP_EOL . var_export($request->all(), true));
                return response('ok', 200);
            }
            Log::channel('pay')->error('Transaction with id ' . $data['invoiceId'] . ' is not found. Request: ' . PHP_EOL . var_export($request->all(), true));
            return response('transaction not found', 404);
        }

        // Find user which create this transaction.
        /** @var MUser $user $user */
        $user = MUser::where('id', $transaction->user_id)->first();

        if ($user === null) {
            Log::channel('pay')->error('User of the transaction is not found.');
            return response('user not found', 500);
        }

        if ($data['status'] === 'Cancelled') {
            Log::channel('pay')->error('status:' . $data['status']);
            $transaction->status_id = PayoutTransaction::STATUS_POSTMAN_FAIL;
            $transaction->save();
            return json_encode(['status' => 'ok']);
        }
        if ($data['status'] !== 'Confirmed') {
            return json_encode(['status' => 'ok']);
        }
        $amount = $data['amount'];

        $currency = UserWallet::getCurrencyCode(mb_strtoupper($data['currency']));
        $transaction->fee = $amount * 0.08;

        Payin::completeTransaction($user, $transaction, $amount, PayoutTransaction::STATUS_POSTMAN_SUCCESS, $currency);

        return json_encode(['status' => 'ok']);
    }
}
