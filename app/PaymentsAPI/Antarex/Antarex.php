<?php

namespace App\PaymentsAPI\Antarex;

use App\ApplicationForPayment;
use App\Models\PaySystemsConfig;
use App\MUser;
use App\Payin\Payin;
use App\PayoutTransaction;
use App\UserWallet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * @url https://antrpay.com/en/login
 * @docs https://docs.antarex.io/openapi/v1/#/operations/Create-card-to-card-payment-H2H
 */
final class Antarex
{
    private string $pub_key = "Nwq3qEHuFB963ivhrAqhEVIB9PKKTx54fzpX4AaNAOvnq78dfQTQMWhT3IrPIty18BD0CyLHfGaNaPHw6KjQ5WI2MbF2H0t1KzPwegLiFGAHVDkfoWAdCynQonlA1j5KPWGBBCD3h9HSAgrHAU3ECBNj953O1kyAlrlEcqPiYcA5cLhJwdUQMdvJJvdmShey9NvUYAnUZivaA2R81okPyiTcCtOvtPt99ZgcHh4Oiv8pTKvO1vBaNGZC33W9jXdN";
    private string $pub_key_ptp = "IdAlNCRp0gmxFPxva5ANLqHL5dkfCvUE0m3Zws4SWOiZiZ2TRs07ONYi48wpdj5ANlc3FHvWYZ6au201UP8w0apYespCi0n6BuQM9ywMtKsgLWoSX5YUqsXq4q3GNNsNG2qXgo7QLTpOJPM9P0TtfJaBz9DvfDaZVsRLOuAalaIH0WfWrYlXNQrTT4lbMQCP5BXUFYr6gwaVy2BYQo8hvuQcgphDkFiYFwsKVOUSuow2qwkSFTfTcS8SQR4D9N3Q";
    private string $secret_key = "YKhBu3W5JsCGAHa34Pb0B5PYFxPUjrQI7c0uYJND8bb6cWKzNhHQSCyd6XCoQi1Zv9JePZf2L7GcYqf5CJH0s4QltBauYGcvy5KVtElI9xnK9tgSgFE2nODqVSPC4mBUW7jJmGqgBsofz5M6I9gcwF7pUEvpLJQB4JARRmonMF12gJFxReeCREqs17cyGfoyZO9cnnCf1Z0nEaYHp4g3n3JSbToIsqU9UBPVMbBFo5ceIYaZuReunFkFRg4WQIhFFc5NKKEXWhdNqKJ9irMke9LURBcWWSiQkJDlIwq017Xfw1f8txrti0JTpgBHJSeqUUFzT9g7PkS6e2caAyvwMVkKD8MHkV9jAuE99j8fWDjCQpAoHnISCCUq7pzLY41xrVLEFfOmeGGmH5O59MNvoJWfG0JUkowd6ZwPfhmHuuBfjuDvNTAgpZWtuKigE4KrCcjpJbCKmys51cnpTdZChS4zHEhdwr9vE2IXWoFtc8FxvhKBxkukX5J1sFbli0Rm";
    private string $secret_key_ptp = "8T6ASAmD0mQkJtHz6yzPR8zZkVzVCuQWdbQ8w8cWAPE8xhAv6rk2Ema9pvraJMtexqQrpLDuTX9ukC8PDu7iIQnxPToaoRPkVaVq1dkBifl4eHgx8CuBZDGCoDzgW1rjsGEGu98VTBzUkkN6Z4uSYncNhPuyc1MK9Htg1c52OERExgDkArK9fKPzEFmBNX8wxNdaQ04seR0qrtsiDyegp2mvCkomrbr47dVTcs5N6RqlnDVFKq0n56EJFx4U1p8wHfRrPtEL763JSbtyFIFmrXWHRAvOzBlbvwsXPlYCJCj7bxaeTcJBAy4dxqEq8yXvk3xEnewOxTlP0GzrDIxKvRbFP3Os0mVGCgb0BGxDUlcVyd2EgzMITyvL7nOZcsymyLHi1fxoNBbzsVLhyM7K8N9A1Ev5D2WU9mFwBaAMqAhLTSnDrzR0e4odUskxLWV0CO0Ns43FlzSBSsgJ2qaG2qyCFEF5a5EeNUigYIKeJ3ZnaVdxWlAObxs9tR9VuZDh";
    private string $api_url = "https://antrpay.com/api/api/";
    private int $api_id_ptp = 1842;

    private array $currencies = [
        UserWallet::CURRENCY_RUB => 1,
        UserWallet::CURRENCY_KZT => 3,
    ];

