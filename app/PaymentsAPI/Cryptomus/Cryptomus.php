<?php

namespace App\PaymentsAPI\Cryptomus;

use App\ApplicationForPayment;
use App\Helpers\Currency;
use App\Models\PaySystemsConfig;
use App\Models\UserNotifications;
use App\MUser;
use App\Payin\Payin;
use App\PayoutTransaction;
use App\UserWallet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * @url https://doc.cryptomus.com
 */
final class Cryptomus
{
    private string $api_url = "https://api.cryptomus.com";
    private string $api_key = "SwOxoJ8I5KynjvDg4XmIKR3qCbtykw17o6XrNISiFdcYpyYkd9LMnZmabwqctHso0XQi2yV0YqdsIDfETVUftniVK8T81aZsFmeHGl22tJiY3ykrREpBo7pqXIYhKQKR";
    private string $merchant_uuid = "a6492892-b5ec-4815-a667-74d132991fbd";
    private string $payout_key = "Oghyk7cgYCVDj2Hqu18qIP3QYcFzF11c0AXtsWv5pojpJ3S0V5Ple3a9ggqFlfTij3R4c24DdXitlv72kqBxbGYt8InhvxvNSXYpHRQ3qVuALPCzjdMvegRidbOIHPoD";

    public function __construct()
    {
        $conf = PaySystemsConfig::getSystemConfig(Payin::TYPE_CRYPTOMUS);
        $this->api_key = $conf['key'];
        $this->merchant_uuid = $conf['merchant_uuid'];
        $this->payout_key = $conf['payout_key'];
    }

    public function services()
    {
        $ep = "/v1/payment/services";
        $payload = [
        ];

        return $this->query($ep, 'post', $payload);
    }

    public function balance()
    {
        $ep = "/v1/balance";
        $payload = [
        ];

        return $this->query($ep, 'post', $payload);
    }

    public function deposit(string $amount, int $currency_id, string $order_id, $network)
    {
        $currency_name = UserWallet::currencyName($currency_id);
        if ($currency_name === 'mBTC') {
            $currency_name = 'BTC';
//            $amount = '' . number_format($amount / 1000, 6, '.', '');
        }
        $ep = "/v1/payment";
        $payload = [
            'amount' => $amount,
            'currency' => $currency_name,
            'network' => $network,
            'order_id' => $order_id,
            'url_return' => route('pay.close-tab'),
            'url_callback' => 'https://krakencassino.com/api/pay/cryptomus',
//            'to_currency' => UserWallet::currencyName($currency_id),
            'subtract' => 0,
        ];

        return $this->query($ep, 'post', $payload);
    }

    public function depositFiat(string $amount, string $currency_name, string $order_id, $currency_pay)
    {
        if ($currency_name === 'mBTC') {
            $currency_name = 'BTC';
//            $amount = '' . number_format($amount / 1000, 6, '.', '');
        }
        $ep = "/v1/payment";
        $payload = [
            'amount' => $amount,
            'currency' => $currency_name,
            'order_id' => $order_id,
            'url_return' => route('pay.close-tab'),
            'url_callback' => 'https://krakencassino.com/api/pay/cryptomus',
//            'to_currency' => $currency_pay,
            'subtract' => 0,
        ];

        return $this->query($ep, 'post', $payload);
    }

    public function depositInfo(string $tr_id)
    {
        $ep = "/v1/payment/info";
        $payload = [
            'order_id' => $tr_id,
        ];

        return $this->query($ep, 'post', $payload);
    }

    public function payout(string $amount, int $currency_id, string $order_id, $network, $address)
    {
        $ep = "/v1/payout";
        $payload = [
            'amount' => $amount,
            'currency' => UserWallet::currencyName($currency_id),
            'network' => $network,
            'order_id' => $order_id,
            'address' => $address,
//            'url_return' => route('landing'),
//            'url_callback' => 'https://krakencassino.com/api/pay/cryptomus',
            'is_subtract' => 1,
        ];
        return $this->query($ep, 'post', $payload, true);
    }

    public function payoutInfo(string $tr_id)
    {
        $ep = " /v1/payout/info";
        $payload = [
            'order_id' => $tr_id,
        ];

        return $this->query($ep, 'post', $payload, true);
    }

    public function checkSign(array $data)
    {
        $sign = $data['sign']?? '';
        unset($data['sign']);
        return $sign === $this->sign($data);
    }

    private function sign(array $data): string
    {
        $json = json_encode($data);
        return md5(base64_encode($json) . $this->api_key);
    }

    private function signPayout(array $data): string
    {
        $json = json_encode($data);
        return md5(base64_encode($json) . $this->payout_key);
    }

