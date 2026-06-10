<?php

namespace App\PaymentsAPI\Exotic;

use App\ApplicationForPayment;
use App\Helpers\Hosts;
use App\MUser;
use App\Payin\Payin;
use App\PayoutTransaction;
use App\UserWallet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

final class ExoticApi
{
//    private string $api_key = "vzg2_ngndxopamc7o4foctj_ald7luh8k_kg14llq8_ku0z8";
    private string $api_key = "qljzjyqg0ki8t_ggjwskw7yq_o2zu9fwa3f9ks9uprsl6rte";
//    private string $sign_key = "q422ykkkmnt0hfixkgw1k2a6ct__k4aq5i3zcicywpg22xmu";
    private string $sign_key = "z3m5z2d__exl5unp30ipbhkcpseenny20szd7gibbdv9d35q";
//    private string $url = "https://test.exotic.company";
    private string $url = "https://exotic.company";
    private string $merchant = "Kraken";

    public function deposit(PayoutTransaction $tr, string $p_method, string $fiat_currency)
    {
        $payload = [
            'order_id' => $tr->getId(),
            'payment_method' => $p_method,
            'fiat_amount' => number_format($tr->getSum(), 2, '.', ''),
            'fiat_currency' => $fiat_currency,
            'success_callback_url' => Hosts::getFinHost($tr->getCurrency())."/api/pay/exotic/deposit",
            'error_callback_url' => Hosts::getFinHost($tr->getCurrency())."/api/pay/exotic/deposit",
            'customer' => (string)$tr->user_id,
            'timeout' => 60 * 12,// 12 hours
        ];
        $sign = hash('sha256', $payload['order_id'] .':'. $payload['fiat_amount'] .':'. $payload['fiat_currency'] .':'.  $payload['payment_method'] .':'.$this->sign_key);
        return $this->query('/api/merchant/'.$this->merchant.'/create_pay_in', 'POST', array_merge($payload, ['sign' => $sign]));
    }

    public function depositEcom(PayoutTransaction $tr, string $p_type, string $fiat_currency)
    {
        $payload = [
            'order_id' => $tr->getId(),
            'payment_type' => $p_type,
            'fiat_amount' => number_format($tr->getSum(), 2, '.', ''),
            'fiat_currency' => $fiat_currency,
            'success_callback_url' => Hosts::getFinHost($tr->getCurrency())."/api/pay/exotic",
            'error_callback_url' => Hosts::getFinHost($tr->getCurrency())."/api/pay/exotic",
        ];
        $sign = hash('sha256', $payload['order_id'] .':'. $payload['fiat_amount'] .':'. $payload['fiat_currency'] .':'.  $payload['payment_type'] .':'.$this->sign_key);
        return $this->query('/api/merchant/'.$this->merchant.'/create_payment_ecom', 'POST', array_merge($payload, ['sign' => $sign]));
    }

    public function payout(ApplicationForPayment $tr, string $p_method, string $fiat_currency, array $rerqusites)
    {
        $payload = [
            'order_id' => 'o'.$tr->getId(),
            'payment_method' => $p_method,
            'fiat_amount' => number_format($tr->getSum(), 2, '.', ''),
            'fiat_currency' => $fiat_currency,
            'success_callback_url' => Hosts::getFinHost($tr->getCurrency())."/api/pay/exotic/payout",
            'error_callback_url' => Hosts::getFinHost($tr->getCurrency())."/api/pay/exotic/payout",
            'customer' => (string)$tr->user_id,
        ];
        $target_number = '';
        if (!empty($rerqusites['cardNumber'])) {
            $payload['number_card'] = $rerqusites['cardNumber'];
            $target_number = $rerqusites['cardNumber'];
        } elseif (!empty($rerqusites['phone'])) {
            $payload['phone_number'] = $rerqusites['phone'];
            $payload['bank_name'] = $rerqusites['bankName'];
            $target_number = $rerqusites['phone'];
        }
        $sign = hash('sha256', $payload['order_id'] .':'. $payload['fiat_amount']. ':'. $target_number .':'. $payload['fiat_currency'] .':'.$this->sign_key);
        return $this->query('/api/merchant/'.$this->merchant.'/create_pay_in', 'POST', array_merge($payload, ['sign' => $sign]));
    }

    public function courses()
    {
        return $this->query('/api/merchant/'.$this->merchant.'/course_usdt_rub/bybit_buy', 'GET');
    }

