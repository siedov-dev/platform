<?php

namespace App\Payin;

use App\Models\PaymentSystems;
use App\MUser;
use App\PaymentsAPI\Chasse\Chasse;
use App\PayoutTransaction;
use Illuminate\Support\Facades\Auth;

class ChassePayin extends Payin
{
    public function __construct(MUser $user)
    {
        parent::__construct($user);
        $this->name = "Chasse";
        $this->type = self::TYPE_CHASSE;
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
        $transaction->operation = 'Пополнение счета через Chasse';
        $transaction->status_id = PayoutTransaction::STATUS_CHASSE_PENDING;
        $transaction->save();


        $api = new Chasse();
        $resp = $api->payin($transaction->id, $amount, $currency, $data);

        dd($resp);

        // if (!isset($resp['status']) || $resp['status'] !== 'accepted') {
        //     $this->_error = $resp['failCause']?? 'system error';
        //     return false;
        // }

        // return ['url' => $resp['paymentURL']];
    }

    public function getStatusCodes(): array
    {
        return [
            PayoutTransaction::STATUS_CHASSE_PENDING,
            PayoutTransaction::STATUS_CHASSE_SUCCESS,
            PayoutTransaction::STATUS_CHASSE_FAIL,
        ];
    }

    public function getFormRequisites($method, ?PaymentSystems $pay_method): string
    {
        return view('pay.requisites.payin.chasse')->render();
    }
}
