<?php

namespace App\PaymentsAPI\Payme;

use App\Helpers\Hlog;
use App\Helpers\Hosts;
use App\MUser;
use App\Payin\Payin;
use App\PayoutTransaction;
use App\Wallet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * @docs https://sandbox.payme.center/api
 */
final class Payme
{
    private string $url = 'https://paymentspage2.com';
    private string $api3_key = 'uv47Kxh2Idm5NbAisg1lTAU076PcjCiSL29G5NDgujUkyJncMPwz8GsUFvMEKTH77rcbWVXCzUZ9kJXciax6dv0iROhjZD9s9CPxgFZv6SSOGM3CDQMW4FyaV5TL8sHF';
    private string $api5_key = 'wCMNXMES6vzITUKnJznjymBxyI4AOquBDK8i03v6oGG3XseH9QHupbgLqoBD7mik9XFKHeYZYHVIPGl2zBAojAcqb7sqkPzXP7RxKIz8nZIdIpwnuptA1Mdrq5PBHxIi';
    private int $merchant_id = 2251;

    public function balance()
    {
        $url = $this->url . '/api/v5/balance';
        return Http::withToken($this->api5_key)->get($url)->json();
    }

    public function balanceFiat()
    {
        $url = $this->url . '/api/v5/fiat-balances';
        return Http::withToken($this->api5_key)->get($url)->json();
    }

    public function limits()
    {
        $url = $this->url . '/api/v3/amount-limits';
        return Http::withToken($this->api3_key)->get($url)->json();
    }

    public function depositForm(string $order_id, float $amount, string $currency, string $user_id, $fiat = 0, $method = ""): array
    {
        $url = $this->url . '/api/v5/invoice/get';
        $params = [
            'merchant_id' => $this->merchant_id,
            'amount' => $amount,
            'currency' => $currency,
            'order_id' => $order_id,
            'server_url' => Hosts::getFinHost(). '/api/pay/payme',
            'cancel_url' => route('pay.close-tab'),
            'response_url' => route('pay.close-tab'),
            'return_url' => route('pay.close-tab'),
            'order_desc' => 'Deposit',
            'customer_id' => $user_id,
            "filter_deal_form" => $fiat,
            "currency2currency" => true,
            "locale" => "ru",
        ];
        if (!empty($method)) {
            $params["filter_payment_system_types"][] = $method;
        }
        $params['signature'] = $this->sign($params);
        Log::channel('pay')->info(__CLASS__ . 'request: ' . $url, $params);
        $resp = Http::withToken($this->api5_key)->asJson()->timeout(30)->post($url, $params)->json();
        Log::channel('pay')->info(__CLASS__ . 'resp: ' . var_export($resp, true));
        return $resp;
    }

    public function withdrawRequest(float $amount, string $currency = 'RUB', string $system_type = 'card_number', $fiat = 0)
    {
        $url = $this->url . '/api/v3/withdrawal/request';
        $payload = [
            'amount' => $amount,
            'currency' => $currency,
            'merchant_expense' => 1,
            'currency2currency' => true,
            'filter_payment_system_types' => [
                $system_type
            ],
            "filter_deal_form" => $fiat,
        ];
        Log::channel('payout')->info(__CLASS__ . 'request: ' . $url, $payload);
        return Http::withToken($this->api3_key)->asJson()->post($url, $payload)->json();
    }

    public function withdrawCreate(string $ad_id, string $order_id, string $acc, float $amount, string $currency = 'RUB')
    {
        $url = $this->url . '/api/v3/withdrawal/create';
        $payload = [
            'ad_id' => $ad_id,
            'order_id' => $order_id,
            'amount' => $amount,
            'currency' => $currency,
            'message' => $acc,
            'merchant_expense' => 1,
            'currency2currency' => true,
        ];
        Log::channel('payout')->info(__CLASS__ . 'request: ' . $url, $payload);
        return Http::withToken($this->api3_key)->asJson()->post($url, $payload)->json();
    }

    public function confirmPayout(string $invoice_id)
    {
        $url = $this->url . '/api/v3/withdrawal/confirm';
        $payload = [
            'invoice_id' => $invoice_id,
        ];
        return Http::withToken($this->api3_key)->asJson()->post($url, $payload)->json();
    }

