<?php

namespace App\PaymentsAPI;

use App\ApplicationForPayment;
use App\Helpers\Hlog;
use App\Helpers\Hosts;
use App\MUser;
use App\Payin\Payin;
use App\PayoutTransaction;
use App\UserWallet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * @docs https://api.turcode.app/v1/#/Payouts/cps
 */
class Turcode extends ApiBase
{

    protected string $api_url = 'https://api.turcode.app/v1';
    public string $api_key = 'b932ab467e2e129067caa61b7a822450';
    protected string $public_key = '0de87b571e5f0201607c897a148c9b1dcde6b106';

    public function balance(): object|bool
    {
        return $this->request(
            uri: '/getBalanceInfo',
        );
    }

    public function hpp(PayoutTransaction $transaction, int $bank = 100): object|bool
    {
        return $this->request(
            uri: '/redirectGateway',
            params: [
                'bank' => $bank,
                'currency' => 'RUB',
                'amount' => (int) $transaction->sum,
                'order' => (string) $transaction->id,
                'token' => (string) $transaction->user_id,
                #'name' => '',
                #'surname' => '',
                #'national' => '',
                #'email' => 'watson@gmail.com',
                'callback' => Hosts::getFinHost('RUB') . '/api/pay/turcode/callback?transaction_id=' . $transaction->id,
                'referer' => route('pay.close-tab'),
                #'language' => 'en'
            ]
        );
    }

    public function get(string $transaction_id): object|bool
    {
        return $this->request(
            uri: '/getTransactionInfo',
            params: [
                'order' => $transaction_id,
            ]
        );
    }

    public function getPayoutInfo(string $transaction_id): object|bool
    {
        return $this->request(
            uri: '/getPayoutInfo',
            params: [
                'id' => $transaction_id,
            ]
        );
    }

    /*public function dpi(PayoutTransaction $transaction)
    {
        return $this->request(
            uri: '/initiateTransaction',
            params: [
                'bank' => 9,
                'currency' => 'RUB',
                'amount' => (int) $transaction->sum,
                'ip' => request()->ip(),
                'order' => (string) $transaction->id,
                'token' => (string) $transaction->user_id,
                'callback' => $this->api_local_host . '/api/pay/turcode/callback?transaction_id=' . $transaction->id,
            ]
        );
    }*/

    public function out(ApplicationForPayment $transaction, int $amount, string $card_number): object|bool
    {
        return $this->request(
            uri: '/createPayout',
            params: [
                'amount' => $amount,
                'order' => (string) $transaction->id,
                'token' => (string) $transaction->user_id,
                'wallet' => $card_number,
                'method' => 'SBP',
                'currency' => 'RUB',
            ]
        );
    }

    protected function request(string $uri, array $params = [], bool $post = true): object|bool
    {
        $this->http = Http::acceptJson();
        $timestamp = time();
        $jsonBody = json_encode($params, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        $message = $jsonBody . $timestamp;
        $signature = hash_hmac('sha256', $message, $this->public_key);

        $this->headers = [
            'x-key' => $this->api_key,
            'x-signature' => $signature,
            'x-timestamp' => $timestamp,
            //'X-Body-Debug' => $message,
        ];

        $this->http->withBody(
            content: json_encode($params, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            contentType: 'application/json'
        );

        Log::channel('pay')->info(get_class($this) . ' API:', [
            'content' => json_encode($params, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
        ]);

        return parent::request(
            uri: $uri,
        );
    }

    public function callback(Request $request)
    {
        Log::channel('pay')->info(__CLASS__.' callback deposit request');
        Log::channel('pay')->info(var_export($request->headers->all(), true));
        Log::channel('pay')->info(var_export($request->getUri(), true));
        Log::channel('pay')->info(var_export($request->getContent(), true));
        Log::channel('pay')->info(var_export($request->all(), true));

        $transaction_id = $request->get('transaction_id');

        try {
            DB::beginTransaction();
            $transaction = PayoutTransaction::where('system_id', Payin::TYPE_TURCODE)
                ->where('id', $transaction_id)
                ->lockForUpdate()
                ->first();

            if ($transaction === null) {
                return response('transaction not found', status: 404);
            }
            if ($transaction->status_id == PayoutTransaction::STATUS_TURCODE_SUCCESS) {
                Log::channel('pay')->info(__CLASS__.'transaction already completed #'.$transaction_id);
                return response('OK', status: 200);
            }

            $response = $this->get($transaction->id);

            $user = MUser::where('id', $transaction->user_id)->firstOrFail();

            switch ($response->status) {
                case 'Pending':
                    $transaction->status_id = PayoutTransaction::STATUS_TURCODE_PENDING;
                    break;
                case 'Complete':
                    $amount = $response->amount;
                    $transaction->fee = $amount * 0.09;

                    Payin::completeTransaction($user, $transaction, $amount, PayoutTransaction::STATUS_TURCODE_SUCCESS, UserWallet::getCurrencyCode($transaction->currency));

                    break;
                case 'Rejected':

                    $transaction->status_id = PayoutTransaction::STATUS_TURCODE_FAIL;
                    break;

            }

            $transaction->save();
        } catch (\Exception $e) {
            Hlog::logException($e);
        } finally {
            DB::commit();
        }

        return response('OK', status: 200);

    }

}
