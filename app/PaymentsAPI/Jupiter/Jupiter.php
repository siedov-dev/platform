<?php

namespace App\PaymentsAPI\Jupiter;

use App\ApplicationForPayment;
use App\Helpers\Hosts;
use App\MUser;
use App\Payin\Payin;
use App\PayoutTransaction;
use App\UserWallet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

final class Jupiter
{
    private string $base_url = "https://app.juppiter.io";
    private string $in_token = '38:35:33:33:35:36:38';
    private string $in_key = 'wb2EnEcakV';
    private string $out_token = '35:36:34:39:37:34:34';
    private string $out_key = 'bpZCX5aFIG';

    public function balance()
    {
        $api_method = "/p2p_balance_v1.0";
        $data = [
            'token' => $this->in_token,
        ];
        $sign_string = 'token=' . $data['token'];

        $data['signature'] = hash_hmac('sha256', $sign_string, $this->in_key);

        return $this->query($this->base_url.$api_method, $data, 'post');
    }

    public function payin(PayoutTransaction $tr, float $amount, string $method, array $data, string $currency = 'KZT')
    {
        $phone = $data['phone'];
        $phone = preg_replace('#[^\d]#', '', $phone);
        $api_method = "/p2p_payin_v1.0";
        $data = [
            'token' => $this->in_token,
            'order_id' => (string)$tr->getId(),
            'amount' => [
                'value' => number_format($amount, 2, '.', ''),
                'currency' => $currency,
            ],
            'customer' => [
                'id' => (string)$tr->getUserId(),
            ],
            'details' => [
                'phone' => substr($phone, 0, 1).' '. substr($phone, 1),
                'email' => $data['email'],
            ],
            'redirect' => 'false',
            'description' => $method.'-STD',
            'callback_url' => Hosts::getFinHost('KZT'). '/api/pay/jupiter',
            'success_url' => route('pay.success'),
            'fail_url' => route('pay.fail'),
        ];

        $sign_string = 'token=' . $data['token']
            . 'order_id=' . $data['order_id']
            . 'amount.value=' . $data['amount']['value']
            . 'amount.currency=' . $data['amount']['currency']
            . 'customer.id=' . $data['customer']['id']
            . 'redirect=' . $data['redirect'];

        $data['signature'] = hash_hmac('sha256', $sign_string, $this->in_key);

        return $this->query($this->base_url.$api_method, $data, 'post');
    }

    public function payout(ApplicationForPayment $tr, float $amount, string $method, string $currency = 'KZT', $requisites = [])
    {
        $api_method = "/p2p_payout_v1.0";
        $data = [
            'token' => $this->out_token,
            'order_id' => $tr->getId(),
            'amount' => [
                'value' => number_format($amount, 2, '.', ''),
                'currency' => $currency,
            ],
            'customer' => [
                'id' => $tr->getUserId(),
            ],
            'description' => $method.'-FTD',
            'details' => [
                'card' => $requisites['card_number'],
            ],
        ];

        $sign_string = 'token=' . $data['token']
            . 'order_id=' . $data['order_id']
            . 'amount.value=' . $data['amount']['value']
            . 'amount.currency=' . $data['amount']['currency']
            . 'customer.id=' . $data['customer']['id']
            . 'details.card=' . $data['details']['card'];

        $data['signature'] = hash_hmac('sha256', $sign_string, $this->out_key);

        return $this->query($this->base_url.$api_method, $data, 'post');
    }

    public function payoutMobile(ApplicationForPayment $tr, float $amount, string $method, string $currency = 'KZT', $requisites = [])
    {
        $api_method = "/p2p_payout_v1.0";
        $data = [
            'token' => $this->out_token,
            'order_id' => $tr->getId(),
            'amount' => [
                'value' => number_format($amount, 2, '.', ''),
                'currency' => $currency,
            ],
            'customer' => [
                'id' => $tr->getUserId(),
            ],
            'description' => $method,
            'details' => [
                'phone' => substr($requisites['phone'], 0, 1).' '. substr($requisites['phone'], 1),
                'email' => $requisites['email'],
            ],
        ];

        $sign_string = 'token=' . $data['token']
            . 'order_id=' . $data['order_id']
            . 'amount.value=' . $data['amount']['value']
            . 'amount.currency=' . $data['amount']['currency']
            . 'customer.id=' . $data['customer']['id']
            . 'details.phone=' . $data['details']['phone'];

        $data['signature'] = hash_hmac('sha256', $sign_string, $this->out_key);

        return $this->query($this->base_url.$api_method, $data, 'post');
    }

