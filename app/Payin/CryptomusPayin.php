<?php

namespace App\Payin;

use App\Models\PaymentSystems;
use App\MUser;
use App\PaymentsAPI\Cryptomus\Cryptomus;
use App\PaymentsAPI\InterKassa\IkKzt;
use App\PayoutTransaction;
use App\UserWallet;
use Illuminate\Support\Facades\Auth;

class CryptomusPayin extends Payin
{
    public function __construct(MUser $user)
    {
        parent::__construct($user);
        $this->name = "Cryptomus";
        $this->type = self::TYPE_CRYPTOMUS;
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
        $transaction->operation = 'Пополнение счета через Cryptomus';
        $transaction->status_id = PayoutTransaction::STATUS_CRYPTOMUS_PENDING;
        $transaction->save();

        $api = new Cryptomus();
        $method = explode('.', $method)[1];
        $fiat = '';
        if (preg_match('#^(\w*)_(\w*)$#', $method)) {
            $fiat = explode('_', $method)[0];
            $method = explode('_', $method)[1];
        }
        if ($fiat === 'fiat') {
            $resp = $api->depositFiat($amount, $method, $transaction->id, UserWallet::currencyName($currency));
        } else {
            $resp = $api->deposit($amount, $currency, $transaction->id, $method);
        }
        if (empty($resp['result']['url'])) {
            $this->_error = $resp['message'] ?? 'system error';
            return false;
        }
        return ['url' => $resp['result']['url']];
    }

    public function getStatusCodes(): array
    {
        return [
            PayoutTransaction::STATUS_CRYPTOMUS_PENDING,
            PayoutTransaction::STATUS_CRYPTOMUS_SUCCESS,
            PayoutTransaction::STATUS_CRYPTOMUS_FAIL,
        ];
    }

    public function getFormRequisites($method, ?PaymentSystems $pay_method): string
    {
        if (preg_match('#^\w*.(\w*)_(\w*)$#', $method, $matches)) {
            if ($matches[1] === 'BTC') return view('pay.requisites.payin.btc', ['method' => $method]);
        }
        return parent::getFormRequisites($method, $pay_method);
    }

}
