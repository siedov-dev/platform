<?php

namespace App\Payin;

use App\Models\PaymentSystems;
use App\MUser;
use App\PaymentsAPI\B2Pay\B2Pay;
use App\PayoutTransaction;
use App\Wallet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class B2PayPayin extends Payin
{
    public function __construct(MUser $user)
    {
        parent::__construct($user);
        $this->name = "B2Pay";
        $this->type = self::TYPE_B2PAY;
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
        $transaction->operation = 'Пополнение счета через B2Pay';
        $transaction->status_id = PayoutTransaction::STATUS_B2PAY_PENDING;
        $transaction->save();

        $api = new B2Pay();
        $method = explode('.', $method)[1];

        $resp = $api->deposit($amount, $transaction->getCurrency(), $transaction->id);

        if ($resp === null) {
            $this->_error = "Internal error";
            return false;
        }

        return ['url' => $resp['paymentData']['links'][$method]??  $resp['paymentData']['link']];
    }

    public function getStatusCodes(): array
    {
        return [
            PayoutTransaction::STATUS_B2PAY_PENDING,
            PayoutTransaction::STATUS_B2PAY_SUCCESS,
            PayoutTransaction::STATUS_B2PAY_FAIL,
        ];
    }

    public function isBlocked($method): bool
    {
        $this->_error = null;

        $pay_system = PaymentSystems::getPayinSystemForMethod($method);

        if ($pay_system === null) {
            Log::channel('pay')->error('PaymentSystems not found for alias ' . $method);
            $this->_error = "system error";
            throw new \Exception('PaymentSystems not found for alias ' . $method);
        }

        if ($pay_system->additionalCheck()) {
            $payed = PayoutTransaction::selectRaw('user_id, sum(`sum`) as sum, count(id) as cnt')
                ->where('user_id', $this->user->id)
                ->whereIn('status_id', PayoutTransaction::getIncomingStatuses())
                ->groupBy('user_id')
                ->first();

            if (!$payed || $payed->cnt < 3) {
                return true;
            }
        }

        return false;
    }
}
