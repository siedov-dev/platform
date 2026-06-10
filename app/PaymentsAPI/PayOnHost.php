<?php

namespace App\PaymentsAPI;

use App\ApplicationForPayment;
use App\Helpers\Hlog;
use App\MUser;
use App\Payin\Payin;
use App\PayoutTransaction;
use App\UserWallet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * @docs https://docs.settlepay.net/services/card_payout
 */
class PayOnHost extends ApiBase
{

    protected string $api_url = 'https://api.payonhost.com';
    private array $api_tokens = [
        27579 => '6xnWBWosDgJSD1cByENYkCBcheFzeHEc',
        27547 => 'cm6mXGZQR9pEYsYEHhehqBkqxfvIicHs',
        27530 => 'wiYa27kfNEWvuo9URNMTmhkqZxPyKE7M',
        27533 => '6tW2AL19WyoGHs3wYdJ2sVeGN5amDIn3',
        27525 => 'S78lMS5ZGWkMloogjqq7eGrnVUo5qlZq',
    ];
    private int $account_id = 24819;
    private int $point_id = 27579;

    protected function request(string $uri, array $params = [], bool $post = true): object|bool
    {
        $key = (int) (microtime(true) * 1000);

        $params['auth'] = [
            'point' => (int) $this->point_id,
            'key' =>  $key,
            'hash' => md5($this->point_id . $this->api_tokens[$this->point_id] . $key),
        ];

        /*$params['fields'] = [
            'product_type' => 'Balance',
            'product_name' => $params['currency'],
        ];

        $params['point'] = [
            'callback_url' => 'https://krakenfn.com/api/pay/payonhost/callback?transaction_id={transaction_id}'
        ];*/

        return parent::request(
                uri: $uri,
                params: $params,
                post: $post
            );
    }

    public function get(int $id): object|bool
    {
        return $this->request(
            uri: '/transaction/find',
            params: [
                'id' => $id
            ],
            //post: false,
        );
    }

    public function in(PayoutTransaction $transaction, string $type = 'binance_pay', array $add_fields = []): object|bool
    {
        list($system_name, $system_syb) = explode('.', $transaction->method);
        list($point_id, $wallet_id, $service_id) = explode(':', $system_syb);

        $this->point_id = $point_id;

        return $this->request(
            uri: '/transaction/create',
            params: [
                'locale' => 'en',
                'account_id' => $this->account_id,
                'service_id' => (int) $service_id,
                'wallet_id' => (int) $wallet_id,
                'customer_ip_address' => request()->ip(),
                'external_transaction_id' => $transaction->id,
                //'external_order_id' => '',
                'external_customer_id' => $transaction->user_id,
                'amount' => $transaction->sum * 100,
                'amount_currency' => $transaction->currency,
                'fields' => array_merge([
                    'product_type' => 'Balance',
                    'product_name' => $transaction->currency,
                ], $add_fields),
                'point' => [
                    'callback_url' => 'https://krakenfn.com/api/pay/payonhost/callback?transaction_id={transaction_id}'
                ]
            ],

        );
    }

    /**
     * Process payout transaction.
     *
     * @param PayoutTransaction $transaction
     * @param string $receiver_type Possible values: 'BINANCE_ID', 'EMAIL'
     * @param string $receiver
     * @return object|bool
     */
    public function out(ApplicationForPayment $transaction, string $receiver_type, string $receiver): object|bool
    {
        list($system_name, $system_syb) = explode('.', $transaction->system_pay);
        list($point_id, $wallet_id, $service_id) = explode(':', $system_syb);

        $this->point_id = $point_id;

        /** @var MUser $user */
        $user = MUser::find($transaction->user_id);

        return $this->request(
            uri: '/transaction/pay',
            params: [
                'locale' => 'en',
                'account_id' => $this->account_id,
                'service_id' => $service_id,
                'wallet_id' => $wallet_id,
                'customer_ip_address' => $user?->last_ip?? '',
                'external_transaction_id' => $transaction->id,
                //'external_order_id' => '',
                'external_customer_id' => $transaction->user_id,
                'amount' => $transaction->money * 100,
                'amount_currency' => UserWallet::currencyName($transaction->currency),
                'fields' => [
                    'receiver_type' => $receiver_type,
                    'receiver' => $receiver,
                ],
                /*'point' => [
                    'callback_url' => 'https://krakenfn.com/api/pay/payonhost/callback?transaction_id={transaction_id}'
                ]*/
            ],

        );
    }

