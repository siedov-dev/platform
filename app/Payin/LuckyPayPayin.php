<?php

namespace App\Payin;

use App\ApplicationForPayment;
use App\Models\PaymentSystems;
use App\MUser;
use App\PaymentsAPI\LuckyPay\LuckyPay;
use App\PaymentsAPI\StreamPay;
use App\PaymentsAPI\Turcode;
use App\PayoutTransaction;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class LuckyPayPayin extends Payin
{

    protected LuckyPay $api;

    public function __construct(MUser $user)
    {
        parent::__construct($user);
        $this->type = self::TYPE_LUCKYPAY;
        $this->name = 'LuckyPay';
        $this->api = new LuckyPay();
    }

    public function process($amount, $method, array $data, ?PayoutTransaction $transaction, int $method_id)
    {
        if ($transaction === null) $transaction = new PayoutTransaction;

        $transaction->user_id = $this->user->id;
        $transaction->hall_id = $this->user->hall_id;
        $transaction->date = date(format: 'Y-m-d H:i:s');
        $transaction->sum = $amount;
        $transaction->currency = $this->user->getWallet($this->user->currency)->getCurrencyName();
        $transaction->operation = class_basename(Payin::class) . ' ' . $this->name;
        $transaction->status_id = PayoutTransaction::STATUS_LUCKYPAY_PENDING;
        $transaction->method = $method;
        $transaction->save();

        $method_name = explode('.', $method)[1];

        $resp = $this->api->deposit($transaction->getId(), $amount, $method_name);
        if (empty($resp) || !$resp['success']) {
            $this->_error = $resp['message']?? 'system error';
            return false;
        }

        return ['url' => $resp['form_data']['form_url'] ?? ''];
    }

    public function getStatusCodes(): array
    {
        return [
            PayoutTransaction::STATUS_LUCKYPAY_PENDING,
            PayoutTransaction::STATUS_LUCKYPAY_SUCCESS,
            PayoutTransaction::STATUS_LUCKYPAY_FAIL,
        ];
    }

    public function getFormRequisites($method, ?PaymentSystems $pay_method): string
    {
//        // update min|max amounts
//        $method_name = explode('.', $method)[1];
//        if (explode(":", $method_name)[0] === '2') {
//            $method_name = explode(":", $method_name)[1];
//            $api = new LuckyPay(2);
//        } else {
//            $api = new LuckyPay();
//        }
//        $methods = collect($api->methods()['payment_methods']);
//        $method_params = $methods->where('method_id', $method_name)->first();
//        if (!empty($method_params) && !empty($method_params['min_buy_amount'])) {
//            if ($pay_method->getMinSum() != $method_params['min_buy_amount']) {
//                $pay_method->min_sum = $method_params['min_buy_amount'];
//            }
//            if ($pay_method->getMaxSum() != $method_params['max_buy_amount']) {
//                $pay_method->max_sum = $method_params['max_buy_amount'];
//            }
//            $pay_method->save();
//        }
        return parent::getFormRequisites($method, $pay_method);
    }

}
