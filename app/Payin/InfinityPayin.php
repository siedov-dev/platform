<?php

namespace App\Payin;

use App\MUser;
use App\PaymentsAPI\InfinityPay\InfinityPay;
use App\PaymentsAPI\NirvanaPay\NirvanaPay;
use App\PayoutTransaction;

class InfinityPayin extends Payin
{

    protected InfinityPay $api;

    public function __construct(MUser $user)
    {
        parent::__construct($user);
        $this->name = 'InfinityPay';
        $this->type = self::TYPE_INFINITY_PAY;
        $this->api = new InfinityPay();
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
        $transaction->status_id = PayoutTransaction::STATUS_INFINITY_PENDING;
        $transaction->method = $method;
        $transaction->save();

        $response = $this->api->deposit(
            tr_id: $transaction->getId(),
            amount: $amount,
            currency: $user->getCurrencyName(),
        );

        if (!empty($response['data']['payment_url'])) {
            return ['url' => $response['data']['payment_url']];
        } else {
            $this->_error = 'System error. Please try again later.';

            $transaction->status_id = PayoutTransaction::STATUS_NIRVANA_FAIL;
            $transaction->coment = "can't get url";
            $transaction->save();

            return false;
        }

    }

    public function getStatusCodes(): array
    {
        return [
            PayoutTransaction::STATUS_INFINITY_PENDING,
            PayoutTransaction::STATUS_INFINITY_SUCCESS,
            PayoutTransaction::STATUS_INFINITY_FAIL,
        ];
    }

}