    public function payoutStatus(string $invoice_id)
    {
        $url = $this->url . '/api/v3/withdrawal/check/status';
        $payload = [
            'invoice_id' => $invoice_id,
        ];
        return Http::withToken($this->api3_key)->asJson()->post($url, $payload)->json();
    }

    public function banks(string $currency = 'RUB', string $type = 'card_number')
    {
        $url = $this->url . '/api/v3/payment-systems/list';
        $payload = [
            'currency' => $currency,
            'payment_system_type' => $type,
        ];
        return Http::withToken($this->api3_key)->post($url, $payload)->json();
    }

    private function sign(array $params): string
    {
        $data = $this->array2string($params);
        return strtolower(sha1($this->api5_key.$data));
    }

    private function array2string($params)
    {
        $filteredData = array_filter($params, function($var) {
            return $var !== '' && $var !== null;
        });
        ksort($filteredData);
        $signStr = '';
        foreach ($filteredData as $key => $value) {
            if (is_array($value)) {
                $signStr .= $this->array2string($value);
            } else {
                $signStr .= '|' . $value;
            }
        }
        return $signStr;
    }

    public static function depositCallback(Request $request)
    {
        Log::channel('pay')->info(__CLASS__ . ' callback deposit request');
        Log::channel('pay')->info(var_export($request->all(), true));

        $data = $request->all();
        $tr_id = $data['order_id'] ?? '';

        if (empty($tr_id)) {
            Log::channel('pay')->error('order_id field not set');
            return response('', 500);
        }

        Log::channel('pay')->info('Start processing Payme transaction with id ' . $tr_id);

        DB::beginTransaction();
        try {
            /** @var PayoutTransaction $transaction */
            $transaction = PayoutTransaction::where('id', $tr_id)
                ->whereIn('status_id', [
                    PayoutTransaction::STATUS_PAYME_PENDING,
                    PayoutTransaction::STATUS_PAYME_FAIL,
                ])
                ->lockForUpdate()
                ->first();

            if ($transaction?->getStatusId() === PayoutTransaction::STATUS_PAYME_FAIL)  {
                Log::channel('pay')->error('Transaction with id ' . $tr_id . ' in failed state found. Request: ' . PHP_EOL . var_export($request->all(), true));
            }

            if ($transaction === null) {
                if (PayoutTransaction::where('id', $tr_id)
                        ->where('status_id', PayoutTransaction::STATUS_PAYME_SUCCESS)
                        ->first() !== null) {
                    Log::channel('pay')->error('Transaction with id ' . $tr_id . ' is already complete found. Request: ' . PHP_EOL . var_export($request->all(), true));
                    return response('ok', 200);
                }
                Log::channel('pay')->error('Transaction with id ' . $tr_id . ' is not found. Request: ' . PHP_EOL . var_export($request->all(), true));
                return response('transaction not found', 404);
            }

            // Find the user created this transaction.
            /** @var MUser $user $user */
            $user = MUser::where('id', $transaction->user_id)->first();

            if ($user === null) {
                Log::channel('pay')->error('User of the transaction is not found.');
                return response('user not found', 500);
            }

            if (in_array($data['status'], [-1])) {
                Log::channel('pay')->error('status:' . $data['status']);
                $transaction->status_id = PayoutTransaction::STATUS_PAYME_FAIL;
                $transaction->coment = $data['cancellation_reason'] ?? ' cancel error';
                $transaction->save();
                return 'ok';
            }

            if (!in_array($data['status'],[1])) {
                return 'ok';
            }

            $amount = $data['amount_currency'];

            $currency = Wallet::getCurrencyCode(mb_strtoupper($data['fiat_currency']));

            $transaction->fee = $amount * 0.105;

            Payin::completeTransaction($user, $transaction, $amount, PayoutTransaction::STATUS_PAYME_SUCCESS, $currency);
        } catch (\Exception $e) {
            Hlog::logException($e, 'pay');
        } finally {
            DB::commit();
        }

        return response('ok', 200);
    }
}
