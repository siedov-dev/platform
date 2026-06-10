<?php

namespace App\Payin;

use App\Models\PaymentMethodData;
use App\Models\PaymentSystems;
use App\MUser;
use App\PaymentsAPI\Exotic\ExoticApi;
use App\PaymentsAPI\SequoiaPay\SequoiaPayApi;
use App\PayoutTransaction;
use Illuminate\Support\Facades\Auth;

class ExoticPayin extends Payin
{
    public function __construct(MUser $user)
    {
        parent::__construct($user);
        $this->name = "Exotic";
        $this->type = self::TYPE_EXOTIC;
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
        $transaction->operation = 'Пополнение счета через Exotic';
        $transaction->status_id = PayoutTransaction::STATUS_EXOTIC_PENDING;
        $transaction->save();

//        $method_data = PaymentMethodData::getMethodData($method_id);
        $api = new ExoticApi();
        $sub_method = explode('.', $method)[1];
        [$p_method, $p_type] = explode(':', $sub_method);
        $resp = $api->deposit($transaction, $p_method, $p_type);

        if (empty($resp['ok'])) {
            $error = $resp['error']?? 'system error';
            if ($error === 'overloading requisite') $error = "Измените сумму депозита или повторите позже";
            $this->_error = $error;
            return false;
        }

        $transaction->o_tr_id = $resp['payment_id']?? null;
        $transaction->save();
        if ($p_method === 'card') return ['modal_data' => view('pay.exotic-card', ['card' => $resp['number_card'], 'amount' => $amount, 'currency' => $transaction->getCurrency()])->render()];
        if ($p_method === 'sbp') return ['modal_data' => view('pay.exotic-sbp', ['card' => $resp['phone_number'], 'amount' => $amount, 'currency' => $transaction->getCurrency()])->render()];
    }

    public function getStatusCodes(): array
    {
        return [
            PayoutTransaction::STATUS_EXOTIC_PENDING,
            PayoutTransaction::STATUS_EXOTIC_SUCCESS,
            PayoutTransaction::STATUS_EXOTIC_FAIL,
        ];
    }

    public function getFormRequisites($method, ?PaymentSystems $pay_method): string
    {
        return parent::getFormRequisites($method, $pay_method);
    }
}
