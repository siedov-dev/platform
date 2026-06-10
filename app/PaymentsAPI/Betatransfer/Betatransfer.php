<?php

namespace App\PaymentsAPI\Betatransfer;

use App\Helpers\Currency;
use App\Models\PaySystemsConfig;
use App\MUser;
use App\Payin\Payin;
use App\PayoutTransaction;
use App\UserWallet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * @see https://merchant.betatransfer.io/ru/api/v-1
 */
final class Betatransfer
{
    private string $pub_key;
    private string $secret_key;
    private string $api_url = 'https://merchant.betatransfer.io/api';

    public function __construct()
    {
        $conf = PaySystemsConfig::getSystemConfig(Payin::TYPE_BETATRANSFER);
        $this->pub_key = $conf['pub_key'] ?? '065618a067917b151203943df72fe776';
        $this->secret_key = $conf['secret_key'] ?? 'ba6cd57d1d44056b753a71037a7f672a';
    }

    public function deposit(float $amount, int $currency, string $tr_id, string $system)
    {
        $curr_name = UserWallet::currencyName($currency);
//        if ($currency === UserWallet::CURRENCY_KZT) {
//            $amount = round(Currency::convert($amount, 'KZT', 'USD'), 2);
//            $curr_name = 'USD';
//        }
        if ($currency === UserWallet::CURRENCY_USDT) {
            $curr_name = 'USD';
        }
        $endpoint = '/payment';
        $payload = [
            'token' => $this->pub_key,
            'amount' => number_format($amount, 2, '.', ''),
            'currency' => $curr_name,
            'orderId' => $tr_id,
            'paymentSystem' => $system,
            'urlResult' => 'https://krakenfn.com/api/pay/betatransfer',
            'urlSuccess' => route('pay.success', [], true),
            'urlFail' => route('pay.fail', [], true),
            'locale' => 'ru',
            'redirect' => 0,
            'fullCallback' => 1,
        ];

        return $this->query($endpoint, 'post', $payload);
    }

    public function withdraw(float $amount, int $currency, string $tr_id, string $system, $card_number)
    {
        $endpoint = '/withdrawal-payment';
        $curr_name = UserWallet::currencyName($currency);
//        if ($currency === UserWallet::CURRENCY_KZT) {
//            $amount = round(Currency::convert($amount, 'KZT', 'USD'), 2);
//            $curr_name = 'USD';
//        }

//        $payload = [
//            'token' => $this->pub_key,
//            'amount' => number_format($amount, 2, '.', ''),
//            'currency' => $curr_name,
//            'orderId' => $tr_id,
//            'paymentSystem' => $system,
//            'address' => $card_number,
//        ];
        $payload = [
            'token' => $this->pub_key,
            'paymentAmount' => number_format($amount, 2, '.', ''),
            'paymentCurrency' => $curr_name,
            'orderId' => $tr_id,
            'paymentSystem' => $system,
            'address' => $card_number,
        ];

        return $this->query($endpoint, 'post', $payload);
    }

    public function info(string $id)
    {
        $endpoint = '/info';
        $payload = [
            'id' => $id,
        ];
        return $this->query($endpoint, 'post', $payload);
    }

    public function accountInfo()
    {
        $endpoint = '/account-info';
        $payload = [];
        return $this->query($endpoint, 'get', $payload);
    }

    public function sign(array $payload)
    {
        return md5(implode('', $payload) . $this->secret_key);
    }

    private function query($method, $httpMethod, array $data = [])
    {
        $url = $this->api_url . $method . '?' . 'token=' . $this->pub_key;

        $ch = curl_init();


        curl_setopt($ch, CURLOPT_HTTPHEADER, [
        ]);

        if (strtolower($httpMethod) === 'post') {
            $sign = $this->sign($data);
            $data['sign'] = $sign;
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));

        } else {
            $data['sign'] = md5($this->pub_key . $this->secret_key);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
            if (!empty($data)) {
                $url .= '&' . http_build_query($data);
            }
        }

        Log::channel('pay')->info('Betastransfer request: ' . $url);
        Log::channel('pay')->info(var_export($data, true));

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
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

