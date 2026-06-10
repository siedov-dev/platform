<?php

namespace App\PaymentsAPI\CrossPay;

use App\Helpers\Currency;
use App\MUser;
use App\Payin\Payin;
use App\PayoutTransaction;
use App\UserWallet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * @see https://docs.crosspay.net/#operation/orderPayinHosted
 * @deprecated
 */
final class CrossPay
{
    private string $public_key = "NnVSmeWNtLdlVvcWw3MhwOsKieFzWr6VbplV3NfwWGxNF6Qi";
    private string $secret_key = "13CBXF4aK6J3f6fovtbKk69ppeLKvkQ12I4CsMBSBvhjw6RX";
    private string $url = "https://api2.crosspay.net/api/";

    public function balances()
    {
        $method = "v2/balances";
        $data = [];

        return $this->query($method, 'POST', $data);
    }

    public function payin(string $order_id, float $amount, string $currency = 'KZT', $requsites = [], $trusted = false, $method = "")
    {
        $endpoint = "v2/order/payin";
        $data = [
            'order_id' => $order_id,
            'currency' => mb_strtolower($currency),
            'wallet_type' => 'ecom',
            'amount' => $amount,
            'payway' => 'card',
            'callback' => 'https://krakenfn.com/api/pay/crosspay',
            'success_url' => route('pay.success'),
            'fail_url' => route('pay.fail'),
        ];


        $data['attributes']['traffic_type'] = $trusted ? 'trusted' : 'untrusted';
        if ($currency !== 'EUR' && $method !== 'KZT_mobile') {
            $data['client']['name'] = $requsites['card_holder'];
        }
        $data['client']['ip'] = \request()->ip();
        $data['client']['id'] = (string)Auth::id();

        if ($currency === 'EUR' || $method === 'KZT_mobile') {
            $data['client']['email'] = $requsites['email'];
            $data['client']['phone'] = preg_replace('#[\D]+#', '', $requsites['phone']);

            if (!$trusted) $data['auth_currency'] = 'usd';
        }

        return $this->query($endpoint, 'POST', $data);
    }

    public function payout(string $order_id, float $amount, string $currency, $card_number, $method = "", $requisites = [])
    {
        $user = MUser::getAuth();

        $method = "v2/order/payout";
        $data = [
            'order_id' => $order_id,
            'currency' => mb_strtolower($currency),
            'wallet_type' => 'ecom',
            'amount' => $amount,
            'payway' => 'card',
        ];

        if ($method === 'CrossPay.KZT_mobile') {
            $data['client']['name'] = $user?->personalData()?->getName() . ' ' . $user?->personalData()?->getSurname();

            $data['counterparty']['type'] = 'mobile';
            $data['counterparty']['phone_number'] = 'mobile';
        } else {
            $data['wallet'] = $card_number;
        }

        return $this->query($method, 'POST', $data);
    }

    public function status(string $id)
    {
        $method = "v2/order/status";
        $data = [
            'order_uuid' => $id,
        ];

        return $this->query($method, 'POST', $data);
    }

    private function sign($content): string
    {
        return hash_hmac('sha256', $content, $this->secret_key);
    }

