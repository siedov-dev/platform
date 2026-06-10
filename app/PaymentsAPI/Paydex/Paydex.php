<?php

namespace App\PaymentsAPI\Paydex;

use App\MUser;
use App\Payin\Payin;
use App\PaymentsAPI\AezaPay\AezaPay;
use App\PayoutTransaction;
use App\UserWallet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * @url https://merchant.paydex.co.uk/docs/ru/integration/auth
 */
class Paydex
{
    private string $_pub_url = "https://pay.paydex.co.uk/public-api";
    private string $_priv_url = "https://pay.paydex.co.uk";

    private string $_username = "coma_BdbSKEeoMdZh3bgE";
    private string $_password = "bRdv2zFveVP4oXtguFgF9u7v2gkE8huV3kWgY54jOfc";

//    private string $_pub_key = "pk_test_DCD1az1rRpqE-8HZBz59G_o_jvKdr4gP69xl0sqwKNs";
    private string $_pub_key = "pk_live_O4N7i0HvVRkZGUWnol6Ib19A3xpKao71GY_jWCpQUQ4";
//    private string $_secret_key = "sk_test_PLEPlKkP-vSD5vM9DECyuC6-DStD4yivdCLHLKt4W70";
    private string $_secret_key = "sk_live_mM8x50hQQ0lZeeI5VkS5Unr7cjge_NAzleZyW49G2PI";

    /**
     * profile info
     * @return mixed
     */
    public function account(): ?array
    {
        $url = $this->_priv_url . '/account/profile';
        $data = [];
        return $this->query($url, 'get', $data);
    }

    /**
     * account balances
     * @return mixed
     */
    public function balances(): ?array
    {
        $url = $this->_priv_url . '/account/balances';
        $data = [];
        return $this->query($url, 'get', $data);
    }

    public function paymentOptions()
    {
        $url = $this->_priv_url . '/account/payment-options';
        $data = [];
        return $this->query($url, 'get', $data);
    }

    public function payoutOptions()
    {
        $url = $this->_priv_url . '/account/payout-options';
        $data = [];
        return $this->query($url, 'get', $data);
    }

    public function paymentServices($id = null)
    {
        $url = $this->_priv_url . '/payment-services';
        if ($id !== null) $url .= '/' . $id;
        $data = [];
        return $this->query($url, 'get', $data);
    }

    public function payoutServices($id = null)
    {
        $url = $this->_priv_url . '/payout-services';
        if ($id !== null) $url .= '/' . $id;
        $data = [];
        return $this->query($url, 'get', $data);
    }

    public function payment(string $tr_id, float $amount, string $method, array $requisites, string $currency = 'KZT')
    {
        $url = $this->_pub_url . '/payment-invoices';
        $data = [
            'public_key' => $this->_pub_key,
            'service' => $method,
            'currency' => $currency,
            'amount' => $amount,
            'description' => 'KrakenCassino deposit',
            'reference_id' => $tr_id,
            'customer' => [
                'reference_id' => ''.Auth::id(),
                'name' => $requisites['name'],
                'email' => $requisites['email'],
            ],
        ];
        return $this->query($url, 'post', $data);
    }

    public function payoutPrerequest(string $currency)
    {
        $url = $this->_pub_url . '/payout-prerequest';
        $data = [
            'public_key' => $this->_pub_key,
            'currency' => $currency,
        ];
        return $this->query($url, 'post', $data);
    }

    public function payout(string $tr_id, float $amount, string $method, array $requisites, string $currency = 'KZT')
    {
        $url = $this->_priv_url . '/payout-invoices';
        $data = [
            'data' => [
                'type' => 'payout-invoice',
                'attributes' => [
                    'service' => $method,
                    'currency' => $currency,
                    'amount' => $amount,
                    'reference_id' => $tr_id,
//                    'test_mode' =>  true,
                    'fields' => [
                        'card_number' => $requisites['cardNumber'],
                    ],
                ],
                'metadata' => [
                    'key' => $this->_secret_key,
                ],
            ],
        ];
        return $this->query($url, 'post', $data);
    }

    public function payoutProcess(string $id)
    {
        $url = $this->_priv_url . '/payout-invoices/' . $id . '/process';
        $data = [];
        return $this->query($url, 'post', $data);
    }

    public function payoutStatus(string $id)
    {
        $url = $this->_pub_url . '/payout-invoices/' . $id;
        $data = [];
        return $this->query($url, 'get', $data);
    }

    public function sign($data): string
    {
        return base64_encode(sha1($this->_secret_key . $data . $this->_secret_key, true));
    }

