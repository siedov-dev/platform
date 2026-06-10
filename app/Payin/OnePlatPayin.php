<?php

namespace App\Payin;

use App\MUser;
use App\PaymentsAPI\InfinityPay\InfinityPay;
use App\PaymentsAPI\NirvanaPay\NirvanaPay;
use App\PaymentsAPI\OnePlat\OnePlat;
use App\PayoutTransaction;

class OnePlatPayin extends Payin
{

    protected OnePlat $api;

    public function __construct(MUser $user)
    {
        parent::__construct($user);
        $this->name = 'OnePlat';
        $this->type = self::TYPE_ONEPLAT_PAY;
        $this->api = new OnePlat();
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
        $transaction->status_id = PayoutTransaction::STATUS_ONEPLAT_PENDING;
        $transaction->method = $method;
        $transaction->save();
        $pay_method = explode('.', $method)[1];
        $pay_method = explode('_', $pay_method)[0];

        $response = $this->api->deposit(
            tr_id: $transaction->getId(),
            user: $user,
            amount: $amount,
            method: $pay_method,
        );

        if (!empty($response['url'])) {
            $transaction->o_tr_id = $response['payment']['id'];
            $transaction->save();
            return ['url' => $response['url'], 'in_frame' => true];
        } else {
            $this->_error = $response['error']?? 'System error. Please try again later.';

            $transaction->status_id = PayoutTransaction::STATUS_ONEPLAT_FAIL;
            $transaction->coment = $response['error']?? "can't get url";
            $transaction->save();

            return false;
        }

    }

    public function getStatusCodes(): array
    {
        return [
            PayoutTransaction::STATUS_ONEPLAT_PENDING,
            PayoutTransaction::STATUS_ONEPLAT_SUCCESS,
            PayoutTransaction::STATUS_ONEPLAT_FAIL,
        ];
    }

}
