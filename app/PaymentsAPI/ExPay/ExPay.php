<?php

namespace App\PaymentsAPI\ExPay;

use App\ApplicationForPayment;
use App\Helpers\Hlog;
use App\Helpers\Hosts;
use App\Models\PaymentSystems;
use App\Models\UserCryptoWallet;
use App\Models\UserCurrency;
use App\MUser;
use App\Payin\Payin;
use App\PaymentsAPI\ApiBase;
use App\PayoutTransaction;
use App\UserWallet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * @docs https://docs.expay.cash/
 */
final class ExPay extends ApiBase
{

    protected string $api_url = 'https://apiv2.expay.cash/api';
    protected string $callback_url = '';
    private string $pub_key = "3wihuee9mt6r4i5swq3lv1ydxbft4uae1xqeyz79p7k5are3eamjcql1rq3mlv8k";
    private string $private_key = '7xx062k54oecubuqwedce46k99ehgo8dasjxm8i8oqazew876k7vhwy3u9ogqab259w7fawqyvfouy09ip8nkg6qhrvivwrt6acapma6hga2suwba8p3yrx5wef4jzjm';
    private string $pub_fiat_key = "3kejhywnao7o8gdh71bp2870c9wrzolbsvc4k2ciorc4ntns1osupexv37jswpbo";
    private string $private_fiat_key = 'sf1u3hqq0nl7o28fhrm0tgyvhfm5rmo3enwummsnvrerllra4azf4us4f52cyqnyisb289isrcbu4wqer8p9xr65vpn37hgzoof8al25bijyggn3u7mfw5ixuz9q1mjj';

    public int $system_id = 45;
    public const system_code = 'ExPay';

    public function __construct()
    {
        parent::__construct();
        $this->callback_url = Hosts::getFinHost('RUB'). '/api/pay/expay';
    }

    protected array $crypto_codes = [
        'BTC',
        'USDTTRC',
        'USDTERC',
        'USDTBSC',
        'USDTPOLY',
        'ETH',
        'TRX',
        'BNB',
        'MATIC',
        'TON',
        'NOT',
        'USDTTON',
        'AVAX',
        'DAIERC',
        'DAIPOLY',
        'BTCBBSC',
        'USDCERC',
        'USDCPOLY',
        'BCH',
        'LTC',
        'DASH',
        'DOGE',
        'DOGEBSC',
        'SHIBBSC'
    ];

    protected function request(string $uri, array $params = [], bool $post = true): object|bool|array
    {
        $this->http = Http::acceptJson();

        $timestamp = time();

        $this->headers = [
            'ApiPublic' => $this->pub_key,
            'Signature' => $this->sign($params, $timestamp, !$post),
            'TimeStamp' => $timestamp,
        ];

        return parent::request(
            uri: $uri,
            params: $params,
            post: $post,
        );
    }

    public function balance(): object|bool|array
    {
        return $this->request(
            uri: '/token/balance',
            params: [
                'token' => 'USDTTRC',
            ],
        );
    }

    public function p2p(PayoutTransaction $transaction, float $amount, string $token, string $sub_token = null): array|object|bool
    {
        $data = [
            'refer_type' => 'p2p_payform',
            'token' => $token,
            'amount' => $amount,
            'client_transaction_id' => (string)$transaction->id,
            'client_merchant_id' => (string)$transaction->system_id,
            'fingerprint' => (string)$transaction->user_id,
            'call_back_url' => Hosts::getFinHost('RUB'). '/api/pay/expay',
            'alter_refer_custom_options' => [
                'redirect_url' => route('pay.close-tab'),
            ]
        ];

        if ($sub_token) $data['sub_token'] = $sub_token;

        return $this->query(
            method: '/transaction/create/in',
            httpMethod: 'post',
            data: $data,
        );
    }

    public function p2pFiat(PayoutTransaction $transaction, float $amount, string $token, string $sub_token = null): array|object|bool
    {
        $this->pub_key = $this->pub_fiat_key;
        $this->private_key = $this->private_fiat_key;

        $data = [
            'refer_type' => 'p2p_payform',
            'token' => $token,
            'amount' => $amount,
            'client_transaction_id' => (string)$transaction->id,
            'client_merchant_id' => (string)$transaction->system_id,
            'fingerprint' => (string)$transaction->user_id,
            'call_back_url' => Hosts::getFinHost('RUB'). '/api/pay/expay',
            'alter_refer_custom_options' => [
                'redirect_url' => route('pay.close-tab'),
            ]
        ];

        if ($sub_token) $data['sub_token'] = $sub_token;

        return $this->query(
            method: '/transaction/create/in',
            httpMethod: 'post',
            data: $data,
        );
    }

