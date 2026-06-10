<?php

namespace App\PaymentsAPI\Empayre;

use App\MUser;
use App\Payin\Payin;
use App\PayoutTransaction;
use App\UserWallet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * @see https://empayre.github.io/swag-payments/#tag/Invoices/operation/createInvoice
 * @see https://empayre.github.io/swag-wallets/#tag/Withdrawals/operation/getWithdrawal
 * @see https://developer.empayre.com/docs/wallets/withdrawal/
 */
final class Empayre
{
    private string $url = "https://api.pcigate.tech/";
    private string $key = "eyJhbGciOiJSUzI1NiIsInR5cCIgOiAiSldUIiwia2lkIiA6ICJVR0hhTG45RFlkcll4a21nc2J2UFVVbEhjUkU3bDJtVmhnMWthbGtKUEljIn0.eyJleHAiOjE3MjA2ODUxMTUsImlhdCI6MTY4OTE0OTExNiwiYXV0aF90aW1lIjoxNjg5MTQ5MTE1LCJqdGkiOiI5MjNiZGQ4NC1kYTJlLTRiY2YtYmQ3OC1lZWZmYWUyOGY3MDgiLCJpc3MiOiJodHRwczovL2F1dGguZW1wYXlyZS5jb20vYXV0aC9yZWFsbXMvZXh0ZXJuYWwiLCJhdWQiOlsiY29tbW9uLWFwaSIsInVybC1zaG9ydGVuZXIiLCJhY2NvdW50Il0sInN1YiI6Ijc2MjRiMjY1LTA0OTUtNGQ0OS1iMTU2LWRjN2NlNzZiYjJiMSIsInR5cCI6IkJlYXJlciIsImF6cCI6ImtvZmZpbmciLCJub25jZSI6ImI5N2Y4MjEzLWEzYzAtNGZhOC04OWFlLWM2ZTRmNDlkZWI1YSIsInNlc3Npb25fc3RhdGUiOiI4YmVmZmZjZC05ZTJiLTRkMDQtYTAyZC00NThlOWYxY2Q5MTMiLCJhbGxvd2VkLW9yaWdpbnMiOlsiaHR0cHM6Ly9kYXNoYm9hcmQuZW1wYXlyZS5jb20iLCJodHRwOi8vbG9jYWxob3N0OjgwMDAiXSwicmVzb3VyY2VfYWNjZXNzIjp7ImNvbW1vbi1hcGkiOnsicm9sZXMiOlsiaW52b2ljZXMuKi5wYXltZW50czp3cml0ZSIsImN1c3RvbWVycy4qLmJpbmRpbmdzOndyaXRlIiwicGFydHk6cmVhZCIsImludm9pY2VzLioucGF5bWVudHM6cmVhZCIsImN1c3RvbWVyczp3cml0ZSIsInBhcnR5OndyaXRlIiwiY3VzdG9tZXJzLiouYmluZGluZ3M6cmVhZCIsImN1c3RvbWVyczpyZWFkIiwiaW52b2ljZXM6d3JpdGUiLCJpbnZvaWNlczpyZWFkIl19LCJ1cmwtc2hvcnRlbmVyIjp7InJvbGVzIjpbInNob3J0ZW5lZC11cmxzOndyaXRlIiwic2hvcnRlbmVkLXVybHM6cmVhZCJdfSwiYWNjb3VudCI6eyJyb2xlcyI6WyJtYW5hZ2UtYWNjb3VudCIsIm1hbmFnZS1hY2NvdW50LWxpbmtzIiwidmlldy1wcm9maWxlIl19fSwic2NvcGUiOiJvcGVuaWQiLCJzaWQiOiI4YmVmZmZjZC05ZTJiLTRkMDQtYTAyZC00NThlOWYxY2Q5MTMiLCJlbWFpbF92ZXJpZmllZCI6dHJ1ZSwibmFtZSI6IlZpa3RvciBTb2xvZHVuZW5rbyIsInByZWZlcnJlZF91c2VybmFtZSI6InNvbG9kdW5lbmtvNkBnbWFpbC5jb20iLCJnaXZlbl9uYW1lIjoiVmlrdG9yIiwiZmFtaWx5X25hbWUiOiJTb2xvZHVuZW5rbyIsImVtYWlsIjoic29sb2R1bmVua282QGdtYWlsLmNvbSJ9.EEafa53dKf1F6am9i9oQ3gNLk3v2TNoCOT1YFvu5UnK0RAvi2Fp5lPwehZv3SQLoEqq6H9adYlcKWCh382m2CGffXmltZDken_dKqLQMtSckhmxs3CPkRJm-cQ32wG0Fsw46S-IRq5egARcLZMPPOfQwzItMGcqbwIRKLvD5o57KcrAhCpcgTPInI0rSm7bDsbD0lHbTJ8IWxhkLtHbfO_AoiQYggaADalVITiWachGcfjg3yp7QuDLDbIw6GDq-UJeEYco2zf_P5cqJDOns9nSi-_c7R0h_3X5xPf8io1_wQkYwI5Qg3weHcaBBZCE2mii7WQE4dkYIRrRybyJw70JeW3-fnjZ0DUtW14mdptNDk3YuxlTaVTyvgZa6D9kelG51EzVKWa_w90RTaUhTi9YuRvVIEXJwyCQX7LZ4W5z7YaFUlpai25SqpxS09QIz14HhChLLECySDACvXwkrLXOgyzEYybVj_oB-kN8XLOBlUuPLE2PtqvxhVXByKgOF2P6q_n4Ow7BOt4uvUY6hK9gNWpitXgzcMZJoLFHB9QB6tjXDnxh_pQDadLuVS4LjcBx5fNHCH3ZZblLsqtcALdmU1XFvR6Mn8XHXj7u1EWBZ4tZlfFhaKHJQATZl9Yc4dBPPz_DuAB-lBgM-HcN6b--8R-zmoa17OU4pb7B-j74";
//    private string $shop_id = "a861f575-641c-4146-9c99-f934cc164508"; //test
    private string $shop_id = "cfc837c7-2478-4ece-bd9c-690eb8d3a6e4";

