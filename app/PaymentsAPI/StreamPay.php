<?php

namespace App\PaymentsAPI;

use App\Helpers\Hlog;
use App\MUser;
use App\Payin\Payin;
use App\PayoutTransaction;
use App\UserWallet;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use SodiumException;

/**
 * @docs https://streampay.org/store/377/integration
 */
class StreamPay extends ApiBase
{

    protected string $api_url = 'https://api.streampay.org/api/';
    protected int $store_id = 377;
    public string $public_key = '6b7a6a15016cdede31d3b7f3f5ce3cb8cfaf7c504ac53cb685e9ee8816b2c756';
    protected string $private_key = 'db29165197ff0b06f01e1e99b3900bd01596f0fdbe72422bfb4a1871fd3b76ef6b7a6a15016cdede31d3b7f3f5ce3cb8cfaf7c504ac53cb685e9ee8816b2c756';

    /**
     * @throws SodiumException
     */
    public function in(PayoutTransaction $transaction): object|bool
    {
        return $this->request(
            uri: 'payment/create',
            params: [
                'store_id' => $this->store_id,
                'customer' => (string)$transaction->user_id,
                'external_id' => (string)$transaction->id,
                'description' => 'Balance replenishment',
                'system_currency' => 'USDT',
                'payment_type' => 1,
                'currency' => 'UAH',
                'amount' => (int)$transaction->sum,
                'success_url' => route('pay.success'),
                'fail_url' => route('pay.fail'),
                'cancel_url' => route('pay.close-tab'),
            ]
        );
    }

    /**
     * @throws SodiumException
     */
    protected function request(string $uri, array $params = [], bool $post = true): object|bool
    {
        $this->headers = [
            'signature' => bin2hex(
                sodium_crypto_sign_detached(
                    message: json_encode($params) . now()->setTimezone('UTC')->format('Ymd:Hi'),
                    secret_key: hex2bin($this->private_key),
                )
            ),
        ];

        return parent::request(
            uri: $uri,
            params: $params,
            post: $post,
        );
    }

    /**
     * @throws SodiumException
     * @throws Exception
     */
    public function callback(Request $request)
    {
        Log::channel('pay')->info(__CLASS__ . ' callback deposit request');
        Log::channel('pay')->info(var_export($request->headers->all(), true));
        Log::channel('pay')->info(var_export($request->getUri(), true));
        Log::channel('pay')->info(var_export($request->getContent(), true));
        Log::channel('pay')->info(var_export($request->all(), true));

        $signature = $request->header('Signature');
        if (!$signature) return response('Need signature', 401);

        $signature = hex2bin($signature);

        $params = $request->toArray();
        ksort($params, SORT_STRING);

        $reqContent = http_build_query($params);
        $reqContent = str_replace('%2A', '*', $reqContent);
        $utcNow = now('UTC');

        for ($i = 0; $i < 3; $i++) {
            $toSign = $reqContent . $utcNow->format('Ymd:Hi');

            #echo $toSign . PHP_EOL;

            if (sodium_crypto_sign_verify_detached($signature, $toSign, hex2bin($this->public_key)) !== false) {

                try {
                    DB::beginTransaction();
                    $transaction = PayoutTransaction::where('system_id', Payin::TYPE_STREAMPAY)
                        ->where('o_tr_id', $params['invoice'])
                        ->lockForUpdate()
                        ->first();

                    if ($transaction === null) {
                        return response('transaction not found', status: 404);
                    }
                    if ($transaction->status_id == PayoutTransaction::STATUS_STREAMPAY_SUCCESS) {
                        Log::channel('pay')->info(__CLASS__ . 'transaction already completed #' . $params['invoice']);
                        return response('OK', status: 200);
                    }

                    $this->user = MUser::find($transaction->user_id);

                    switch ($params['status']) {
                        case 'created':
                        case 'awaiting_payment':
                        case 'paid':
                        case 'dispute':
                        case 'refund':

                            $transaction->status_id = PayoutTransaction::STATUS_STREAMPAY_PENDING;
                            break;

                        case 'success':

                            $amount = $params['payed_amount'];
                            $transaction->fee = $amount * 0.055;

                            Payin::completeTransaction($this->user, $transaction, $amount, PayoutTransaction::STATUS_STREAMPAY_SUCCESS, UserWallet::getCurrencyCode($transaction->currency));

                            break;

                        case 'cancel':
                        case 'overdue':

                            $transaction->status_id = PayoutTransaction::STATUS_STREAMPAY_FAIL;
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

            $utcNow->subMinute();
        }

        return response('Invalid signature', status: 403);
    }

}