    public function outCard(ApplicationForPayment $transaction, array $requisites): object|bool
    {
        list($system_name, $system_syb) = explode('.', $transaction->system_pay);
        list($point_id, $wallet_id, $service_id) = explode(':', $system_syb);

        /** @var MUser $user */
        $user = MUser::find($transaction->user_id);

        $this->point_id = $point_id;

        return $this->request(
            uri: '/transaction/pay',
            params: [
                'locale' => 'en',
                'account_id' => $this->account_id,
                'service_id' => $service_id,
                'wallet_id' => $wallet_id,
                'customer_ip_address' => $user?->last_ip?? '',
                'external_transaction_id' => $transaction->id,
                'external_customer_id' => $transaction->user_id,
                'amount' => $transaction->money * 100,
                'amount_currency' => UserWallet::currencyName($transaction->currency),
                'fields' => [
                    'card_number' => $requisites['cardNumber'],
                    'expire_month' => $requisites['month'],
                    'expire_year' => $requisites['year'],
                    'country' => '398',
                    'city' => $requisites['city'],
                    'address' => $requisites['address'],
                    'first_name' => $requisites['first_name'],
                    'last_name' => $requisites['last_name'],
                ],
            ],
        );
    }

    public function setIds(string $method)
    {
        list($system_name, $system_syb) = explode('.', $method);
        list($point_id, $wallet_id, $service_id) = explode(':', $system_syb);
        $this->point_id = $point_id;
    }


    public function callback(Request $request)
    {
        Log::channel('pay')->info(__CLASS__.' callback deposit request');
        Log::channel('pay')->info(var_export($request->getUri(), true));
        Log::channel('pay')->info(var_export($request->getContent(), true));
        Log::channel('pay')->info(var_export($request->all(), true));

        $transaction_id = $request->transaction_id;

        try {
            DB::beginTransaction();
            $transaction = PayoutTransaction::where('o_tr_id', $transaction_id)
                ->lockForUpdate()
                ->first();

            if ($transaction === null) {
                return response('transaction not found', status: 404);
            }

            if ($transaction->status_id == PayoutTransaction::STATUS_PAYONHOST_SUCCESS) {
                Log::channel('pay')->info(__CLASS__.'transaction already completed #'.$transaction_id);
                return response('OK', status: 200);
            }

            list($point_id, $wallet_id, $service_id) = explode(':', explode('.', $transaction->method)[1]);
            $this->point_id = $point_id;

            $response = $this->get($transaction_id)?->response;

            switch ($response->status) {
                case 0:
                case 6:
                case 10:
                    $transaction->status_id = PayoutTransaction::STATUS_PAYONHOST_PENDING;
                    break;
                case 1:
                    if($transaction->status_id != PayoutTransaction::STATUS_PAYONHOST_SUCCESS){
                        $user = MUser::where('id', $transaction->user_id)->first();

                        $amount = $response->amount / 100;
                        $transaction->fee = $amount - ($response->account_wallet_amount / 100);

                        Payin::completeTransaction($user, $transaction, $amount, PayoutTransaction::STATUS_PAYONHOST_SUCCESS, UserWallet::getCurrencyCode($response->amount_currency));
                    }
                    break;
                case 2:
                case 3:
                case 4:
                case 5:
                    $transaction->status_id = PayoutTransaction::STATUS_PAYONHOST_FAIL;
                    break;
            }

            $transaction->coment = 'status code: ' . $response->status;
            $transaction->save();

        } catch (\Exception $e) {
            Hlog::logException($e);
        } finally {
            DB::commit();
        }

        return ['status' => 200];

    }

}

/*wallet_id: 30905
service_id: 4329 | Payin form Binance Pay KZT

wallet_id: 30906
service_id: 4327 | Payin form Binance Pay EUR

wallet_id: 30907
service_id: 4326 | Payin form Binance Pay USD

wallet_id: 30908
service_id: 4919 | Payin form Binance Pay INR

wallet_id: 30909
service_id: 2611 | Payout Binance Pay USD

wallet_id: 30910
service_id: 12555 | Payout Binance Pay KZT

wallet_id: 30912
service_id: 12556 | Payout Binance Pay EUR

wallet_id: 30914
service_id: 12521 | Payout Binance Pay INR*/
