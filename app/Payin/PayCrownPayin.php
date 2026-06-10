<?php

namespace App\Payin;

use App\MUser;
use App\PaymentsAPI\PayCrown\PayCrown;
use App\PayoutTransaction;
use Illuminate\Support\Facades\Auth;

class PayCrownPayin extends Payin
{
    public function __construct(MUser $user)
    {
        parent::__construct($user);
        $this->name = "PayCrown";
        $this->type = self::TYPE_PAYCROWN;
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
        $transaction->operation = 'Пополнение счета через PayCrown';
        $transaction->status_id = PayoutTransaction::STATUS_PAYCROWN_PENDING;
        $transaction->save();

        $api = new PayCrown();
        $method = explode('.', $method)[1];

        $url = $api->deposit($amount, $transaction);

        return ['url' => $url, 'in_frame' => true];
    }

    public function getStatusCodes(): array
    {
        return [
            PayoutTransaction::STATUS_PAYCROWN_PENDING,
            PayoutTransaction::STATUS_PAYCROWN_SUCCESS,
            PayoutTransaction::STATUS_PAYCROWN_FAIL,
        ];
    }

}