    private string $pub_key = "-----BEGIN PUBLIC KEY-----
MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAudet6XJJOC0h/cx0bUmW
bTeAoMV7aj2++qepJChNAWc50LepbtuN+sFypoBcjBSuaaSSrgD0fF75hz5KDg3B
6afI+pnpBAew28v20dgbpQAf4rG85X3s+TXLNnURzlK4CLs03P6o/G9ZknUY7MPo
AdtFD6Ikmm+Q3hi9vS+Z3QD4ybvVrepVRdp2D8UcooDRM/sQ/HA2A+vjufaPf8Ts
RkYhN4D4KHw56qUHdwfoav3aoeGxGVqWMptMD8Ned9IzVuiEkvE2/FIWrgHCt40w
lC04rUwXiJhLmKWXSQwcywG1lkEfbd9SDEKqiPaOxPK+HLnCkbtTSm0EZefj+bEL
awIDAQAB
-----END PUBLIC KEY-----";

    public function getWebhooks()
    {
        $method = 'v2/processing/webhooks';
        $data = [];
        return $this->query($method, 'GET', $data);
    }

    public function createInvoice(int $cents, string $currency, string $tr_id, string $trust)
    {
        $method = 'v2/processing/invoices';
        $data = [
            'shopID' => $this->shop_id,
            'dueDate' => now()->addMinutes(20)->toAtomString(),
            'amount' => $cents,
            'currency' => $currency,
            'product' => 'Krakencassino deposit',
            'metadata' => [
                'transactionId' => $tr_id,
            ],
            'clientInfo' => [
                'trustLevel' => $trust,
            ],
        ];
        return $this->query($method, 'POST', $data);
    }

    public function wallets()
    {
        $method = 'wallet/v0/wallets';
        $data = [
            'limit' => 10,
        ];
        return $this->query($method, 'GET', $data);
    }

    public function destinations(string $identityID, string $currencyID = 'KZT')
    {
        $method = 'wallet/v0/destinations';
        $data = [
            'identityID' => '1171',
            'currencyID' => $currencyID,
            'limit' => 1,
        ];
        return $this->query($method, 'GET', $data);
    }

    public function createDestination(string $id, string $currency, string $phone)
    {
        $method = 'wallet/v0/destinations';
        $data = [
            'name' => $id,
            'identity' => '1171',
            'currency' => $currency,
            'resource' => [
                'type' => 'MobileTransferKZ',
                'phoneNumber' => $phone,
            ],
            'externalID' => $id,
        ];
        return $this->query($method, 'POST', $data);
    }

    public function payout(string $tr_id, string $destination_id, int $amount, string $currency = 'KZT')
    {
        $method = "wallet/v0/withdrawals";
        $data = [
            'wallet' => '454',
            'destination' => $destination_id,
            'body' => [
                'amount' => $amount,
                'currency' => $currency,
            ],
            'externalId' => $tr_id,
        ];
        return $this->query($method, 'POST', $data);
    }

    public function payoutStatus(string $id)
    {
        $method = "wallet/v0/withdrawals/".$id;
        $data = [
        ];
        return $this->query($method, 'GET', $data);
    }

    public function getDestination($id)
    {
        $method = 'wallet/v0/destinations/' . $id;
        $data = [
        ];
        return $this->query($method, 'GET', $data);
    }