    private function query($method, $httpMethod, array $data = [])
    {
        $url = $method;

        $ch = curl_init();
        if (strtolower($httpMethod) === 'post') {

            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Authorization: Basic ' . base64_encode($this->_username . ':' . $this->_password),
                'Content-Type: application/json',
            ]);
            curl_setopt($ch, CURLOPT_POST, true);
            if (!empty($data)) curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

        } else {

            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Authorization: Basic ' . base64_encode($this->_username . ':' . $this->_password),
                'Content-Type: application/json',
            ]);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
            curl_setopt($ch, CURLOPT_ENCODING, "");
            if (!empty($data)) {
                $url .= '?' . http_build_query($data);
            }
        }

        Log::channel('pay')->info('Paydex request: ' . $url);
        Log::channel('pay')->info(json_encode($data));
        Log::channel('pay')->info(var_export($data, true));

        curl_setopt($ch, CURLOPT_HEADER, false);

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);

        curl_setopt($ch, CURLOPT_USERAGENT, 'KrakenCassino');
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_MAXREDIRS, 10);


        curl_setopt($ch, CURLOPT_VERBOSE, true);
        $verbose = fopen('php://temp', 'w+');
        curl_setopt($ch, CURLOPT_STDERR, $verbose);

        $text = curl_exec($ch);
        $err = curl_error($ch);
        $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        rewind($verbose);
        $verboseLog = stream_get_contents($verbose);

        Log::channel('pay')->info('curl verbose');
        Log::channel('pay')->info(htmlspecialchars($verboseLog));

        $resp = json_decode($text, true);
        if ($err) {
            Log::channel('pay')->error($err);
        }

        Log::channel('pay')->info('Paydex resp:');
        Log::channel('pay')->info('code:' . $httpcode);
        Log::channel('pay')->info($text);
        Log::channel('pay')->info(var_export($resp, true));
        return $resp;
    }

    public static function depositCallback(Request $request)
    {
        Log::channel('pay')->info('Start processing Paydex ' . PHP_EOL . var_export($request->getContent(), true));
        Log::channel('pay')->info('sign header ' . PHP_EOL . var_export($request->header('X-Signature'), true));

        $data = json_decode($request->getContent(), true)['data'];
        Log::channel('pay')->info('Paydex data' . PHP_EOL . var_export($data, true));

        $trans_id = $data['attributes']['reference_id'];
        if (!$trans_id) {
            Log::channel('pay')->error('reference_id field not set');
            return response('Платеж не найден', 500);
        }

        // if payout
        if ($data['type'] !== 'payment-invoices') {
            DB::commit();
            return response('OK', 200);
        }

        $currency = UserWallet::getCurrencyCode($data['attributes']['currency']);

        $api = new Paydex();
        if ($api->sign($request->getContent()) !== $request->header('X-Signature')) {
            Log::channel('pay')->error('Wrong sign, possible hack');
//            return response('Wrong sign', 500);
        }

        DB::beginTransaction();

        // Find transaction.
        /** @var PayoutTransaction $transaction */
        $transaction = PayoutTransaction::where('id', $trans_id)
            ->where('status_id', PayoutTransaction::STATUS_PAYDEX_PENDING)
            ->lockForUpdate()
            ->first();

        if ($transaction === null) {
            $transaction = PayoutTransaction::where('id', $trans_id)
                ->where('status_id', PayoutTransaction::STATUS_AEZA_PENDING)
                ->first();
            if ($transaction !== null) {
                Log::channel('pay')->error('Transaction with id ' . $trans_id . ' already completed. Request: ' . PHP_EOL . var_export($request->all(), true));
                DB::commit();
                return response('OK', 200);
            }
            Log::channel('pay')->error('Transaction with id ' . $trans_id . ' is not found. Request: ' . PHP_EOL . var_export($request->all(), true));
            DB::commit();
            return response('OK', 200);
        }

        if ($data['attributes']['status'] === 'process_failed' || $data['attributes']['status'] === 'expired') {
            $transaction->status_id = PayoutTransaction::STATUS_PAYDEX_FAIL;
            $transaction->coment = $data['attributes']['status'];
            $transaction->save();
            DB::commit();
            return response('OK', 200);
        }

        if ($data['attributes']['status'] !== 'processed') {
            DB::commit();
            return response('OK', 200);
        }

        // Find user which create this transaction.
        /** @var MUser $user $user */
        $user = MUser::where('id', $transaction->user_id)->first();

        if ($user === null) {
            Log::channel('pay')->error('User of the transaction is not found.');
            DB::commit();
            return response('User not found.', 500);
        }

        $amount = $data['attributes']['amount'];

        $fee = $amount * 0.095;

        $transaction->sum = $amount;
        $transaction->fee = round($fee, 2);
        $transaction->total_sum = $amount + $fee;
        $transaction->card_number = $data['payload']['mask'] ?? '';

        try {
            Payin::completeTransaction($user, $transaction, $amount, PayoutTransaction::STATUS_PAYDEX_SUCCESS, $currency);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::channel('pay')->error($e->getMessage());
        }

        DB::commit();

        return response('OK', 200);
    }
}