//        Log::channel('pay')->info('curl verbose');
//        Log::channel('pay')->info(htmlspecialchars($verboseLog));

        if ($err) {
            Log::channel('pay')->error($err);
        }

        Log::channel('pay')->info('Betastransfer resp:');
        Log::channel('pay')->info('code:' . $httpcode);
        Log::channel('pay')->info($text);
        Log::channel('pay')->info(var_export($resp, true));
        return $resp;
    }

    public static function depositCallback(Request $request)
    {
        Log::channel('pay')->info(__CLASS__ . " callback:", $request->all());
        Log::channel('pay')->info(__CLASS__ . " callback raw:". var_export($request->getContent(), true));
        $data = $request->all();
        Log::channel('pay')->info(__CLASS__ . " callback data:", $data);

        $targetUrl = "http://127.0.0.1:23106/pay/betatransfer";
        $response = Http::withHeaders($request->headers->all())
            ->withBody($request->getContent(), $request->header('Content-Type'))
            ->send($request->method(), $targetUrl);
        Log::channel('pay')->info('betatransfer callback response ' . var_export($response->body(), true));
        return response($response->body(), $response->status())->withHeaders($response->headers());

//        Log::channel('pay')->info('Start processing betatransfer ' . PHP_EOL . var_export($request->all(), true));
//        $trans_id = $request->get('orderId');
//        if (!$trans_id) {
//            Log::channel('pay')->error('orderId field not set');
//            return response('Платеж не найден', 500);
//        }
//
//        $data = $request->all();
//
//        $currency = UserWallet::getCurrencyCode($data['currency']);
//        if ($currency === UserWallet::CURRENCY_USD) $currency = UserWallet::CURRENCY_USDT;
//
//        $api = new Betatransfer();
//        $sign = $data['sign'];
//
////        if (!$api->checkSign($data, $sign)) {
////            Log::channel('pay')->error('Wrong sign, possible hack');
////            return response('', 500);
////        }
//
//        DB::beginTransaction();
//
//        // Find transaction.
//        /** @var PayoutTransaction $transaction */
//        $transaction = PayoutTransaction::where('id', $trans_id)
//            ->whereIn('status_id', [
//                PayoutTransaction::STATUS_BETATRANSFER_PENDING,
//                PayoutTransaction::STATUS_BETATRANSFER_FAIL,
//            ])
//            ->lockForUpdate()
//            ->first();
//
//        if ($transaction->status_id === PayoutTransaction::STATUS_BETATRANSFER_FAIL) Log::channel('pay')
//            ->info(__CLASS__ . " transaction was in fail state");
//
//        if ($transaction === null) {
//            $transaction = PayoutTransaction::where('id', $trans_id)
//                ->where('status_id', PayoutTransaction::STATUS_BETATRANSFER_SUCCESS)
//                ->first();
//            if ($transaction !== null) {
//                Log::channel('pay')->error('Transaction with id ' . $trans_id . ' already completed. Request: ' . PHP_EOL . var_export($request->all(), true));
//                DB::commit();
//                return response('OK', 200);
//            }
//            Log::channel('pay')->error('Transaction with id ' . $trans_id . ' is not found. Request: ' . PHP_EOL . var_export($request->all(), true));
//            DB::commit();
//            return response('OK', 500);
//        }
//
//        if ($data['status'] !== 'success' && $data['status'] !== 'partial_payment') {
//            $transaction->status_id = PayoutTransaction::STATUS_BETATRANSFER_FAIL;
//            $transaction->save();
//            DB::commit();
//            return response('OK', 200);
//        }
//
//        // Find user which create this transaction.
//        /** @var MUser $user $user */
//        $user = MUser::where('id', $transaction->user_id)->first();
//
//        if ($user === null) {
//            Log::channel('pay')->error('User of the transaction is not found.');
//            DB::commit();
//            return response('User not found.', 500);
//        }
//
//        $amount = $data['paidAmount'];
//        $fee = $data['commission'] ?? 0;
//
//        if ($data['currency'] !== $transaction->currency) {
//            Log::channel('pay')->info('converting currency from ' . $data['currency'] . ' to ' . $transaction->currency);
//            $currency = UserWallet::getCurrencyCode($transaction->currency);
//            $amount = round(Currency::convert($amount, $data['currency'], $transaction->currency), 2);
//            $fee = round(Currency::convert($fee, $data['currency'], $transaction->currency), 2);
//            $diff_prc = $amount / $transaction->sum;
//            if ($diff_prc < 1 && $diff_prc >= 0.97) $amount = $transaction->sum;
//        }
//
//        $transaction->sum = $amount;
//        $transaction->fee = round($fee, 2);
//        $transaction->total_sum = $amount + $fee;
//
//        try {
//            Payin::completeTransaction($user, $transaction, $amount, PayoutTransaction::STATUS_BETATRANSFER_SUCCESS, $currency);
//        } catch (\Exception $e) {
//            DB::rollBack();
//            Log::channel('pay')->error($e->getMessage());
//        }
//
//        DB::commit();
//
//        return response('OK', 200);
    }
}