    public function __construct()
    {
        $conf = PaySystemsConfig::getSystemConfig(Payin::TYPE_ANTAREX);
        $this->pub_key = $conf['pub_key'];
        $this->secret_key = $conf['secret_key'];
    }

    public function sign(array $data): string
    {
        $hmac = hash_hmac('sha512', $this->arrImplode($data), $this->secret_key, true);
        $hash = hash('sha512', $hmac, true);
        return base64_encode($hash);
    }

    public function arrImplode(array $data): string
    {
        $result = "";
        foreach ($data as $k => $v) {
            if ($k === 'ttl') continue;
            if (is_array($v)) {
                $result = $result . $this->arrImplode($v);
            } else {
                $result = $result . $v;
            }
        }
        return $result;
    }

    public function balance()
    {
        $enpoint = "/balance";
        $data = [
            'api_key' => $this->pub_key,
        ];
        return $this->query($enpoint, 'get', $data);
    }

    public function deposit(float $amount, int $currency, string $tr_id, $verified = false)
    {
        $enpoint = "/payment-c2c-form";
        $hash = hash_hmac('sha256', $tr_id . $amount, $this->pub_key);
        $data = [
            'api_key' => $this->pub_key,
            'paymentDetails' => [
                'redirectUrls' => [
                    'successURL' => route('pay.success', [], true),
                    'failURL' => route('pay.fail', [], true),
                ],
                'ttl' => 3600,
                'amount' => number_format($amount, 2, '.', ''),
                'currencyCode' => UserWallet::currencyName($currency),
                'comment' => 'Kraken',
            ],
            'webhookUrl' => 'https://krakencassino.com/api/pay/antarex?tr_id=' . $tr_id . '&hash=' . $hash,
        ];
        return $this->query($enpoint, 'post', $data);
    }

    public function depositCps(int $amount, int $currency, PayoutTransaction $transaction, $verified = false)
    {
        $enpoint = "repayment/create_payment_cps";
        $this->pub_key = $this->pub_key_ptp;
        $this->secret_key = $this->secret_key_ptp;

        $hash = hash_hmac('sha256', $transaction->id . $amount, $this->pub_key);

        $data = [
            'clientOrderID' => "".$transaction->id,
            'payerID' => '' . $transaction->user_id,
            'amount' => $amount,
            'payerIP' => \request()->ip(),
            'currencyId' => 1, //RUB hardcoded
            'callbackURL' => 'https://krakenfn.com/api/pay/antarex?tr_id=' . $transaction->id . '&hash=' . $hash,
            'redirect' => [
                'successURL' => route('pay.success'),
                'failURL' => route('pay.fail'),
            ],
        ];

        return $this->query($enpoint, 'post', $data);
    }

    public function depositFps(int $amount, int $currency, PayoutTransaction $transaction, $verified = false)
    {
        $enpoint = "repayment/create_payment_fps";
        $this->pub_key = $this->pub_key_ptp;
        $this->secret_key = $this->secret_key_ptp;

        $hash = hash_hmac('sha256', $transaction->id . $amount, $this->pub_key);

        $data = [
            'clientOrderID' => "".$transaction->id,
            'payerID' => '' . $transaction->user_id,
            'amount' => $amount,
            'expireAt' => 3600,
            'comment' => 'deposit',
            'clientIP' => \request()->ip(),
            'currencyId' => 1, //RUB hardcoded
            'webhookURL' => 'https://krakenfn.com/api/pay/antarex?tr_id=' . $transaction->id . '&hash=' . $hash,
            'redirectURLs' => [
                'successUrl' => route('pay.success'),
                'failUrl' => route('pay.fail'),
            ],
        ];

        return $this->query($enpoint, 'post', $data);
    }

    public function depositPtp(int $amount, int $currency, PayoutTransaction $transaction, $verified = false)
    {
        $this->pub_key = $this->pub_key_ptp;
        $this->secret_key = $this->secret_key_ptp;

        $this->api_url = "https://antrpay.com/api/api/";
        $enpoint = "repayment/p2p_form";
        $hash = hash_hmac('sha256', $transaction->id . $amount, $this->pub_key);

        $data = [
            'clientOrderID' => 't' . $transaction->id,
            'payerID' => '' . $transaction->user_id,
            'amount' => $amount,
            'expireAt' => 3600,
            'comment' => 'deposit',
            'clientIP' => \request()->ip(),
            'currencyID' => 1, //RUB hardcoded
            'callbackURL' => 'https://krakenfn.com/api/pay/antarex?tr_id=' . $transaction->id . '&hash=' . $hash,
            'redirect' => [
                'successURL' => route('pay.success'),
                'failURL' => route('pay.fail'),
            ],
        ];

        return $this->query($enpoint, 'post', $data);
    }

