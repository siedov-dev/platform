<?php

namespace App\Payin;

use App\Models\PaymentSystems;
use App\MUser;
use App\PaymentsAPI\ChatlPay\ChatlPay;
use App\PayoutTransaction;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ChatlPayPayin extends Payin
{
    public function __construct(MUser $user)
    {
        parent::__construct($user);
        $this->name = "ChatlPay";
        $this->type = self::TYPE_CHATLPAY;
    }

    public function process($amount, $method, array $data, ?PayoutTransaction $transaction, int $method_id)
    {
        if ($transaction === null) $transaction = new PayoutTransaction();

        $user_id = Auth::id();
        /** @var MUser $user */
        $user = MUser::find($user_id);

        $currency = $this->user->currency;

        $transaction->user_id = $user->id;
        $transaction->hall_id = $user->hall_id;
        $transaction->date = date("Y-m-d H:i:s");
        $transaction->sum = $amount;
        $transaction->currency = $user->getWallet($currency)->getCurrencyName();
        $transaction->operation = 'Пополнение счета через ChatlPay';
        $transaction->status_id = PayoutTransaction::STATUS_CHATLPAY_PENDING;
        $transaction->save();

        $api = new ChatlPay();
        $method = explode('.', $method)[1];

        if ($method === 'UAH_quasi') {
            $resp = $api->preAuth($user);
            if (empty($resp) || $resp['success'] === false) {
                $this->_error = $resp['msg'] ?? 'system error';
                return false;
            }

            $pub_key = $resp['server_ps'];
            Log::channel('pay')->info('server_ps:'. $resp['server_ps']);
            $shared_key = $api->createSharedKey($pub_key);
            Log::channel('pay')->info('$shared_key:'.var_export($shared_key, true));

            $resp = $api->asyncPay($transaction, $user, $data, $shared_key);

            if (empty($resp) || $resp['success'] === false) {
                $this->_error = $resp['msg'] ?? 'system error';
                return false;
            }

            $transaction->o_tr_id = $resp['bill_id'];
            $transaction->save();

            $details = $api->quasiDetails($resp['bill_id']);
            if (empty($resp) || $resp['success'] === false) {
                $this->_error = $resp['msg'] ?? 'system error';
                return false;
            }

            $info = $api->decryptData($details['dt'], $shared_key);

            Log::channel('pay')->info('decrypted info: '.var_export($info, true));

            if (!empty($info['3ds'])) {
                return ['url' => $resp['payment_url'], 'in_frame' => true];
            }
            return ['url' => route('pay.success')];
        }

        $resp = $api->depositForm($transaction->id, $amount, $user->getWallet($currency)->getCurrencyName(), $user_id);

        if (empty($resp) || $resp['success'] === false) {
            $this->_error = $resp['msg'] ?? 'system error';
            return false;
        }

        $transaction->o_tr_id = $resp['bill_id'];
        $transaction->save();

        return ['url' => $resp['payment_url'], 'in_frame' => true];
    }

    public function getStatusCodes(): array
    {
        return [
            PayoutTransaction::STATUS_CHATLPAY_PENDING,
            PayoutTransaction::STATUS_CHATLPAY_SUCCESS,
            PayoutTransaction::STATUS_CHATLPAY_FAIL,
        ];
    }

    public function getFormRequisites($method, ?PaymentSystems $pay_method): string
    {
        if ($method === 'ChatlPay.UAH_quasi') {
            return view('pay.requisites.payin.card-data', [
                'method' => $method,
            ])->render();
        }
        return parent::getFormRequisites($method, $pay_method);
    }
}
