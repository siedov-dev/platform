<?php

namespace App\PaymentsAPI\PayCrown;

use App\MUser;
use App\Payin\Payin;
use App\PayoutTransaction;
use App\UserWallet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

final class PayCrown
{
    private string $client_id = "qt3f7bnj10m0Zfv2uwd4vy0fUoxdz8fCvKl3Whcq";
    private string $client_secret = "L3x2yU59iDTXj6udKKqwxftoFcgcISqg4gYHPi8N";
    private string $endpoint = "https://new.paycrown.net";

    public function deposit(float $amount, PayoutTransaction $transaction): string
    {
        $url = $this->endpoint . "/payments/create";

        $data = [
            'client_id' => $this->client_id,
            'amount' => number_format($amount, 2, '.', ''),
            'order_id' => $transaction->getId(),
            'user_id' => $transaction->getUserId(),
            'success_url' => route('pay.success'),
            'fail_url' => route('pay.fail'),
            'currency' => UserWallet::getIntCodeById(UserWallet::getCurrencyCode($transaction->getCurrency())),
        ];  

        return $url . '?' . http_build_query($data);
    }

    public function payout($transactionId, $amount, $currency, $cardNum, $paySys, $requisites)
    {
        // $url = $this->endpoint . "/api/v1/addRefund";

        // $token = $this->getAuthToken();
        // if(!$token) return false;

        // $data = [
        //     'token' => $token,
        //     'client_id' => $this->client_id,
        //     'currency' => $this->getCurrencyISOCode($currency),
        //     'amount' => number_format($amount, 2, '.', ''),
        //     'comment' => 'Kraken Casino withdraw '.$cardNum,
        //     'internal_id' => $transactionId,
        //     'card' => $cardNum,
        //     'service' => 'bank',
        //     ''
        // ];
    }

    public function getCurrencyISOCode($currencyId) {
        $codes = [
            1 => 643,
            2 => 980,
            3 => 840,
            4 => 978,
        ];

        return $codes[$currencyId];
    }

    public function getAuthToken() {

        $response = Http::asForm()->withHeaders(['Accept' => 'application/json'])->post('https://paycrown.net/oauth/token', [
            'client_id' => $this->client_id,
            'client_secret' => $this->client_secret,
            'grant_type' => 'client_credentials',
        ]);
        
        Log::channel('payout')->info(__CLASS__ . ' get auth token request');

        $response = $response->json();
        if(isset($response['access_token'])) return $response['access_token'];

        Log::channel('payout')->info(__CLASS__ . ' get auth token failed');
        Log::channel('payout')->info(var_export($response->json(), true));

        return false;

    }

    private function sign(string $amount): string
    {
        return base64_encode(sha1($amount . "|" . $this->client_id . "|" . $this->client_secret));
    }

    public function checkSign(string $amount, string $sign): bool
    {
        return $this->sign($amount) === $sign;
    }

    public static function depositCallback(Request $request)
    {
        Log::channel('pay')->info(__CLASS__ . ' callback deposit request');
        Log::channel('pay')->info(var_export($request->getUri(), true));
        Log::channel('pay')->info(var_export($request->getContent(), true));
        Log::channel('pay')->info(var_export($request->all(), true));
//        $api = new CrossPay();

        $data = json_decode($_POST["payment"], true);

        if (!isset($data['order_id'])) {
            Log::channel('pay')->error('order_id field not set');
            return response('', 500);
        }

        $api = new PayCrown();

        if (!$api->checkSign($data['amount'], $data['signature'])) {
            Log::channel('pay')->error('wrong hash');
//            return response('wrong hash', 500);
        }

        Log::channel('pay')->info('Start processing PayCrown transaction with id ' . $data['order_id']);

        /** @var PayoutTransaction $transaction */
        $transaction = PayoutTransaction::where('id', $data['order_id'])
            ->where('status_id', PayoutTransaction::STATUS_PAYCROWN_PENDING)
            ->first();

        if ($transaction === null) {
            if (PayoutTransaction::where('id', $data['order_id'])
                    ->where('status_id', PayoutTransaction::STATUS_PAYCROWN_SUCCESS)
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

        if ($data['status'] === 'fail') {
            Log::channel('pay')->error('status:' . $data['status']);
            $transaction->status_id = PayoutTransaction::STATUS_PAYCROWN_FAIL;
            $transaction->coment = $data['comment'] ?? ' event error';
            $transaction->save();
            return 'ok';
        }

        if ($data['status'] !== 'success') {
            return 'ok';
        }

        $amount = $data['amount'];

        $currency = UserWallet::getCurrencyCode($transaction->getCurrency());
        $transaction->fee = $data['amount_fee'] ?? 0;
        $transaction->card_number = $data['card'] ?? '';
        $transaction->o_tr_id = $data['id'] ?? '';

        Payin::completeTransaction($user, $transaction, $amount, PayoutTransaction::STATUS_PAYCROWN_SUCCESS, $currency);

        return response('ok', 200);
    }
}
