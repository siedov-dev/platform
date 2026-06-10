<?php

namespace App\Payin;

use App\MUser;
use App\PaymentsAPI\PaymentCenter\PaymentCenter;
use App\PaymentsAPI\Postman\Postman;
use App\PayoutTransaction;
use App\UserWallet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class PostmanPayin extends Payin
{

    public function __construct(MUser $user)
    {
        parent::__construct($user);
        $this->name = "Postman";
        $this->type = self::TYPE_POSTMAN;
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
        $transaction->operation = 'Пополнение счета через Postman';
        $transaction->status_id = PayoutTransaction::STATUS_POSTMAN_PENDING;
        $transaction->save();

        $api = new Postman();
        $method = explode('.', $method)[1];

        $resp = $api->createBuy($amount, $transaction->id, $user_id);
        if ($resp['error']??'' === 'auth.session_expired') {
            $api->refreshToken();
            $resp = $api->createBuy($amount, $transaction->id, $user_id);
        }

        if (empty($resp)) {
            $this->_error = 'system error';
            return false;
        }

        $transaction->coment = "639ae40e996d9f37eb72a561";
        $transaction->save();

        if ($resp['status'] !== 'ok') {
            $this->_error = $resp['error']?? 'unknown error';
            return false;
        }

        return ['url' => $resp['transactionUrl']];
    }

    public function getStatusCodes(): array
    {
        return [
            PayoutTransaction::STATUS_POSTMAN_PENDING,
            PayoutTransaction::STATUS_POSTMAN_SUCCESS,
            PayoutTransaction::STATUS_POSTMAN_FAIL,
        ];
    }
}