    public function bankSelectUrl(float $amount, int $currency, string $tr_id, $verified = false)
    {
//        $enpoint = "/payment-c2c-bank-select";
        $enpoint = "/payment";
        $hash = hash_hmac('sha256', $tr_id . $amount, $this->pub_key);
        $data = [
            'api_key' => $this->pub_key,
            'paymentDetails' => [
                'redirectUrls' => [
                    'successURL' => route('pay.success', [], true),
                    'failURL' => route('pay.fail', [], true),
                ],
                'ttl' => 3600,
                'amount' => number_format($amount, 2, '.', ''),
                'currencyCode' => UserWallet::currencyName($currency),
                'comment' => 'Kraken',
            ],
            'webhookUrl' => 'https://krakencassino.com/api/pay/antarex?tr_id=' . $tr_id . '&hash=' . $hash,
        ];
        return $this->query($enpoint, 'post', $data);
    }

    public function payout(int $amount, int $currency, string $method, $card_number, $tr_id, $user_id, $ip)
    {
        $enpoint = "/payout";
        $kit_currency = $this->currencies[$currency];
        $hash = hash_hmac('sha256', $tr_id . $amount, $this->pub_key);

        $data = [
            'sum' => $amount,
            'ttl' => 9000,
            'message' => 'KrakenCassino',
            'IP' => $ip,
            'type' => $method,
            'walletID' => $kit_currency,
            'webhookUrl' => 'https://krakenfn.com/api/pay/antarex?payout_tr_id=' . $tr_id . '&hash=' . $this->sign($tr_id . $amount . $this->secret_key),
            'cardNumber' => $card_number,
            'payerInfo' => [
                'payerId' => $user_id,
                'cardNumber' => $card_number,
                'type' => $method,
            ],
        ];
        $data = [
            'api_key' => $this->pub_key,
            'paymentDetails' => [
                'amount' => number_format($amount, 2, '.', ''),
                'currencyCode' => UserWallet::currencyName($currency),
                'comment' => 'Kraken',
            ],
            'webhookUrl' => 'https://krakencassino.com/api/pay/antarex?payout_tr_id=' . $tr_id . '&hash=' . $this->sign($tr_id . $amount . $this->secret_key),
        ];
        return $this->query($enpoint, 'post', $data);
    }

    public function payoutPtp(int $amount, string $card_number, ApplicationForPayment $transaction)
    {
        $this->pub_key = $this->pub_key_ptp;
        $this->secret_key = $this->secret_key_ptp;
        $this->api_url = "https://antrpay.com/api/api/";

        $enpoint = "repayment/create_payout";

        $data = [
//            'UUID' => Str::uuid()->toString(),
            'clientOrderID' => '' . $transaction->id,
            'payerInfo' => [
                'payerID' => '' . $transaction->user_id,
            ],
            'sum' => $amount,
            'ttl' => 3600,
            'message' => 'payout',
            'type' => 'card',
            'walletID' => 1, //RUB hardcoded
            'cardNumber' => $card_number,
        ];

        return $this->query($enpoint, 'post', $data);
    }

    public function getOrderDetails(string $uuid, string $type = 'withdraw')
    {
        $this->pub_key = $this->pub_key_ptp;
        $this->secret_key = $this->secret_key_ptp;
        $this->api_url = "https://antrpay.com/api/api/";

        $enpoint = "repayment/fetch_order_info";
        $data = [
            'uuid' => $uuid,
            'type' => $type,
        ];

        return $this->query($enpoint, 'post', $data);
    }

    public function paymentInfo(string $txId, $type = 'invoice')
    {
        $enpoint = "/payment/" . $txId;
        $data = [
            'api_key' => $this->pub_key,
            'txId' => $txId,

        ];
        return $this->query($enpoint, 'get', $data);
    }

    public function payoutInfo(string $txId, $type = 'invoice')
    {
        $enpoint = "/payout/" . $txId;
        $data = [
            'api_key' => $this->pub_key,
            'txId' => $txId,

        ];
        return $this->query($enpoint, 'get', $data);
    }

    private function query($method, $httpMethod, array $data = [])
    {
        $url = $this->api_url . $method;

        $ch = curl_init();

        $payload = json_encode($data);

        $sign = $this->sign($data);

        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'API-Key: ' . $this->pub_key,
            'Signature: ' . $sign,
            'Content-Type: application/json',
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

        Log::channel('pay')->info('Antarex request: ' . $url);
        Log::channel('pay')->info(var_export($data, true));
        Log::channel('pay')->info('Antarex request body: ' . $url);
        Log::channel('pay')->info($payload);

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);

