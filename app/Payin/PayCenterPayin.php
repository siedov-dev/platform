<?php

namespace App\Payin;

use App\MUser;
use App\PaymentsAPI\PaymentCenter\PaymentCenter;
use App\PayoutTransaction;
use App\UserWallet;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;

class PayCenterPayin extends Payin
{

    public function __construct(MUser $user)
    {
        parent::__construct($user);
        $this->name = "PaymentCeenter";
        $this->type = self::TYPE_PAYCENTER;
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
        $transaction->operation = 'Пополнение счета через PayCenter';
        $transaction->status_id = PayoutTransaction::STATUS_PAYCENTER_PENDING;
        $transaction->save();

        $api = new PaymentCenter();
        $method = explode('.', $method)[1];
        $payed = PayoutTransaction::selectRaw('user_id, sum(`sum`) as sum, count(id) as cnt')
            ->where('user_id', $this->user->id)
            ->whereIn('status_id', PayoutTransaction::getIncomingStatuses())
            ->groupBy('user_id')
            ->first();

        $verified = $payed?->cnt > 5;
        $api->setMode($verified? PaymentCenter::MODE_TRUSTED : PaymentCenter::MODE_DEFAULT);
        $resp = $api->webform($transaction->id, $amount, $user->getWallet($currency)->getCurrencyName());

        if (empty($resp)) {
            $this->_error = $resp['error']?? 'system error';
            return false;
        }

        return ['url' => $resp];
    }

    public function getStatusCodes(): array
    {
        return [
            PayoutTransaction::STATUS_PAYCENTER_PENDING,
            PayoutTransaction::STATUS_PAYCENTER_SUCCESS,
            PayoutTransaction::STATUS_PAYCENTER_FAIL,
        ];
    }
}
