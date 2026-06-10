<?php

namespace App\PaymentsAPI;

use App\MUser;
use App\Payin\PayCraftPayin;
use App\Payin\Payin;
use App\PayoutTransaction;
use App\UserWallet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PayCraft
{

    protected static string $url = 'https://p2p-lk.paycraft.pro/api';
    private static string $public_key = '8f225b5a-9e9a-453e-9f55-f4549ee3cacc';
    private static string $secret_key = 'd450833a-7789-4084-92e7-99412bc31390';

    protected $lastResponse;

    public function __construct()
    {
        //
    }

    protected static function request(string $url, array $params = [], bool $post = true): object|bool
    {
        $url = self::$url . $url;
        $http = Http::withHeaders(self::genHeaders($params))->withBody(json_encode($params), 'application/json');

        $response = $post ? $http->post($url) : $http->get($url);

        Log::channel('pay')->info(__CLASS__.' request: '.$url);
        Log::channel('pay')->info(var_export($response?->object() ?? false, true));
        Log::channel('pay')->info(__CLASS__.' request body: '.$url);
        Log::channel('pay')->info(var_export($params, true));

        return $response?->object() ?? false;
    }

    public function in(int $order_id, int $amount, string $callback_url = ''): object|bool
    {

        /*return (object)[
        "address" => "2202 2069 5071 7665",
            "currency_name" => "",
            "success" => true,
            "dest_tag" => "",
            "description" => "",
            "standard_code" => 200,
            "external_id" => "9441732145335f8a50f5c8ffb4d0d89979a819dcbfd0293c1bb1133f281eb1a888b47dc71fc7ef02069fe52356d7094395e9d74eec795df4d37f658135ea3fce"
        ];*/

        return self::request(
            url: '/proxy/19/transaction/service/create_pay_in',
            params: [
                'external_id' => (string) $order_id,
                'amount' => $amount,
                'callback_url' => $callback_url,
            ],
            post: true
        );
    }

    public static function getStatus(string $order_id, string $mode = 'in'): object|bool
    {
        return self::request(
            url: '/proxy/19/transaction/service/pay' . $mode . '_status',
            params: [
                'clientUniqueId' => $order_id,
            ],
            post: true
        );
    }

    public function out(int $order_id, int $amount, int $card_number, string $callback_url = '')
    {
        return self::request(
            url: '/proxy/19/transaction/service/create_payout',
            params: [
                'clientUniqueId' => $order_id,
                'destination' => $card_number,
                'amount' => $amount,
                'callback_url' => $callback_url,
            ]
        );
    }

    public static function depositCallback(Request $request)
    {

        Log::channel('pay')->info(__CLASS__.' callback deposit request: ' . $request->fullUrl());
        Log::channel('pay')->info('getContent: ' . var_export($request->getContent(), true));
        Log::channel('pay')->info('all: ' . var_export($request->all(), true));

        if (empty($transaction_id = $request->external_id)) {
            Log::channel('pay')->error('external_id field not set');
            return response('', 500);
        }

        $transaction = PayoutTransaction::where('id', $transaction_id)->first();

        if(!$transaction){
            Log::channel('pay')->error('Transaction with id ' . $transaction_id . ' is not found.');
            return response('transaction not found', 404);
        }

        $response = PayCraft::getStatus(
            order_id: $transaction->id,
            mode: 'in',
        );

        $msg = 'Unknown payment status.';

        if(!isset($response->status)) return response(['status' => false, 'message' => 'return status failed']);

        // Find user which create this transaction.
        /** @var MUser $user $user */
        $user = MUser::where('id', $transaction->user_id)->first();

        if ($user === null) {
            Log::channel('pay')->error('User of the transaction is not found.');
            return response('user not found', 500);
        }

        switch($response->status){
            case PayCraftPayin::STATUS_PENDING:
                $transaction->status = PayoutTransaction::STATUS_PAYCRAFT_PENDING;
                $msg = 'Waiting for payment.';
                break;
            case PayCraftPayin::STATUS_SUCCESS:
                $transaction->status = PayoutTransaction::STATUS_PAYCRAFT_SUCCESS;
                $msg = 'Payment successful.';
                break;
            case PayCraftPayin::STATUS_ERROR:
                $transaction->status = PayoutTransaction::STATUS_PAYCRAFT_FAIL;
                $msg = 'Payment error.';
                break;
        }

        $transaction->save();

        $currency = UserWallet::getCurrencyCode($transaction->currency);

        Payin::completeTransaction($user, $transaction, $request->amountPaid, PayoutTransaction::STATUS_PAYCRAFT_SUCCESS, $currency);

        $transaction->refresh();

        return [
            'status' => $transaction->status,
            'msg' => $msg,
            'changed' => $transaction->wasChanged()
        ];
    }

    private static function genHeaders(array $params): array
    {
        return [
            'ApiPublic' => self::$public_key,
            'Signature' => self::genSignature($params),
        ];
    }

    private static function genSignature(array $data): string
    {
        //return urldecode(http_build_query($data));
        return hash_hmac('sha512', json_encode($data), self::$secret_key);
    }

}