    private function query($method, $httpMethod, array $data = [])
    {
        $url = $this->url . $method;

        $ch = curl_init();

        $payload = json_encode((object)$data);
        $request_id = 't'.time().Str::random(5);
        if (empty($data) && $httpMethod === 'GET') $payload = '';



        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $this->key,
            'X-Request-ID: ' . $request_id,
            'X-Request-Deadline: ' . '15s',
//            'Accept: ' . 'application/json',
            'Content-Type: application/json; charset=utf-8',
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

        Log::channel('pay')->info(__CLASS__ . ' request: ' . $url);
        Log::channel('pay')->info(var_export($data, true));
        Log::channel('pay')->info(__CLASS__ . ' request body: ' . $url);
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

        Log::channel('pay')->info(__CLASS__ . 'resp:');
        Log::channel('pay')->info('code:' . $httpcode);
        Log::channel('pay')->info($json);
        Log::channel('pay')->info(var_export($resp, true));
        return $resp;
    }

    private function get_signature_from_header($contentSignature)
    {
        $signature = preg_replace("/alg=(\S+);\sdigest=/", '', $contentSignature);

        if (empty($signature)) {
            throw new \Exception('Signature is missing');
        }

        return $signature;
    }

    private function verify_signature($data, $signature, $publicKey)
    {
        if (empty($data) || empty($signature) || empty($publicKey)) {
            return FALSE;
        }

        $publicKeyId = openssl_get_publickey($publicKey);
        if (empty($publicKeyId)) {
            return FALSE;
        }

        $verify = openssl_verify($data, $signature, $publicKeyId, OPENSSL_ALGO_SHA256);

        return ($verify == 1);
    }

    public function depositCallback(Request $request)
    {
        Log::channel('pay')->info(__CLASS__ . ' callback deposit request');
        Log::channel('pay')->info(var_export($request->all(), true));

        $signatureFromHeader = $this->get_signature_from_header($_SERVER['HTTP_CONTENT_SIGNATURE']);

        $decodedSignature = base64_decode(strtr($signatureFromHeader, '-_,', '+/='));
        $content = file_get_contents('php://input');
        if (!$this->verify_signature($content, $decodedSignature, $this->pub_key)) {
            Log::channel('pay')->error('Webhook notification signature mismatch');
            return response(json_encode(['message' => 'Webhook notification signature mismatch']), 400);
        }

        $data = $request->all();

        if (!isset($data['invoice']['id'])) {
            Log::channel('pay')->error('OrderId field not set');
            return response('', 500);
        }

        Log::channel('pay')->info('Start processing GateExpress transaction with id ' . $data['invoice']['id']);


        /** @var PayoutTransaction $transaction */
        $transaction = PayoutTransaction::where('o_tr_id', $data['invoice']['id'])
            ->where('status_id', PayoutTransaction::STATUS_EMPAYRE_PENDING)
            ->first();

        if ($transaction === null) {
            if (PayoutTransaction::where('o_tr_id', $data['invoice']['id'])
                    ->where('status_id', PayoutTransaction::STATUS_EMPAYRE_SUCCESS)
                    ->first() !== null) {
                Log::channel('pay')->error('Transaction with id ' . $data['invoice']['id'] . ' is already complete found. Request: ' . PHP_EOL . var_export($request->all(), true));
                return response('ok', 200);
            }
            Log::channel('pay')->error('Transaction with id ' . $data['invoice']['id'] . ' is not found. Request: ' . PHP_EOL . var_export($request->all(), true));
            return response('transaction not found', 404);
        }

        // Find user which create this transaction.
        /** @var MUser $user $user */
        $user = MUser::where('id', $transaction->user_id)->first();

        if ($user === null) {
            Log::channel('pay')->error('User of the transaction is not found.');
            return response('user not found', 500);
        }

        if ($data['eventType'] === 'PaymentFailed' || $data['eventType'] === 'PaymentCancelled' || $data['eventType'] === 'InvoiceCancelled') {
            $transaction->status_id = PayoutTransaction::STATUS_EMPAYRE_FAIL;
            $transaction->save();
            return response('OK', 200);
        }


        if ($data['eventType'] !== 'PaymentProcessed' && $data['eventType'] !== 'InvoicePaid') {
            return response('OK', 200);
        }

        $amount = $data['payment']['amount'] / 100;

        $currency = UserWallet::getCurrencyCode($data['payment']['currency']);
        $transaction->fee = $data['payment']['fee'] / 100;

        Payin::completeTransaction($user, $transaction, $amount, PayoutTransaction::STATUS_EMPAYRE_SUCCESS, $currency);

        return 'OK';

    }
}
