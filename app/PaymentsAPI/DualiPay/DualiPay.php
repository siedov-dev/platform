<?php

namespace App\PaymentsAPI\DualiPay;

use App\Helpers\Hlog;
use App\Helpers\Hosts;
use App\MUser;
use App\Payin\Payin;
use App\PayoutTransaction;
use App\Wallet;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;

/**
 * @docs https://api.dualipay.com/docs/auth
 */
final class DualiPay
{
    private string $api_token = "3e97a4fc1d5b4fce82b3a8e2c4579160";
    private string $merchant_id = "769a9b6b-45d2-559f-aeca-cb00173937ed";
    private string $api_url = "https://api.dualipay.com";

    public function depositH2h(string $tr_id, string $user_id, string $method, float $amount, string $currency = 'RUB')
    {
        $endpoint = $this->api_url . "/api/payment/request";
        $payload = [
            'transactionId' => $tr_id,
            'externalUserId' => $user_id,
            'amount' => $amount,
            'currency' => $currency,
            'merchantToken' => $this->merchant_id,
            'paymentMethod' => $method,
            'callbackUrl' => Hosts::getFinHost($currency)."/api/pay/duali/callback",
            'redirectUrl' => route('pay.close-tab')
        ];

        return $this->query($endpoint, $payload, 'post');
    }

    public function payout(string $tr_id, string $user_id, string $method, float $amount, string $currency, string $identifier, array $requisites)
    {
        $endpoint = $this->api_url . "/api/payout/init";
        $payload = [
            'transactionId' => $tr_id,
            'externalUserId' => $user_id,
            'amount' => $amount,
            'merchantToken' => $this->merchant_id,
            'callbackUrl' => Hosts::getFinHost($currency)."/api/pay/duali/payout",
            'userRequisite' => [
                'type' => $method,
                'currency' => $currency,
                'identifier' => $identifier,
            ],
        ];

        if (!empty($requisites['bank'])) {
            $payload['userRequisite']['acquiringBank'] = $requisites['bank'];
        }

        return $this->query($endpoint, $payload, 'post');
    }

    public function getPayoutTicket(string $token)
    {
        $endpoint = $this->api_url . "/api/payout/$token";
        $payload = [];
        return $this->query($endpoint, $payload, 'get');
    }

    public function trList()
    {
        $endpoint = $this->api_url . "/api/payment/list";
        $payload = [
            'createdDateStart' => '2025-07-10T00:00:00.000Z',
        ];
        return $this->query($endpoint, $payload, 'get');
    }

    public function completeTransaction($tr_id, $user_id, $amount)
    {
        $endpoint = $this->api_url . "/api/payment/complete/$tr_id";
        $payload = [
            'externalUserId' => $user_id,
            'amount' => $amount,
        ];
        return $this->query($endpoint, $payload, 'get');
    }

    private function query(string $url, array $data = [], string $httpMethod = 'post')
    {
        Log::channel('pay')->info(__CLASS__ . " query $url method $httpMethod data:", $data);

        if (mb_strtolower($httpMethod) === 'get') {
            $result = Http::withToken($this->api_token)->acceptJson()->get($url, $data)->json();
        } else {
            $result = Http::withToken($this->api_token)->acceptJson()->post($url, $data)->json();
        }

        Log::channel('pay')->info(__CLASS__ . " query $url response:", $result);
        return $result;
    }

    public static function depositCallback(Request $request)
    {
        Log::channel('pay')->info(__CLASS__ . " webhook:", $request->all());
        $data = $request->all();

        if (!isset($data['ticket']['transactionId'])) {
            Log::channel('pay')->error('transactionId field not set');
            return response('', 404);
        }

        $order_id = $data['ticket']['transactionId'];

        Log::channel('pay')->info('Start processing DualiPay transaction with id ' . $order_id);

        try {
            DB::beginTransaction();

            /** @var PayoutTransaction $transaction */
            $transaction = PayoutTransaction::where('id', $order_id)
                ->whereIn('status_id', [
                    PayoutTransaction::STATUS_DUALIPAY_PENDING,
                    PayoutTransaction::STATUS_DUALIPAY_FAIL,
                ])
                ->lockForUpdate()
                ->first();

            if ($transaction?->status_id === PayoutTransaction::STATUS_DUALIPAY_FAIL) {
                Log::channel('pay')->warning('Transaction with id ' . $order_id . ' already failed found. Processing');
            }

            if ($transaction === null) {
                if (PayoutTransaction::where('id', $order_id)
                        ->where('status_id', PayoutTransaction::STATUS_DUALIPAY_SUCCESS)
                        ->first() !== null) {
                    Log::channel('pay')->error('Transaction with id ' . $order_id . ' is already complete found. Request: ' . PHP_EOL . var_export($request->all(), true));
                    return response('ok', 200);
                }
                Log::channel('pay')->error('Transaction with id ' . $order_id . ' is not found. Request: ' . PHP_EOL . var_export($request->all(), true));
                return response('transaction not found', 404);
            }

            // Find the user which created this transaction.
            /** @var MUser $user $user */
            $user = MUser::find($transaction->user_id);

            if ($user === null) {
                Log::channel('pay')->error('User of the transaction is not found.');
                return response('user not found', 500);
            }

            if ($data['ticket']['status'] === 'DECLINED' || $data['ticket']['status'] === 'EXPIRED') {
                Log::channel('pay')->error('status:' . $data['ticket']['status']);
                $transaction->status_id = PayoutTransaction::STATUS_DUALIPAY_FAIL;
                $transaction->coment = $data['ticket']['status'] ?? ' status error';
                $transaction->save();
                return 'ok';
            }

            if ($data['ticket']['status'] !== 'COMPLETED') {
                Log::channel('pay')->info('status:' . $data['ticket']['status']);
                $transaction->coment = $data['ticket']['status'] ?? 'pending status';
                $transaction->save();
                return 'ok';
            }

            $amount = $data['ticket']['amount'];

            $currency = Wallet::getCurrencyCode($data['ticket']['currency']);
            $transaction->fee = $amount * 0.11;
//            if (!empty($data['ticket']['fee'])) {
//                $transaction->fee = $amount * $data['tx']['fee_pct'] / 100;
//            }
//            $transaction->card_number = $data['ticket']['requisite'] ?? '';

            Payin::completeTransaction($user, $transaction, $amount, PayoutTransaction::STATUS_DUALIPAY_SUCCESS, $currency);

        } catch (\Throwable $e) {
            Hlog::logException($e, 'pay');
        } finally {
            DB::commit();
        }

        return response('ok', 200);
    }

    public static function payoutCallback(Request $request)
    {
        Log::channel('pay')->info(__CLASS__ . " payout:", $request->all());
    }
}