    public function orderState(string $tr_id)
    {
        return $this->query('/api/merchant/'.$this->merchant.'/status_pay_out/'.$tr_id, 'GET');
    }

    public static function depositCallback(Request $request)
    {
        Log::channel('pay')->info(__CLASS__ . 'callback');
        Log::channel('pay')->info(var_export($request->all(), true));
        Log::channel('pay')->info('signature:'.$request->header('Signature'));

        $data = $request->all();

        if (!isset($data['order_id'])) {
            Log::channel('pay')->error('order_id field not set');
            return response('', 500);
        }

        Log::channel('pay')->info('Start processing Exotic transaction with id ' . $data['order_id']);

        /** @var PayoutTransaction $transaction */
        $transaction = PayoutTransaction::where('id', $data['order_id'])
            ->whereIn('status_id', [
                PayoutTransaction::STATUS_EXOTIC_PENDING,
                PayoutTransaction::STATUS_EXOTIC_FAIL,
            ])
            ->first();

        if ($transaction === null) {
            if (PayoutTransaction::where('id', $data['order_id'])
                    ->where('status_id', PayoutTransaction::STATUS_EXOTIC_SUCCESS)
                    ->first() !== null) {
                Log::channel('pay')->error('Transaction with id ' . $data['order_id'] . ' is already complete found. Request: ' . PHP_EOL . var_export($request->all(), true));
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

        if (in_array($data['status'], ['rejected_timeout', 'rejected_merchant', 'rejected_gate'])) {
            Log::channel('pay')->error('status:' . $data['status']);
            $transaction->status_id = PayoutTransaction::STATUS_EXOTIC_FAIL;
            $transaction->save();
        }
//
//        $p_method = PaymentSystems::where('method_alias', $transaction->method)
//            ->where('type', PaymentSystems::TYPE_PAYIN)
//            ->where('currency_id', Wallet::getCurrencyCode($transaction->currency))
//            ->first();
//        if ($p_method === null) {
//            Log::channel('pay')->error('Payment method not found:' . $transaction->method);
//        } else {
//            $method_data = PaymentMethodData::getMethodData($p_method->id);
//            $hook_key = $method_data['Webhook']?? '';
//            $sign = hash_hmac('sha256', $request->getContent(), $hook_key);
//            if ($sign !== $request->header('Signature')) {
//                Log::channel('pay')->error('Wrong signature: currenct sign:' . $sign . ' header sign:' . $request->header('Signature'));
//                return response('wrong signature', 403);
//            }
//        }

        if ($data['status'] !== 'successful') {
            return 'ok';
        }

        $amount = $data['new_fiat_amount'] ?? $data['fiat_amount'];

        $currency = UserWallet::getCurrencyCode(strtoupper($data['fiat_currency']));
        $transaction->fee = $amount - $data['merchant_spent_usdt'] * $data['exchange_rate'];
        $transaction->card_number = $data['card_number'] ?? '';

        Payin::completeTransaction($user, $transaction, $amount, PayoutTransaction::STATUS_EXOTIC_SUCCESS, $currency);

        return response('ok', 200);
    }

    public static function payoutCallback(Request $request)
    {
        Log::channel('pay')->info(__CLASS__ . ' payout callback');
        Log::channel('pay')->info(var_export($request->all(), true));
    }

    private function query($method, $httpMethod, array $data = [])
    {
        $url = $method;
        if (!preg_match('/^http.*$/', $method)) {
            $url = $this->url . $method;
        }

        $ch = curl_init();

        if (strtolower($httpMethod) === 'post') {

            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

        } else {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
            if (!empty($data)) {
                $url .= '?' . http_build_query($data);
            }
        }

        Log::channel('pay')->info(__CLASS__ . 'request: ' . $url);
        Log::channel('pay')->info(var_export($data, true));

        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'X-Api-Key: ' . $this->api_key,
            'Content-Type: application/json',
        ]);

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);

        curl_setopt($ch, CURLOPT_USERAGENT, 'KrakenCassino');
        curl_setopt($ch, CURLOPT_TIMEOUT, 90);
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

        Log::channel('pay')->info(__CLASS__ . 'resp: ');
        Log::channel('pay')->info('code:' . $resp_code);
        Log::channel('pay')->info($text);
        Log::channel('pay')->info(var_export($resp, true));
        return $resp;
    }
}
