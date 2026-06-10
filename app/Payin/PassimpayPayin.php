<?php

namespace App\Payin;

use App\MUser;
use App\PaymentsAPI\Passimpay\Passimpay;
use App\PayoutTransaction;
use Illuminate\Support\Facades\Auth;

class PassimpayPayin extends Payin
{

    public function __construct(MUser $user)
    {
        parent::__construct($user);
        $this->name = "Passimpay";
        $this->type = self::TYPE_PASSIMPAY;
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
        $transaction->operation = 'Пополнение счета через Passimpay';
        $transaction->status_id = PayoutTransaction::STATUS_PASSIMPAY_PENDING;
        $transaction->save();

        $api = new Passimpay();
        $resp = $api->createInvoiceLink($transaction->id, $amount);
        if (!empty($resp['message'])) {
            $this->_error = $resp['message'];
            return false;
        }
        return ['url' => $resp['url']?? ''];
    }

    public function getStatusCodes(): array
    {
        return [
            PayoutTransaction::STATUS_PASSIMPAY_PENDING,
            PayoutTransaction::STATUS_PASSIMPAY_SUCCESS,
            PayoutTransaction::STATUS_PASSIMPAY_FAIL,
        ];
    }
}
