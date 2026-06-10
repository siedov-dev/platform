<?php

namespace App\Payin;

use App\Models\PaymentSystems;
use App\MUser;
use App\PaymentsAPI\Inwizo\Inwizo;
use App\PayoutTransaction;
use App\UserWallet;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class InwizoPayin extends Payin
{
    public function __construct(MUser $user)
    {
        parent::__construct($user);
        $this->name = "Inwizo";
        $this->type = self::TYPE_INWIZO;
    }

    public function process($amount, $method, array $data, ?PayoutTransaction $transaction, int $method_id)
    {
        if ($transaction === null) $transaction = new PayoutTransaction();

        $user_id = Auth::id();
        /** @var MUser $user */
        $user = MUser::find($user_id);

        $currency = $this->user->currency;

        $payed = $user->getDepositsCount();

        $transaction->user_id = $user->id;
        $transaction->hall_id = $user->hall_id;
        $transaction->date = date("Y-m-d H:i:s");
        $transaction->sum = $amount;
        $transaction->currency = $user->getWallet($currency)->getCurrencyName();
        $transaction->operation = 'Пополнение счета через Inwizo';
        $transaction->status_id = PayoutTransaction::STATUS_INWIZO_PENDING;

        if ($currency == UserWallet::CURRENCY_KZT && $payed > 3) {
            $transaction->coment = 'trusted';
        }

        $transaction->save();

        $method = explode('.', $method)[1];
        $api = new Inwizo($method, $transaction->coment);

        if ($method === 'ext' || $method === 'ext-KZT') {
            $resp = $api->depositExternal($amount, $currency, $method, $transaction->id, $data);
        } else {
            $resp = $api->deposit($amount, $currency, 'MBC', $transaction->id, $data);
        }

        Log::channel('pay')->info('Inwizo form:');
        Log::channel('pay')->info($resp);

        return ['redirect_form' => $resp];
    }

    public function getFormRequisites($method, ?PaymentSystems $pay_method): string
    {
        $email = $this->getLastRequisite('email')?? $this->user->email;
        $phone = $this->getLastRequisite('phone')?? $this->user->phone;

        $phone =  preg_replace('#[^\d]#', '', $phone??'');

        return view('pay.requisites.payin.phone-mail', [
            'phone' => $phone,
            'email' => $email,
            'method' => $method,
        ]);
    }

    public function getStatusCodes(): array
    {
        return [
            PayoutTransaction::STATUS_INWIZO_PENDING,
            PayoutTransaction::STATUS_INWIZO_SUCCESS,
            PayoutTransaction::STATUS_INWIZO_FAIL,
        ];
    }

//    public function isHidden(int $currency_id, int $deposits_count, float $deposits_sum, ?PaymentSystems $system = null): bool
//    {
//        if ($currency_id == UserWallet::CURRENCY_RUB) return false;
//        return $deposits_count < 4;
//    }
}