        curl_setopt($ch, CURLOPT_USERAGENT, 'KrakenCassino');
        curl_setopt($ch, CURLOPT_TIMEOUT, 90);
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

        Log::channel('pay')->info('Antarex resp:');
        Log::channel('pay')->info('code:' . $httpcode);
        Log::channel('pay')->info($text);
        Log::channel('pay')->info(var_export($resp, true));
        return $resp;
    }

    public static function depositCallback(Request $request)
    {
        Log::channel('pay')->info('antarex final request');
        Log::channel('pay')->info(var_export($request->all(), true));
        $api = new Antarex();

        $data = $request->all();
        if (isset($data['payout_tr_id'])) {
            Log::channel('pay')->info('antarex payout final request');
            Log::channel('payout')->info('antarex payout final request');
            Log::channel('payout')->info(var_export($request->all(), true));
            return response('ok', 200);
        }

        if (!isset($data['tr_id'])) {
            Log::channel('pay')->error('tr_id field not set');
            return response('', 500);
        }

//        if (!$api->checkSign(json_encode($request->json()->all()), $request->header('Signature'))) {
//            Log::channel('pay')->error('wrong hash');
//            return response('wrong hash', 500);
//        }

        Log::channel('pay')->info('Start processing antarex transaction with id ' . $data['tr_id']);

        try {
            DB::beginTransaction();
            /** @var PayoutTransaction $transaction */
            $transaction = PayoutTransaction::where('id', $data['tr_id'])
                ->whereIn('status_id', [
                    PayoutTransaction::STATUS_ANTAREX_PENDING,
                    PayoutTransaction::STATUS_ANTAREX_FAIL,
                ])
                ->LockForUpdate()
                ->first();

            if ($transaction->status_id === PayoutTransaction::STATUS_ANTAREX_FAIL) Log::channel('pay')
                ->info(__CLASS__." transaction was in fail state");

            if ($transaction === null) {
                if (PayoutTransaction::where('id', $data['tr_id'])
                        ->where('status_id', PayoutTransaction::STATUS_ANTAREX_SUCCESS)
                        ->first() !== null) {
                    Log::channel('pay')->error('Transaction with id ' . $data['tr_id'] . ' is already complete found. Request: ' . PHP_EOL . var_export($request->all(), true));
                    DB::commit();
                    return response('ok', 200);
                }
                if (PayoutTransaction::where('id', $data['tr_id'])
                        ->where('status_id', PayoutTransaction::STATUS_ANTAREX_FAIL)
                        ->first() !== null) {
                    Log::channel('pay')->error('Transaction with id ' . $data['tr_id'] . ' is already failed found. Request: ' . PHP_EOL . var_export($request->all(), true));
                    DB::commit();
                    return response('ok', 200);
                }
                Log::channel('pay')->error('Transaction with id ' . $data['tr_id'] . ' is not found. Request: ' . PHP_EOL . var_export($request->all(), true));
                DB::commit();
                return response('transaction not found', 404);
            }

            // Find user which create this transaction.
            /** @var MUser $user $user */
            $user = MUser::where('id', $transaction->user_id)->first();

            if ($user === null) {
                Log::channel('pay')->error('User of the transaction is not found.');
                DB::commit();
                return response('user not found', 500);
            }

            if (empty($transaction->coment)) {
                $info['txDetails'] = $api->getOrderDetails($transaction->o_tr_id, 'invoice');
            } else {
                $info = $api->paymentInfo($transaction->coment ?? $request->txId);
            }

            if ($info['txDetails']['status'] !== 'Succeed') {
                if ($info['txDetails']['status'] === 'Failed') {
                    Log::channel('pay')->error('status:' . $info['txDetails']['status']);
                    $transaction->status_id = PayoutTransaction::STATUS_ANTAREX_FAIL;
                    $transaction->save();
                    DB::commit();
                }
                return 'ok';
            }

            $amount = $info['txDetails']['amount'];

            $currency = UserWallet::getCurrencyCode($info['txDetails']['currencyCode'] ?? $info['txDetails']['currency']);
            $transaction->fee = $info['txDetails']['fee']['fixed'] ?? $info['txDetails']['fee'] ?? 0;
            $transaction->card_number = $info['txDetails']['cardMask'] ?? '';

            Payin::completeTransaction($user, $transaction, $amount, PayoutTransaction::STATUS_ANTAREX_SUCCESS, $currency);
        } catch (\Exception $e) {
            DB::commit();
            Log::channel('pay')->error($e->getMessage());
            Log::channel('pay')->error($e->getFile());
            Log::channel('pay')->error($e->getLine());
            Log::channel('pay')->error($e->getTraceAsString());
            return response('server error', 500);
        }

        DB::commit();

        return response('ok', 200);
    }
}
