<?php

namespace App\Payin;

use App\Models\PaymentSystemGroup;
use App\Models\PaymentSystems;
use App\Models\UserMoneyStat;
use App\MUser;
use App\PaymentsAPI\DualiPay\DualiPay;
use App\PaymentsAPI\PayOnHost;
use App\PaymentsAPI\Repay\Repay;
use App\PaymentsAPI\XPayPro\XPayPro;
use App\PayoutTransaction;
use App\UserWallet;

class DualiPayin extends Payin
{

    protected DualiPay $api;

    public function __construct(MUser $user)
    {
        parent::__construct($user);
        $this->name = 'DualiPay';
        $this->type = self::TYPE_DUALI_PAY;
        $this->api = new DualiPay();
    }

    public function process($amount, $method, array $data, ?PayoutTransaction $transaction, int $method_id): bool|array
    {
        if ($transaction === null) $transaction = new PayoutTransaction;

        $user = MUser::getAuth();

        $transaction->user_id = $user->id;
        $transaction->hall_id = $user->hall_id;
        $transaction->date = date(format: 'Y-m-d H:i:s');
        $transaction->sum = $amount;
        $transaction->currency = $user->getCurrencyName();
        $transaction->operation = class_basename(Payin::class) . ' ' . $this->name;
        $transaction->status_id = PayoutTransaction::STATUS_DUALIPAY_PENDING;
        $transaction->method = $method;
        $transaction->save();
        $method_id = str_replace('_', " ", explode('.', $method)[1]);
        $method_id = str_replace('-', '/', $method_id);

        $response = $this->api->depositH2h(
            tr_id: $transaction->getId(),
            user_id: $user->getId(),
            method: $method_id,
            amount: $amount,
            currency: $user->getCurrencyName(),
        );

        if (!empty($response['url'])) {

            return ['url' => $response['url']];
        } else {
            $this->_error = 'System error. Please try again later.';

            $transaction->status_id = PayoutTransaction::STATUS_DUALIPAY_FAIL;
            $transaction->coment = "can't get url";
            $transaction->save();

            return false;
        }

    }

    public function getStatusCodes(): array
    {
        return [
            PayoutTransaction::STATUS_DUALIPAY_PENDING,
            PayoutTransaction::STATUS_DUALIPAY_SUCCESS,
            PayoutTransaction::STATUS_DUALIPAY_FAIL,
        ];
    }

}