    public function deposit(PayoutTransaction $transaction, float $amount, string $token, string $sub_token)
    {
        $method = '/transaction/create/in';

        $data = [
            'refer_type' => 'p2p_payform',
            'token' => $token,
            'sub_token' => $sub_token,
            'amount' => $amount,
            'client_transaction_id' => (string)$transaction->getId(),
            'fingerprint' => (string)$transaction->user_id,
            'call_back_url' => Hosts::getFinHost('RUB'). '/api/pay/expay',
            'alter_refer_custom_options' => [
                'redirect_url' => route('pay.close-tab'),
            ],
        ];

        return $this->query($method, 'post', $data);
    }

    public function withdraw(ApplicationForPayment $transaction, float $amount, string $token, string $sub_token, array $requisites = [])
    {
        $method = '/transaction/create/out';

        $data = [
            'token' => $token,
            'amount' => $amount,
            'receiver' => $requisites['receiver'] ?? $requisites['cardNumber'],
            'client_transaction_id' => (string)$transaction->getId(),
//            'call_back_url' => 'https://krakenfn.com/api/pay/expay',
//            'alter_refer_custom_options' => [
//                'redirect_url' => route('pay.success'),
//            ],
        ];

        if (!empty($sub_token)) $data['sub_token'] = $sub_token;

        if (!empty($requisites['extra_info'])) $data['extra_info'] = $requisites['extra_info'];

        return $this->query($method, 'post', $data);
    }

    public function crypto_out(ApplicationForPayment $transaction, float $amount, string $token): object|bool|array
    {
        return $this->request(
            uri: '/transaction/create/out',
            params: [
                'token' => $token,
                'amount' => $amount,
                'client_transaction_id' => (string)$transaction->id,
                'receiver' => $transaction->card_number,
            ]
        );
    }

    public function getTransactionInfo(string $tracker_id, bool $fiat = false)
    {
        if ($fiat === true) {
            $this->pub_key = $this->pub_fiat_key;
            $this->private_key = $this->private_fiat_key;
        }
        $method = "/transaction/get";

        $data = [
            'tracker_id' => $tracker_id,
        ];

        return $this->query($method, 'post', $data);
    }

    public function course(string $from, string $to = ''): object|string|array
    {
        return $this->request(
            uri: '/token/fetch/course',
            params: [
                'from' => [$from],
                'to' => $to
            ],
        );
    }

    public function get_invoice(string $id): object|bool|array
    {
        return $this->request(
            uri: '/crypto/invoice/get',
            params: [
                'tracker_id' => $id,
            ],
            post: false
        );
    }

    public function createCryptoWallet(string $token, string $fiat_currency, string $client_transaction_id): object|bool
    {
        return $this->request(
            uri: '/crypto/invoice/create',
            params: [
                'token' => $token,
                'fiat_currency' => $fiat_currency,
                'client_transaction_id' => $client_transaction_id,
                'static' => true,
                'call_back_url' => $this->callback_url,
            ]
        );
    }

    private function sign(array|string $params, int $timestamp, bool $empty_body = false): string
    {
        return hash_hmac('sha512', ($timestamp . (!$empty_body ? json_encode($params) : '')), $this->private_key);
    }

    private function sign_fiat(array|string $params, int $timestamp, bool $empty_body = false): string
    {
        return hash_hmac('sha512', ($timestamp . (!$empty_body ? json_encode($params) : '')), $this->private_fiat_key);
    }

    private function sign_old(string $body, int $timestamp): string
    {
        return hash_hmac('sha512', $timestamp . $body, $this->private_key);
    }

    private function query(string $method, string $httpMethod, array $data = [])
    {
        $url = $this->api_url . $method;

        $ch = curl_init();

        $payload = json_encode($data, JSON_UNESCAPED_SLASHES);
        $timestamp = time();

        $sign = $this->sign_old($payload, $timestamp);

        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'ApiPublic: ' . $this->pub_key,
            'Signature: ' . $sign,
            'TimeStamp: ' . $timestamp,
            'Content-Type: ' . 'application/json',
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

        curl_setopt($ch, CURLOPT_USERAGENT, 'KrakenCasino');
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
        //$verboseLog = stream_get_contents($verbose);

        //Log::channel('pay')->info('curl verbose');
        //Log::channel('pay')->info(htmlspecialchars($verboseLog));

        if ($err) {
            Log::channel('pay')->error($err);
        }

        Log::channel('pay')->info(__CLASS__ . ' resp:');
        Log::channel('pay')->info('code:' . $httpcode);
        Log::channel('pay')->info($json);
        Log::channel('pay')->info(var_export($resp, true));
        return $resp;
    }

