<?php

namespace App\Payin;

use App\Models\PaymentSystems;
use App\MUser;
use App\PaymentsAPI\AnyPay\AnyPay;
use App\PayoutTransaction;
use Illuminate\Support\Facades\Auth;

class AnyPayPayin extends Payin
{
    private AnyPay $api;

    public function __construct(MUser $user)
    {
        parent::__construct($user);
        $this->name = "AnyPay";
        $this->type = self::TYPE_ANYPAY;
        $this->api = new AnyPay();
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
        $transaction->operation = 'Пополнение счета через AnyPay';
        $transaction->status_id = PayoutTransaction::STATUS_ANYPAY_PENDING;
        $transaction->save();

        $deps = $user->getDepositsCount();
        $pays = $user->getPayoutsCount();
        $trusted = $deps > 4 && $pays > 0;
        $this->api = new AnyPay($trusted);

        $resp = $this->api->deposit($transaction->id, $amount, $user->getWallet($currency)->getCurrencyName(), $data);
        return ['redirect_form' => $resp];
    }

    public function getFormRequisites($method, ?PaymentSystems $pay_method): string
    {
        return view('pay.requisites.payin.with-email', [
            'method' => $method,
            'email' => Auth::user()?->email ?? '',
        ])->render();
    }

    public function getStatusCodes(): array
    {
        return [
            PayoutTransaction::STATUS_ANYPAY_PENDING,
            PayoutTransaction::STATUS_ANYPAY_SUCCESS,
            PayoutTransaction::STATUS_ANYPAY_FAIL,
        ];
    }
}
