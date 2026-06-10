<?php

namespace App\Payin;

use App\Models\PaymentSystems;
use App\MUser;
use App\PaymentsAPI\Paydex\Paydex;
use App\PayoutTransaction;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class PaydexPayin extends Payin
{
    protected Paydex $api;

    public function __construct(MUser $user)
    {
        parent::__construct($user);
        $this->name = "Paydex";
        $this->type = self::TYPE_PAYDEX;
        $this->api = new Paydex();
    }

    public function process($amount, $method, array $data, ?PayoutTransaction $transaction, int $method_id)
    {
        if ($transaction === null) $transaction = new PayoutTransaction();
        $method = explode('.', $method)[1];
        $currency = $this->user->currency;
        Log::channel('pay')->info(__CLASS__ . "@" . __METHOD__ . ' method' . $method);
        $user_id = Auth::id();
        /** @var MUser $user */
        $user = MUser::find($user_id);

        $transaction->user_id = $user->id;
        $transaction->hall_id = $user->hall_id;
        $transaction->date = date("Y-m-d H:i:s");
        $transaction->sum = $amount;
        $transaction->currency = $user->getWallet($currency)->getCurrencyName();
        $transaction->operation = 'Пополнение счета через Paydex';
        $transaction->status_id = PayoutTransaction::STATUS_PAYDEX_PENDING;
        $transaction->save();

        $resp = $this->api->payment($transaction->id, $amount, $method, $data, $transaction->currency);

        if (!empty($resp) && $resp['data']['status'] === 'process_pending' && !empty($resp['data']['active_payment']['payload']['action'])) {
            return ['url' => $resp['data']['active_payment']['payload']['action']];
        }

        $this->_error = $resp['error'] ?? 'system error';
        return false;
    }

    public function getFormRequisites($method, ?PaymentSystems $pay_method): string
    {
        $email = $this->getLastRequisite('email')?? $this->user->email;
        $name = $this->getLastRequisite('name');
        return view('pay.requisites.payin.name-email', [
            'name' => $name??'',
            'email' => $email??'',
            'method' => $method,
        ])->render();
    }

    public function getStatusCodes(): array
    {
        return [
            PayoutTransaction::STATUS_PAYDEX_PENDING,
            PayoutTransaction::STATUS_PAYDEX_SUCCESS,
            PayoutTransaction::STATUS_PAYDEX_FAIL,
        ];
    }
}