    public static function depositCallback(Request $request)
    {
        Log::channel('pay')->info(__CLASS__ . ' callback deposit request: ' . $request->fullUrl());
        Log::channel('pay')->info('getContent: ' . var_export($request->getContent(), true));
        Log::channel('pay')->info('all: ' . var_export($request->all(), true));

        $targetUrl = "http://127.0.0.1:23106/pay/expay";
        $response = Http::withHeaders($request->headers->all())
            ->withBody($request->getContent(), $request->header('Content-Type'))
            ->send($request->method(), $targetUrl);
        Log::channel('pay')->info('expay callback response ' . var_export($response->body(), true));
        return response($response->body(), $response->status())->withHeaders($response->headers());

//        $data = $request->all();
//
//        $api = new self;
//
//        $client_transaction_id = $request->client_transaction_id;
//        $tracker_id = $request->tracker_id;
//
//        if (empty($transaction_id = $request->client_transaction_id)) {
//            Log::channel('pay')->error('client_transaction_id field not set');
//            return response('client_transaction_id field not set', 400);
//        }
//
//        if (!empty($request->client_transaction_id) && !empty($request->tracker_id)) {
//            $transaction_id = $request->tracker_id;
//        }
//
//        Log::channel('pay')->info('Start processing ExPay transaction with id ' . $transaction_id);
//
//        try {
//            DB::beginTransaction();
//
//            $transaction = PayoutTransaction::where('o_tr_id', $transaction_id)->lockForUpdate()->first();
//
//            if (!$transaction) {
//                Log::channel('pay')->info('Unknown transaction with id ' . $transaction_id . '.');
//
//                $transaction_api = $api->get_invoice($transaction_id);
//
//                if ($transaction_api->status == 'SUCCESS') {
//
//                    $user_crypto_wallet = UserCryptoWallet::where('service_user_id', $transaction_api->client_transaction_id)->first();
//
//                    if (!$user_crypto_wallet) {
//                        Log::channel('pay')->error('wallet not found for transaction with id ' . $transaction_id . '.');
//                        return response('', 500);
//                    }
//
//                    $user = MUser::where('id', $user_crypto_wallet->user_id)->first();
//
//                    if (!$user) {
//                        Log::channel('pay')->error('user not found for transaction with id ' . $transaction_id . '.');
//                        return response('', 500);
//                    }
//
//                    $currency = UserCurrency::where('name', $transaction_api->fiat_currency)->first();
//                    if (!$currency) {
//                        Log::channel('pay')->error('currency not found for transaction with id ' . $transaction_id . '.');
//                        return response('', 500);
//                    }
//
//                    $amount = $transaction_api->fiat_payed_amount;
//                    $fee = $amount * 0.025;
//
//                    $transaction = new PayoutTransaction();
//                    $transaction->system_id = PayIn::TYPE_EXPAY;
//                    $transaction->method = 'ExPay.' . $transaction_api->token.'_'.$currency->name;
//                    $transaction->user_id = $user->id;
//                    $transaction->hall_id = $user->hall_id;
//                    $transaction->date = date("Y-m-d H:i:s");
//                    $transaction->sum = $amount;
//                    $transaction->fee = $fee;
//                    $transaction->currency = $currency->name;
//                    $transaction->operation = 'Пополнение счета через ExPay';
//                    $transaction->status_id = PayoutTransaction::STATUS_EXPAY_SUCCESS;
//                    $transaction->o_tr_id = $transaction_id;
//                    $transaction->bonus_id = 1;
//                    $transaction->save();
//
//                    Payin::completeTransaction($user, $transaction, $amount, PayoutTransaction::STATUS_EXPAY_SUCCESS, $currency->id);
//
//                    return response('ok', 200);
//
//                } else {
//                    Log::channel('pay')->error('Waiting status SUCCESS.');
//                    return response('watch', 200);
//                }
//
//            }
//
//            if (!$transaction) {
//                Log::channel('pay')->error('Transaction with id ' . $transaction_id . ' is not found.');
//                return response('transaction not found', 404);
//            }
//
//            //check transaction completed
//            if ($transaction->status_id == PayoutTransaction::STATUS_EXPAY_SUCCESS) {
//                Log::channel('pay')->info('Transaction with id ' . $transaction_id . ' is already complete found. Request: ' . PHP_EOL . var_export($request->all(), true));
//                return response('ok', 200);
//            }
//
//            $status = $api->getTransactionInfo($transaction_id);
//
//            if (empty($status)) {
//                Log::channel('pay')->error('Check status error: ' . var_export($status, true));
//                return response('Check status error', 500);
//            }
//
//            if (!isset($status['transaction'])) {
//                $status = $api->getTransactionInfo($transaction_id, true);
//                if (!isset($status['transaction'])) {
//                    Log::channel('pay')->error('$status[transaction] empty');
//                    return response('', 500);
//                }
//            }
//
//            //usdt crypto transaction
//            if (preg_match('#^ccw(\d+):.*$#', $status['transaction']['client_transaction_id'], $matches)) {
//                Log::channel('pay')->info('Start processing ExPay crypto transaction with id ' . $status['transaction']['client_transaction_id']);
//
//                if ($status['transaction']['status'] !== 'SUCCESS') {
//                    Log::channel('pay')->info('Start processing status ' . $status['transaction']['status']);
//                    return response('ok', 200);
//                }
//
//                /** @var MUser $user $user */
//                $user = MUser::where('id', $matches[1])->first();
//                $crypto_wallet = UserCryptoWallet::getForUser(user_id: $user->getId(), system_id: 'ExPay', pay_type: PaymentSystems::TYPE_PAYIN, currency: 'USDT');
//                Log::channel('pay')->info('ExPay user wallet :' . $crypto_wallet->getAddress());
//
//                $currency_name = $status['transaction']['token_major_name'] ?? 'USDT';
//                $currency = UserWallet::getCurrencyCode($currency_name);
//                $amount = $status['transaction']['amount'];
//
//                $transaction = new PayoutTransaction();
//                $transaction->user_id = $user->id;
//                $transaction->hall_id = $user->hall_id;
//                $transaction->date = date("Y-m-d H:i:s");
//                $transaction->sum = $transaction->total_sum = $amount;
//                $transaction->fee = $status['transaction']['transaction_commission'] ?? 0;
//                $transaction->currency = $user->getWallet($currency)->getCurrencyName();
//                $transaction->operation = 'Пополнение счета через ExPay';
//                $transaction->status_id = PayoutTransaction::STATUS_EXPAY_SUCCESS;
//                $transaction->o_tr_id = $transaction_id;
//                $transaction->save();
//
//                Payin::completeTransaction($user, $transaction, $amount, PayoutTransaction::STATUS_EXPAY_SUCCESS, $currency);
//
//                return response('ok', 200);
//            }
//
//            if ($transaction->status_id === PayoutTransaction::STATUS_EXPAY_SUCCESS) {
//                Log::channel('pay')->error('Transaction with id ' . $transaction_id . ' status success.');
//                return response('transaction done', 410);
//            } else if ($transaction->status_id === PayoutTransaction::STATUS_EXPAY_FAIL) {
//                Log::channel('pay')->info(__CLASS__ . " transaction was in fail state");
//            }
//
//            // Find user which create this transaction.
//            /** @var MUser $user $user */
//            $user = MUser::where('id', $transaction->user_id)->first();
//
//            if ($user === null) {
//                Log::channel('pay')->error('User of the transaction is not found.');
//                return response('user not found', 500);
//            }
//
//            if (!isset($status['transaction']['status'])) {
//                Log::channel('pay')->error('$status[transaction][status] empty ' . var_export($status, true));
//                return response('', 500);
//            }
//
//
//            if ($status['transaction']['status'] === 'ERROR') {
//                Log::channel('pay')->error('status:', $status);
//                $transaction->status_id = PayoutTransaction::STATUS_EXPAY_FAIL;
//                $transaction->coment = $status['transaction']['status'] ?? '/';
//                $transaction->save();
//                return response('ok', 200);
//            }
//
//            if ($status['transaction']['status'] !== 'SUCCESS') {
//                return response('ok', 200);
//            }
//
//            $amount = $status['transaction']['amount'];
//
//            $currency = UserWallet::getCurrencyCode($transaction->currency);
//            $transaction->fee = $amount * 0.07;
//
//            Payin::completeTransaction($user, $transaction, $amount, PayoutTransaction::STATUS_EXPAY_SUCCESS, $currency);
//        } catch (\Throwable $e) {
//            Hlog::logException($e, 'pay');
//        } finally {
//            DB::commit();
//        }
//
//        return response('ok', 200);
    }

}