    public function status(string $transaction_id)
    {
        $api_method = "/p2p_status_v1.0";
        $data = [
            'token' => $this->out_token,
            'transaction_id' => $transaction_id
        ];

        $sign_string = 'token=' . $data['token']
            . 'transaction_id=' . $data['transaction_id'];

        $data['signature'] = hash_hmac('sha256', $sign_string, $this->out_key);

        return $this->query($this->base_url.$api_method, $data, 'post');
    }

    private function query(string $url, array $data = [], string $httpMethod = 'post')
    {
        Log::channel('pay')->info(__CLASS__ . " query $url method $httpMethod data:", $data);

        if (mb_strtolower($httpMethod) === 'get') {
            $result = Http::asJson()->acceptJson()->get($url, $data)->json();
        } else {
            $result = Http::asJson()->acceptJson()->post($url, $data)->json();
        }

        Log::channel('pay')->info(__CLASS__ . " query $url response:". var_export($result, true));
        return $result;
    }

    public static function callback(Request $request)
    {
        Log::channel('pay')->info(__CLASS__ . " callback:", $request->all());
        $data = $request->all();

        if (!isset($data['order_id'])) {
            Log::channel('pay')->error('merchant_tx_id field not set');
            return response('', 404);
        }

        $order_id = $data['order_id'];

        Log::channel('pay')->info('Start processing Jupiter transaction with id ' . $order_id);

        try {
            DB::beginTransaction();

            /** @var PayoutTransaction $transaction */
            $transaction = PayoutTransaction::where('id', $order_id)
                ->where('status_id', PayoutTransaction::STATUS_JUPITER_PENDING)
                ->lockForUpdate()
                ->first();

            if ($transaction === null) {
                if (PayoutTransaction::where('id', $order_id)
                        ->where('status_id', PayoutTransaction::STATUS_JUPITER_SUCCESS)
                        ->first() !== null) {
                    Log::channel('pay')->error('Transaction with id ' . $order_id . ' is already complete found. Request: ' . PHP_EOL . var_export($request->all(), true));
                    return response('ok', 200);
                }
                Log::channel('pay')->error('Transaction with id ' . $order_id . ' is not found. Request: ' . PHP_EOL . var_export($request->all(), true));
                return response('transaction not found', 404);
            }

            // Find user which create this transaction.
            /** @var MUser $user $user */
            $user = MUser::where('id', $transaction->user_id)->first();

            if ($user === null) {
                Log::channel('pay')->error('User of the transaction is not found.');
                return response('user not found', 500);
            }

            if (in_array($data['status']['type'], ['cancelled', 'declined', 'error', 'disputed'])) {
                Log::channel('pay')->error('error status:', $data);
                $transaction->status_id = PayoutTransaction::STATUS_JUPITER_FAIL;
                $transaction->coment = $data['error_description'] ?? $data['status']['type'];
                $transaction->save();
                return 'ok';
            }

            if (in_array($data['status']['type'], ['success'])) {
                $transaction->coment = $data['status']['type'];
                $transaction->save();
            }

            $amount = $data['amount']['value'];

            $currency = UserWallet::getCurrencyCode($data['amount']['currency']);
            $transaction->fee = 0;

            Payin::completeTransaction($user, $transaction, $amount, PayoutTransaction::STATUS_JUPITER_SUCCESS, $currency);

        } catch (\Exception $e) {
            Log::channel('pay')->error($e->getMessage());
        } finally {
            DB::commit();
        }

        return response('ok', 200);
    }

}
