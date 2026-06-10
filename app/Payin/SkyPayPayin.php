<?php

namespace App\Payin;

use App\Models\PaymentSystems;
use App\MUser;
use App\PaymentsAPI\SkyPay;
use App\PayoutTransaction;
use App\UserWallet;
use Illuminate\Support\Facades\Auth;

class SkyPayPayin extends Payin
{

    protected SkyPay $api;

    public function __construct(MUser $user)
    {
        parent::__construct($user);
        $this->name = "SkyPay";
        $this->type = self::TYPE_SKYPAY;
        $this->api = new SkyPay(mode: 'production');
    }

    public function process($amount, $method, array $data, ?PayoutTransaction $transaction, int $method_id)
    {
        if ($transaction === null) $transaction = new PayoutTransaction;



        $user_id = Auth::id();
        /** @var MUser $user */
        $user = MUser::find($user_id);

        $currency = $this->user->currency;

        $transaction->user_id = $user->id;
        $transaction->hall_id = $user->hall_id;
        $transaction->date = date("Y-m-d H:i:s");
        $transaction->sum = $amount;
        $transaction->currency = $user->getWallet($currency)->getCurrencyName();
        $transaction->operation = 'Payin '. SkyPay::class;
        $transaction->status_id = $this->getStatusCodes()[0];
        $transaction->save();

        $response = $this->api->purchase(
            order_id: $transaction->id,
            amount: $amount,
            currency: $transaction->currency,
            card_number: $data['cardNumber'],
            back_url: route('pay.success')
        );

        if(isset($response->web_link) && $response->web_link){

            $transaction->o_tr_id = $response->payment_id;
            $transaction->save();

            return ['url' => $response->web_link];
        }else{
            $this->_error = 'System error. Please try again later.';

            $transaction->status_id = $this->getStatusCodes()[2];
            $transaction->coment = $this->_error;
            $transaction->save();

            return false;
        }

    }

    public function getFormRequisites($method, ?PaymentSystems $pay_method): string
    {
        return view('pay.requisites.payin.sky-pay', ['method' => $method,]);
        //return parent::getFormRequisites($method);
    }

    public function getStatusCodes(): array
    {
        return [
            PayoutTransaction::STATUS_SKYPAY_PENDING,
            PayoutTransaction::STATUS_SKYPAY_SUCCESS,
            PayoutTransaction::STATUS_SKYPAY_FAIL,
        ];
    }

}
