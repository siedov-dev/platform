<?php

namespace App\Payin;

use App\Models\PaymentSystems;
use App\MUser;
use App\PaymentsAPI\PayKassa\PayKassa;
use App\PayoutTransaction;
use Illuminate\Support\Facades\Auth;

class PayKassaPayin extends Payin
{
    private PayKassa $api;

    public function __construct(MUser $user)
    {
        parent::__construct($user);
        $this->name = "PayKassa";
        $this->type = self::TYPE_PAYKASSA;
        $this->api = new PayKassa();
    }

    public function process($amount, $method, array $data, ?PayoutTransaction $transaction, int $method_id)
    {
        if ($transaction === null) $transaction = new PayoutTransaction();
        $method = explode('.', $method)[1];
        $user_id = Auth::id();
        /** @var MUser $user */
        $user = MUser::find($user_id);

        $currency = $this->user->currency;

        $transaction->user_id = $user->id;
        $transaction->hall_id = $user->hall_id;
        $transaction->date = date("Y-m-d H:i:s");
        $transaction->sum = $amount;
        $transaction->currency = $user->getWallet($currency)->getCurrencyName();
        $transaction->operation = 'Пополнение счета через InterKassa Inr P2P';
        $transaction->status_id = PayoutTransaction::STATUS_PAYKASSA_PENDING;
        $transaction->save();

        $resp = $this->api->deposit($transaction->id, $amount, $user->getWallet($currency)->getCurrencyName(), $method);
        if ($resp['error'] !== false) {
            $this->_error = $resp['message']?? 'system error!';
            return false;
        }

        return ['url' => $resp['data']['url']];
    }

    public function getFormRequisites($method, ?PaymentSystems $pay_method): string
    {
        if ($method === 'PayKassa.11-BitCoin') return view('pay.requisites.payin.btc', ['method' => $method,]);
        if ($method === 'PayKassa.31-BinanceSmartChain_BEP20') return view('pay.requisites.payin.btc', ['method' => $method,]);
        return parent::getFormRequisites($method, $pay_method);
    }

    public function getStatusCodes(): array
    {
        return [
            PayoutTransaction::STATUS_PAYKASSA_PENDING,
            PayoutTransaction::STATUS_PAYKASSA_SUCCESS,
            PayoutTransaction::STATUS_PAYKASSA_FAIL,
        ];
    }
}
