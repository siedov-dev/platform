<?php

namespace App\Payin;

use App\Models\PaymentSystems;
use App\MUser;
use App\PaymentsAPI\Inwizo\Inwizo;
use App\PaymentsAPI\Sandbox\Sandbox;
use App\PayoutTransaction;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class SandboxPayin extends Payin
{
    public function __construct(MUser $user)
    {
        parent::__construct($user);
        $this->name = "Sandbox";
        $this->type = self::TYPE_SANDBOX;
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
        $transaction->operation = 'Пополнение счета через Sandbox';
        $transaction->status_id = PayoutTransaction::STATUS_SANDBOX_PENDING;
        $transaction->save();

        $api = new Sandbox();
        $method = explode('.', $method)[1];

        $resp = $api->paymentSession($amount, $transaction->id, $data, $transaction->currency);
        if (($resp['status']??'') === 'error') {
            $this->_error = $resp['errors'][0]??'system error';
        }

        $transaction->coment = $resp['payment_session']['id']??'';
        $transaction->save();

        return ['url' => $resp['url']];
    }

    public function getFormRequisites($method, ?PaymentSystems $pay_method): string
    {
        $email = $this->getLastRequisite('email')?? $this->user->email;

        return view('pay.requisites.payin.with-email', [
            'email' => $email,
            'method' => $method,
        ]);
    }

    public function getStatusCodes(): array
    {
        return [
            PayoutTransaction::STATUS_SANDBOX_PENDING,
            PayoutTransaction::STATUS_SANDBOX_SUCCESS,
            PayoutTransaction::STATUS_SANDBOX_FAIL,
        ];
    }
}
