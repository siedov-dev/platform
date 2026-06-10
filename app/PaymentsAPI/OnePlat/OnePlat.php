<?php

namespace App\PaymentsAPI\OnePlat;

use App\Helpers\Hlog;
use App\MUser;
use App\Payin\Payin;
use App\PayoutTransaction;
use App\Wallet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * @docs https://1plat.money/doc/api?page=api&tab=withdrawCreate
 */
final class OnePlat
{
    private string $url = "https://1plat.money";
    private string $secret = "ROSUP6F8UQPRTZ30NMIWBEF22OLDH599";
    private int $shop_id = 544;

    public function depositMethods()
    {
        $endpoint = $this->url . "/api/merchant/payments/methods/by-api";
        $data = [
        ];
        return $this->query($endpoint, $data, 'get');
    }

    public function deposit(string $tr_id, MUser $user, int $amount, string $method)
    {
        $endpoint = $this->url . "/api/merchant/order/create/by-api";
        $payload = [
            'merchant_order_id' => $tr_id,
            'user_id' => $user->getId(),
            'amount' => $amount,
            'email' => $user->email?? 'user_id@temp.com',
            'method' => $method,
        ];

        return $this->query($endpoint, $payload, 'post');
    }

    public function payout(string $tr_id, float $amount, int $pay_method_id, array $requisites)
    {
        $endpoint = $this->url . "/api/merchant/withdraw/shop/create/by-api";
        $payload = [
            'amount' => $amount,
            'merchant_id' => $tr_id,
            'method_id' => $pay_method_id,
            'purse' => $requisites['purse'],
        ];

        if (!empty($requisites['bank'])) {
            $payload['bank'] = $requisites['bank'];
        }

        return $this->query($endpoint, $payload, 'post');
    }

    public function payoutStatus(string $order_id)
    {
        $endpoint = $this->url . "/api/merchant/shop/withdraw/info/$order_id/by-api";
        $data = [
        ];
        return $this->query($endpoint, $data, 'get');
    }

    public function payoutMethods()
    {
        $endpoint = $this->url . "/api/merchant/withdraws/methods/by-api";
        $data = [
        ];
        return $this->query($endpoint, $data, 'get');
    }

    private function query(string $url, array $data = [], string $httpMethod = 'post')
    {
        Log::channel('pay')->info(__CLASS__ . " query $url method $httpMethod data:", $data);

        $request = Http::asJson()->withHeaders([
            "x-shop" => $this->shop_id,
            "x-secret" => $this->secret,
        ])->acceptJson();

        if (mb_strtolower($httpMethod) === 'get') {
            $result = $request->get($url, $data)->body();
        } else {
            $result  = $request->post($url, $data)->body();
        }

        Log::channel('pay')->info(__CLASS__ . " query $url response:". $result);
        return json_decode($result, true);
    }

    public static function depositCallback(Request $request)
    {
        Log::channel('pay')->info(__CLASS__ . " callback:", $request->all());
        $data = $request->all();

        if (!isset($data['payment_id'])) {
            Log::channel('pay')->error('payment_id field not set');
            return response('', 404);
        }

        $order_id = $data['payment_id'];

        Log::channel('pay')->info('Start processing 1Plat transaction with id ' . $order_id);

        try {
            DB::beginTransaction();

            /** @var PayoutTransaction $transaction */
            $transaction = PayoutTransaction::where('o_tr_id', $order_id)
                ->whereIn('status_id', [
                    PayoutTransaction::STATUS_ONEPLAT_PENDING,
                    PayoutTransaction::STATUS_ONEPLAT_FAIL,
                ])
                ->lockForUpdate()
                ->first();

            if ($transaction?->status_id === PayoutTransaction::STATUS_ONEPLAT_FAIL) {
                Log::channel('pay')->warning('Transaction with id ' . $order_id . ' already failed found. Processing');
            }

            if ($transaction === null) {
                if (PayoutTransaction::where('id', $order_id)
                        ->where('status_id', PayoutTransaction::STATUS_ONEPLAT_SUCCESS)
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

            if ($data['status'] === -2) {
                Log::channel('pay')->error('status:' . $data['status']);
                $transaction->status_id = PayoutTransaction::STATUS_ONEPLAT_FAIL;
                $transaction->coment = ' status error -2';
                $transaction->save();
                return 'ok';
            }

            if ($data['status'] !== 1) {
                Log::channel('pay')->info('status:' . $data['ticket']['status']);
                $transaction->coment = $data['status'] ?? 'pending status';
                $transaction->save();
                return 'ok';
            }

            $amount = $data['amount_to_pay'];
            if ($amount < $data['amount']) {
                $amount = $data['amount'];
            }

            $currency = Wallet::CURRENCY_RUB;
            $transaction->fee = $data['amount_to_pay'] - $data['amount_to_shop'];

            Payin::completeTransaction($user, $transaction, $amount, PayoutTransaction::STATUS_ONEPLAT_SUCCESS, $currency);

        } catch (\Throwable $e) {
            Hlog::logException($e, 'pay');
        } finally {
            DB::commit();
        }

        return response('ok', 200);
    }
}
