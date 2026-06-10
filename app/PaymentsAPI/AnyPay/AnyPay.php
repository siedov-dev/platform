<?php

namespace App\PaymentsAPI\AnyPay;

use App\MUser;
use App\Payin\Payin;
use App\PaymentsAPI\Payadmit\Payadmit;
use App\PayoutTransaction;
use App\UserWallet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;

class AnyPay
{
    protected string $secret_key = 'b8dffc11f68ba655c7cbdb4f154338f6dde50800ef318887';
    protected string $secret_key_untrusted = '0323a0605c964e9c3d8604f95dc89558e8209deae79d63ad';
    protected string $api_key = 'bcf97ef0-48b8-013c-278a-317ef413e695';
    protected string $api_key_untrusted = 'a47a35b0-5aca-013c-a4b7-0c19b9e62b85';

    public function __construct($trusted = true)
    {
        if (!$trusted) {
            $this->secret_key = $this->secret_key_untrusted;
            $this->api_key = $this->api_key_untrusted;
        }
    }

    public function deposit(string $tr_id, float $amount, string $currency, array $requisites)
    {
        $order_data = [
            'api_key' => $this->api_key,
            'expiration' => now()->addHours(3)->format('Y-m-d H:i'),
            'amount'=> round($amount, 2),
            'currency'=> $currency,
            'description'=> 'kraken deposit',
            'reference'=> $tr_id,
            'success_url'=> route('pay.success'),
            'failure_url'=> route('pay.fail'),
            'lang'=> App::getLocale(),
            'params' => [
                'user_email' => $requisites['email'],
            ],
        ];

        $data = base64_encode(json_encode($order_data));
        $sign = hash_hmac('md5', $data, $this->secret_key);

        return view('pay.anypay', [
            'data' => $data,
            'sign' => $sign
        ])->render();
    }

    public function checkSign($data): bool
    {
        return hash_hmac('md5', $data['data'], $this->secret_key) === $data['sign'];
    }

    public static function depositCallback(Request $request)
    {
        Log::channel('pay')->info(__CLASS__.' callback deposit request');
        Log::channel('pay')->info(var_export($request->getContent(), true));
        Log::channel('pay')->info(var_export($request->all(), true));

        $data = $request->all();

        if (!isset($data['data'])) {
            Log::channel('pay')->error('data field not set');
            return response('data field not set', 500);
        }

        $api = new AnyPay();

        if ($api->checkSign($data)) {
            Log::channel('pay')->error('check sign ok');
        } else {
            $api = new AnyPay(false);
            if ($api->checkSign($data)) {
                Log::channel('pay')->error('check sign ok');
            } else {
                Log::channel('pay')->error('check sign fail');
            }
        }

        $data = json_decode(base64_decode($_POST['data']), true);

        if (!isset($data['reference'])) {
            Log::channel('pay')->error('reference field not set');
            return response('reference field not set', 500);
        }
        Log::channel('pay')->info('Start processing AnyPay transaction with id ' . $data['reference']);

        /** @var PayoutTransaction $transaction */
        $transaction = PayoutTransaction::where('id', $data['reference'])
            ->where('status_id', PayoutTransaction::STATUS_ANYPAY_PENDING)
            ->first();

        if ($transaction === null) {
            if (PayoutTransaction::where('id', $data['reference'])
                    ->where('status_id', PayoutTransaction::STATUS_ANYPAY_SUCCESS)
                    ->first() !== null) {
                Log::channel('pay')->error('Transaction with id ' . $data['reference'] . ' is already complete found. Request: ' . PHP_EOL . var_export($request->all(), true));
                return response('OK', 200);
            }
            Log::channel('pay')->error('Transaction with id ' . $data['reference'] . ' is not found. Request: ' . PHP_EOL . var_export($request->all(), true));
            return response('transaction not found', 404);
        }

        // Find user which create this transaction.
        /** @var MUser $user $user */
        $user = MUser::where('id', $transaction->user_id)->first();

        if ($user === null) {
            Log::channel('pay')->error('User of the transaction is not found.');
            return response('user not found', 500);
        }

        if ($data['status'] == 99 || $data['status'] == 98) {
            Log::channel('pay')->error('Transaction status ' . $data['status']);
            $transaction->status_id = PayoutTransaction::STATUS_ANYPAY_FAIL;
            $transaction->coment = $data['state'];
            $transaction->save();
            return 'ok';
        }

        if ($data['status'] != 3) {
            $transaction->coment = $data['status_name']?? '';
            $transaction->save();
            return 'ok';
        }

        $amount = $data['operation_amount'];

        $currency = UserWallet::getCurrencyCode($data['currency']);
        $transaction->fee = $data['commission']?? 0;

        Payin::completeTransaction($user, $transaction, $amount, PayoutTransaction::STATUS_ANYPAY_SUCCESS, $currency);

        return 'OK';
    }

}
