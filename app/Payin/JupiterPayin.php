<?php

namespace App\Payin;

use App\Models\PaymentSystemGroup;
use App\Models\PaymentSystems;
use App\MUser;
use App\PaymentsAPI\Jupiter\Jupiter;
use App\PaymentsAPI\PayOnHost;
use App\PaymentsAPI\Repay\Repay;
use App\PaymentsAPI\XPayPro\XPayPro;
use App\PayoutTransaction;
use App\UserWallet;

class JupiterPayin extends Payin
{

    protected Jupiter $api;

    public function __construct(MUser $user)
    {
        parent::__construct($user);
        $this->name = 'Jupiter';
        $this->type = self::TYPE_JUPITER;
        $this->api = new Jupiter();
    }

    public function process($amount, $method, array $data, ?PayoutTransaction $transaction, int $method_id): bool|array
    {
        if ($transaction === null) $transaction = new PayoutTransaction;
        $pay_method = explode('.', $method)[1];

        $user = MUser::getAuth();

        $transaction->user_id = $user->id;
        $transaction->hall_id = $user->hall_id;
        $transaction->date = date(format: 'Y-m-d H:i:s');
        $transaction->sum = $amount;
        $transaction->currency = $user->getCurrencyName();
        $transaction->operation = class_basename(Payin::class) . ' ' . $this->name;
        $transaction->status_id = PayoutTransaction::STATUS_JUPITER_PENDING;
        $transaction->method = $method;
        $transaction->save();

        $response = $this->api->payin(
            tr: $transaction,
            amount: $amount,
            method: $pay_method,
            data: $data,
            currency: $user->getCurrencyName(),
        );

        if (!empty($response['redirect_url'])) {

            $transaction->o_tr_id = $response['order_id'];
            $transaction->save();

            return ['url' => $response['redirect_url'], 'in_frame' => true];
        } else {
            $this->_error = 'System error. Please try again later.';

            $transaction->status_id = PayoutTransaction::STATUS_JUPITER_FAIL;
            $transaction->coment = "deposit error";
            $transaction->save();

            return false;
        }

    }

    public function getFormRequisites($method, ?PaymentSystems $pay_method): string
    {
        return view('pay.requisites.payin.phone-mail', ['method' => $method])->render();
//        return parent::getFormRequisites($method);
    }

    public function getStatusCodes(): array
    {
        return [
            PayoutTransaction::STATUS_JUPITER_PENDING,
            PayoutTransaction::STATUS_JUPITER_SUCCESS,
            PayoutTransaction::STATUS_JUPITER_FAIL,
        ];
    }

}
