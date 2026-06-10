<?php

namespace App\PaymentsAPI\BitZone;

use App\Helpers\Hlog;
use App\MUser;
use App\Payin\Payin;
use App\PayoutTransaction;
use App\Wallet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class BitZone
{
    private string $url = "https://api.paychain.fund";
    private string $key = "383930d8-fee7-4839-a553-d205ad827fce-2926eb16-0601-47d2-9fcc-aa962ca59f69-ea6c7ece-8d22-4877-aeb5-7d65b6f46604";

    public function banks()
    {
        $m = "/payment/trading/banks";
        return $this->query($m, 'get');
    }

    public function deposit(MUser $user, float $amount, string $dep_t, string $tr_id, string $method, string $fingerprint)
    {
        $m = "/payment/trading/pay-in";
        $data = [
//            'bank' => $bank,
            'depositType' => $dep_t,
//            'transferType' => 'any',
//            'method' => $method,
//            'fiatAmount' => $amount,
            'fiatAmountRange' => [
                'min' => $amount,
                'max' => $amount + 10,
                'allowPenny' => true,
            ],
            'extra' => [
                'externalTransactionId' => $tr_id,
                'payerInfo' => [
                    'userAgent' => request()->userAgent(),
                    'ip' => request()->ip(),
                    'userId' => (string)($user->getId()),
                    'fingerprint' => $fingerprint,
                    'registeredAt' => $user->created_at->timestamp,
                ],
            ],
        ];

        return $this->query($m, 'post', $data);
    }

    public function payout(MUser $user, float $amount, string $tr_id, array $requisites)
    {
        $m = "/payment/trading/pay-out";
        $data = [
            'fiatAmount' => $amount,
            'extra' => [
                'externalTransactionId' => $tr_id,
                'payerInfo' => [
                    'userAgent' => $requisites['user_agent'],
                    'ip' => $user->last_ip,
                    'userId' => $user->getId(),
                    'fingerprint' => $requisites['fingerprint'],
                    'registeredAt' => $user->created_at->timestamp,
                ],
            ],
            'requisite' => $requisites['requisite'],
        ];

        return $this->query($m, 'post', $data);
    }

    public function orderInfo(string $id)
    {
        $m = "/payment/trading/" . $id;
        return $this->query($m, 'get');
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
            'x-api-key: ' . $this->key,
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

    public static function depositCallback(Request $request)
    {
        Log::channel('pay')->info(__CLASS__ . ' callback deposit request');
        Log::channel('pay')->info(var_export($request->all(), true));

        $data = $request->all();
        $tr_id = $data['extra']['externalTransactionId'] ?? '';

        if ($data['type'] !== 'pay-in') {
            Log::channel('pay')->info('not a deposit callback');
            return 'ok';
        }

        if (empty($tr_id)) {
            Log::channel('pay')->error('externalTransactionId field not set');
            return response('', 500);
        }

        Log::channel('pay')->info('Start processing BitZone transaction with id ' . $tr_id);

        DB::beginTransaction();
        try {
            /** @var PayoutTransaction $transaction */
            $transaction = PayoutTransaction::where('id', $tr_id)
                ->whereIn('status_id', [
                    PayoutTransaction::STATUS_BITZONE_PENDING,
                    PayoutTransaction::STATUS_BITZONE_FAIL,
                ])
                ->lockForUpdate()
                ->first();

            if ($transaction?->getStatusId() === PayoutTransaction::STATUS_BITZONE_FAIL)  {
                Log::channel('pay')->error('Transaction with id ' . $tr_id . ' in failed state found. Request: ' . PHP_EOL . var_export($request->all(), true));
            }

            if ($transaction === null) {
                if (PayoutTransaction::where('id', $tr_id)
                        ->where('status_id', PayoutTransaction::STATUS_BITZONE_SUCCESS)
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

            if (in_array($data['status'], ['canceled', 'failed'])) {
                Log::channel('pay')->error('status:' . $data['status']);
                $transaction->status_id = PayoutTransaction::STATUS_BITZONE_FAIL;
                $transaction->coment = $data['event'] ?? ' event error';
                $transaction->save();
                return 'ok';
            }

            if (!in_array($data['status'],['completed', 'closed'])) {
                return 'ok';
            }

            $amount = $data['fiatAmount'];

            $currency = Wallet::getCurrencyCode(mb_strtoupper($data['fiatCurrency']));

            $transaction->fee = $amount * ($data['merchantInterestRate'] ?? 0) / 100;

            Payin::completeTransaction($user, $transaction, $amount, PayoutTransaction::STATUS_BITZONE_SUCCESS, $currency);
        } catch (\Exception $e) {
            Hlog::logException($e, 'pay');
        } finally {
            DB::commit();
        }

        return response('ok', 200);
    }
}