    private function query($method, $httpMethod, array $data = [])
    {
        $url = $this->url . $method;

        $ch = curl_init();

        $payload = json_encode($data, JSON_FORCE_OBJECT);

        $sign = $this->sign($payload);

        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: ' . $this->public_key,
            'Signature: ' . $sign,
        ]);

        if (strtolower($httpMethod) === 'post') {

            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);

        } else {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
            if (!empty($data)) {
                $url .= '?' . http_build_query($data);
            }
        }

        Log::channel('pay')->info(__CLASS__ . ' request: ' . $url);
        Log::channel('pay')->info(var_export($data, true));
        Log::channel('pay')->info(__CLASS__ . ' request body: ' . $url);
        Log::channel('pay')->info($payload);

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);

        curl_setopt($ch, CURLOPT_USERAGENT, 'KrakenCassino');
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_MAXREDIRS, 10);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

        curl_setopt($ch, CURLOPT_VERBOSE, true);

        $verbose = fopen('php://temp', 'w+');
        curl_setopt($ch, CURLOPT_STDERR, $verbose);

        $json = curl_exec($ch);
        $err = curl_error($ch);
        $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $resp = json_decode($json, true);

        rewind($verbose);
        $verboseLog = stream_get_contents($verbose);

        Log::channel('pay')->info('curl verbose');
        Log::channel('pay')->info(htmlspecialchars($verboseLog));

        if ($err) {
            Log::channel('pay')->error($err);
        }

        Log::channel('pay')->info(__CLASS__ . ' resp:');
        Log::channel('pay')->info('code:' . $httpcode);
        Log::channel('pay')->info($json);
        Log::channel('pay')->info(var_export($resp, true));
        return $resp;
    }

    public static function depositCallback(Request $request)
    {
        Log::channel('pay')->info(__CLASS__ . ' callback deposit request');
        Log::channel('pay')->info(var_export($request->all(), true));
//        $api = new CrossPay();

        $data = $request->all();

        if (!isset($data['data'][0]['order_id'])) {
            Log::channel('pay')->error('Order_Id field not set');
            return response('', 500);
        }

//        if ($api->sign($request->getContent()) !== $request->header('X-REQUEST-SIGNATURE')) {
//            Log::channel('pay')->error('wrong hash');
//            Log::channel('pay')->error('signature ' . $request->header('X-REQUEST-SIGNATURE'));
//            return response('wrong hash', 500);
//        }

        Log::channel('pay')->info('Start processing CrossPay transaction with id ' . $data['data'][0]['order_id']);

        DB::beginTransaction();
        try {
            /** @var PayoutTransaction $transaction */
            $transaction = PayoutTransaction::where('id', $data['data'][0]['order_id'])
                ->whereIn('status_id', [
                    PayoutTransaction::STATUS_CROSSPAY_PENDING,
                    PayoutTransaction::STATUS_CROSSPAY_FAIL,
                ])
                ->lockForUpdate()
                ->first();

            if ($transaction?->getStatusId() === PayoutTransaction::STATUS_CROSSPAY_FAIL)  {
                Log::channel('pay')->error('Transaction with id ' . $data['data'][0]['order_id'] . ' in failed state found. Request: ' . PHP_EOL . var_export($request->all(), true));
            }

            if ($transaction === null) {
                DB::commit();
                if (PayoutTransaction::where('id', $data['data'][0]['order_id'])
                        ->where('status_id', PayoutTransaction::STATUS_CROSSPAY_SUCCESS)
                        ->first() !== null) {
                    Log::channel('pay')->error('Transaction with id ' . $data['data'][0]['order_id'] . ' is already complete found. Request: ' . PHP_EOL . var_export($request->all(), true));
                    return response('ok', 200);
                }
                Log::channel('pay')->error('Transaction with id ' . $data['data'][0]['order_id'] . ' is not found. Request: ' . PHP_EOL . var_export($request->all(), true));
                return response('transaction not found', 404);
            }

            // Find user which create this transaction.
            /** @var MUser $user $user */
            $user = MUser::where('id', $transaction->user_id)->first();

            if ($user === null) {
                DB::commit();
                Log::channel('pay')->error('User of the transaction is not found.');
                return response('user not found', 500);
            }
            if ($data['data'][0]['order_type'] !== 'payin') {
                DB::commit();
                return 'ok';
            }

            if ($data['data'][0]['status'] === 'canceled' || $data['data'][0]['status'] === 'rejected') {
                Log::channel('pay')->error('status:' . $data['data'][0]['status']);
                $transaction->status_id = PayoutTransaction::STATUS_CROSSPAY_FAIL;
                $transaction->coment = $data['event'] ?? ' event error';
                $transaction->save();
                DB::commit();
                return 'ok';
            }

            if ($data['data'][0]['status'] !== 'completed') {
                DB::commit();
                return 'ok';
            }

            $amount = $data['data'][0]['amount_from'];

            $currency = UserWallet::getCurrencyCode(mb_strtoupper($data['data'][0]['currency_from']));

            if ($currency === UserWallet::CURRENCY_USD) {
                $currency = UserWallet::CURRENCY_EUR;
                if ($amount > $transaction->sum * 0.95 && $amount < $transaction->sum * 1.1) {
                    $amount = $transaction->sum;
                } else {
                    $amount = Currency::convert($amount, 'USD', 'EUR');
                }
            }

            $transaction->fee = $data['data'][0]['amount_fee'] ?? 0;
            $transaction->card_number = $data['data'][0]['card_mask'] ?? '';

            Payin::completeTransaction($user, $transaction, $amount, PayoutTransaction::STATUS_CROSSPAY_SUCCESS, $currency);
        } catch (\Exception $e) {
            DB::commit();
            Log::channel('stack')->error($e->getMessage());
        }

        DB::commit();

        return response('ok', 200);
    }
}
