<?php

namespace App\Payin;

use App\MUser;
use App\PaymentsAPI\BestPay24\BestPay24;
use App\PayoutTransaction;
use Illuminate\Support\Facades\Auth;

class BestPay24Payin extends Payin
{
    public function __construct(MUser $user)
    {
        parent::__construct($user);
        $this->name = "BestPay24";
        $this->type = self::TYPE_BESTPAY24;
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
        $transaction->operation = 'Пополнение счета через BestPay24';
        $transaction->status_id = PayoutTransaction::STATUS_BESTPAY24_PENDING;
        $transaction->save();


        $api = new BestPay24();
        $resp = $api->deposit($transaction->id, $amount, $user->email, $data);

        if(isset($resp['Status']) && $resp['Status'] == true){
            $transaction->fee = $resp['Data']['Fee'];
            $transaction->total_sum = $transaction->sum - $resp['Data']['Fee'];
            $transaction->save();

            return ['url' => $resp['Data']['PayURL']];
        }

        $this->_error = 'system error';
        return false;

    }

    public function getStatusCodes(): array
    {
        return [
            PayoutTransaction::STATUS_BESTPAY24_PENDING,
            PayoutTransaction::STATUS_BESTPAY24_SUCCESS,
            PayoutTransaction::STATUS_BESTPAY24_FAIL,
        ];
    }
}
