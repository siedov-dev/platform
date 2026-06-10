<?php

namespace App\Payin;

use App\Models\PaymentSystems;
use App\MUser;
use App\PaymentsAPI\ShadowPays\ShadowPays;
use App\PayoutTransaction;
use Illuminate\Support\Facades\Auth;

class ShadowPaysPayin extends Payin
{
    public function __construct(MUser $user)
    {
        parent::__construct($user);
        $this->name = "ShadowPays";
        $this->type = self::TYPE_SHADOWSPAYS;
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
        $transaction->operation = 'Пополнение счета через ShadowPays';
        $transaction->status_id = PayoutTransaction::STATUS_SHADOWPAYS_PENDING;
        $transaction->save();

        $api = new ShadowPays();
        $method = explode('.', $method)[1];

        $resp = $api->deposit($transaction, $data['phone']);
        if (empty($resp['FormURL'])) {
            $this->_error = $resp['Message']?? 'system error';
            return false;
        }

        $transaction->o_tr_id = $resp['OrderId']??'';
        $transaction->save();
        return ['url' => $resp['FormURL']];
    }

    public function getStatusCodes(): array
    {
        return [
            PayoutTransaction::STATUS_SHADOWPAYS_PENDING,
            PayoutTransaction::STATUS_SHADOWPAYS_SUCCESS,
            PayoutTransaction::STATUS_SHADOWPAYS_FAIL,
        ];
    }

    public function getFormRequisites($method, ?PaymentSystems $pay_method): string
    {
        return view('pay.requisites.payin.with-phone', ['method' => $method, 'pay_method' => $pay_method])->render();
    }
}
