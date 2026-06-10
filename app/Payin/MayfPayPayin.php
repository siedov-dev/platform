<?php

namespace App\Payin;

use App\MUser;
use App\PaymentsAPI\MayfPay\MayfPay;
use App\PayoutTransaction;
use Illuminate\Support\Facades\Auth;

class MayfPayPayin extends Payin
{
    public function __construct(MUser $user)
    {
        parent::__construct($user);
        $this->name = "MayfPay";
        $this->type = self::TYPE_MAYFPAY;
    }

    public function process($amount, $method, array $data, ?PayoutTransaction $transaction, int $method_id)
    {
        if ($transaction === null) $transaction = new PayoutTransaction();

        $user = MUser::getAuth();

        $currency = $this->user->currency;

        $transaction->user_id = $user->id;
        $transaction->hall_id = $user->hall_id;
        $transaction->date = date("Y-m-d H:i:s");
        $transaction->sum = $amount;
        $transaction->currency = $user->getWallet($currency)->getCurrencyName();
        $transaction->operation = 'Пополнение счета через MayfPay';
        $transaction->status_id = PayoutTransaction::STATUS_MAYFPAY_PENDING;
        $transaction->save();

        $api = new MayfPay();
        $method = explode('.', $method)[1];

        $resp = $api->invoice($amount, $transaction, $method);

        if (empty($resp['success']) || $resp['success'] !== true) {
            $this->_error = $resp['error']?? 'system error';
            return false;
        }

        $transaction->o_tr_id = $resp['Id'];
        $transaction->save();

        return ['url' => $resp['UrlTransaction']];

//        $resp = $api->cardFlow($transaction, $data);
//
//        if (empty($resp['success']) || $resp['success'] !== true) {
//            $this->_error = $resp['error']?? 'system error';
//            return false;
//        }
//
//        return ['url' => $resp['redirect']['url']];
    }

    public function getStatusCodes(): array
    {
        return [
            PayoutTransaction::STATUS_MAYFPAY_PENDING,
            PayoutTransaction::STATUS_MAYFPAY_SUCCESS,
            PayoutTransaction::STATUS_MAYFPAY_FAIL,
        ];
    }

//    public function getFormRequisites($method): string
//    {
//        return view('pay.requisites.payin.card-data', [
//            'method' => $method,
//            'email' => Auth::user()?->email ?? '',
//        ])->render();
//    }
}