    private function query($method, $httpMethod, array $data = [], $is_payout = false)
    {
        $url = $this->api_url . $method;

        $ch = curl_init();

        $sign = $is_payout? $this->signPayout($data) : $this->sign($data);

        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'merchant: ' . $this->merchant_uuid,
            'Content-Type: application/json',
            'sign: ' . $sign,
        ]);

        if ( strtolower($httpMethod) === 'post') {

            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

        } else {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
            if (!empty($data)) {
                $url .= '?' . http_build_query($data);
            }
        }

        Log::channel('pay')->info('cryptomus request: '.$url);
        Log::channel('pay')->info(json_encode($data));
        Log::channel('pay')->info(var_export($data, true));

        curl_setopt($ch, CURLOPT_HEADER, false);

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);

        curl_setopt($ch, CURLOPT_USERAGENT, 'KrakenCassino');
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_MAXREDIRS, 10);

        $text = curl_exec($ch);
        $err = curl_error($ch);
        $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $resp = json_decode($text, true);
        if ($err) {
            Log::channel('pay')->error($err);
        }

        Log::channel('pay')->info('cryptomus resp:');
        Log::channel('pay')->info('code:'.$httpcode);
        Log::channel('pay')->info($text);
        Log::channel('pay')->info(var_export($resp, true));
        return $resp;
    }

    public static function cryptomusCallback(Request $request)
    {
        Log::channel('pay')->info('cryptomus pay callback');
        Log::channel('pay')->info(var_export($request->all(), true));

        $data = $request->all();
        $api = new Cryptomus();

        if ($data['is_final'] !== true) return response('ok', 200);

        if ($data['type'] === 'payout') {
            Log::channel('pay')->info('Start processing payout cryptomus transaction with id ' . $data['order_id']);

            DB::beginTransaction();
            /** @var ApplicationForPayment $applicationForPayment */
            $applicationForPayment = ApplicationForPayment::where('id', $data['order_id'])
                ->where('status_id', ApplicationForPayment::STATUS_AUTO_PAY_PENDING)
                ->lockForUpdate()
                ->first();

            if ($applicationForPayment === null) {
                Log::channel('pay')->error('ApplicationForPayment with id ' . $data['order_id'] . ' is not found. Request: ' . PHP_EOL . var_export($request->all(), true));
                DB::commit();
                return response('transaction not found', 404);
            }

            if ($data['status'] !== 'paid') {
                $applicationForPayment->confirmed = ApplicationForPayment::STATUS_AUTO_PAY_ERROR;
                $applicationForPayment->error = $data['status'] ?? 'unknown error';
                $applicationForPayment->save();
            } else {

                $applicationForPayment->confirmed = ApplicationForPayment::STATUS_AUTO_PAY_SUCCESS;
                $applicationForPayment->comentar = "Завершено успешно";
                $applicationForPayment->suma = $data['amount'];
                $applicationForPayment->save();
                $fee = $data['commission'];

                DB::table('payout_transactions')
                    ->insert([
                        'user_id' => $applicationForPayment->user_id,
                        'hall_id' => $applicationForPayment->hall_id,
                        'date' => date('Y-m-d H:i:s'),
                        'currency' => UserWallet::currencyName($applicationForPayment->currency),
                        'operation' => 'Автовыплата Cryptomus',
                        'sum' => $applicationForPayment->money,
                        'fee' => $fee,
                        'total_sum' => $applicationForPayment->money + $fee,
                        'status_id' => '45',
                    ]);

                if ($applicationForPayment->user_id) {
                    UserNotifications::notify($applicationForPayment->user_id, __('notify.title_payout'),
                        '<span class="gold-text">' . $applicationForPayment->money . ' ' . UserWallet::currencyName($applicationForPayment->currency) . '</span> ' . __('notify.payout_success'));
                }
            }

            DB::commit();

            return response('ok', 200);
        }


        if ($data['type'] !== 'payment') return response('ok', 200);

        if (!$api->checkSign($data)) {
            Log::channel('pay')->error('wrong hash');
//            return response('wrong hash', 500);
        }
        Log::channel('pay')->info('Start processing cryptomus transaction with id ' . $data['order_id']);

        // Find transaction.
        /** @var PayoutTransaction $transaction */
        $transaction = PayoutTransaction::where('id', $data['order_id'])
            ->where('status_id', PayoutTransaction::STATUS_CRYPTOMUS_PENDING)
            ->first();

        if ($transaction === null) {
            if (PayoutTransaction::where('id', $data['order_id'])
                    ->where('status_id', PayoutTransaction::STATUS_CRYPTOMUS_SUCCESS)
                    ->first() !== null) {
                Log::channel('pay')->error('Transaction with id ' . $data['order_id'] . ' is already complete. Request: ' . PHP_EOL . var_export($request->all(), true));
                return response('ok', 200);
            }
            Log::channel('pay')->error('Transaction with id ' . $data['order_id'] . ' is not found. Request: ' . PHP_EOL . var_export($request->all(), true));
            return response('transaction not found', 404);
        }

        // Find user which create this transaction.
        /** @var MUser $user $user */
        $user = MUser::where('id', $transaction->user_id)->first();

        if ($user === null) {
            Log::channel('pay')->error('User of the transaction is not found.');
            return response('user not found', 500);
        }

        if ($data['status'] !== 'paid' && $data['status'] !== 'paid_over' && $data['status'] !== 'wrong_amount') {
            $transaction->status_id = PayoutTransaction::STATUS_CRYPTOMUS_FAIL;
            $transaction->save();
            Log::channel('pay')->error('Error status '.$data['status']);
            return response('ok', 200);
        }

        $amount = $data['merchant_amount'];
//        $amount = $data['payment_amount'];
        $currency_name = $data['currency'];
        $fee = $data['merchant_amount'] - $data['payment_amount'];
//        $fee = round(Currency::convert($fee, $data['payer_currency']??$transaction->currency, $transaction->currency), 2);
//        if (mb_strtoupper($currency_name) === 'BTC') {
//            $currency_name = 'mBTC';
//            $amount = $amount * 1000;
//            $fee = $fee * 1000;
//        }

        //convert currency
        if ($currency_name !== $transaction->currency && $currency_name !== 'mBTC') {
            $amount = round(Currency::convert($amount, $currency_name, $transaction->currency), 2);
            $diff_prc = $amount / $transaction->sum;
            if ($diff_prc < 1 && $diff_prc >= 0.97) $amount = $transaction->sum;
        }

        $currency = UserWallet::getCurrencyCode($currency_name);

        $transaction->fee = $fee;

        Payin::completeTransaction($user, $transaction, $amount, PayoutTransaction::STATUS_CRYPTOMUS_SUCCESS, $currency);

        return response('ok', 200);
    }

}
