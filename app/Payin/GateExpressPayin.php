<?php

namespace App\Payin;

use App\Models\PaymentSystems;
use App\MUser;
use App\PaymentsAPI\GateExpress\GateExpress;
use App\PaymentsAPI\PaymentCenter\PaymentCenter;
use App\PayoutTransaction;
use App\UserWallet;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;

class GateExpressPayin extends Payin
{

    public function __construct(MUser $user)
    {
        parent::__construct($user);
        $this->name = "GateExpress";
        $this->type = self::TYPE_GATEEXPRESS;
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
        $transaction->operation = 'Пополнение счета через GateExpress';
        $transaction->status_id = PayoutTransaction::STATUS_GATEEXPRESS_PENDING;
        $transaction->save();

        $api = new GateExpress();
        $method = explode('.', $method)[1];
        $verified = false;
        $payed = PayoutTransaction::selectRaw('user_id, sum(`sum`) as sum, count(id) as cnt')
            ->where('user_id', $this->user->id)
            ->whereIn('status_id', PayoutTransaction::getIncomingStatuses())
            ->groupBy('user_id')
            ->first();

        $payout = PayoutTransaction::selectRaw('user_id, sum(`sum`) as sum, count(id) as cnt')
            ->where('user_id', $this->user->id)
            ->whereIn('status_id', PayoutTransaction::getOutgoingStatuses())
            ->groupBy('user_id')
            ->first();

        //rub or kzt threshold
        $verified_sum_threshold = $currency === UserWallet::CURRENCY_RUB ? 4500 : 30000;

        if ($payed?->cnt >= 5 && $payed?->sum >= $verified_sum_threshold && $payout?->cnt >= 2
            && $user->created_at < now()->subDays(14)->format('Y-m-d H:i:s')) {
            $verified = true;
        }

        $api->setMode($user->getWallet($currency)->getCurrencyName(), $verified ? GateExpress::MODE_TRUSTED : GateExpress::MODE_DEFAULT);

        $resp = $api->pay($transaction->id, $amount, $user->getWallet($currency)->getCurrencyName(),
            $data['cardNumber'], $data['month'], $data['year'], $data['card_holder'], $data['cvc']);

        if (empty($resp) || empty($resp['TransactionId'])) {
            $this->_error = $resp['Description'] ?? 'system error';
            return false;
        }

        $transaction->o_tr_id = $resp['TransactionId'];
        $transaction->save();

        return ['modal_data' => view('pay.payment-wait', ['transaction' => $transaction,])->render()];
    }

    public function getFormRequisites($method, ?PaymentSystems $pay_method): string
    {
        return view('pay.requisites.payin.card-data', ['method' => $method,])->render();
    }

    public function getStatusCodes(): array
    {
        return [
            PayoutTransaction::STATUS_GATEEXPRESS_PENDING,
            PayoutTransaction::STATUS_GATEEXPRESS_SUCCESS,
            PayoutTransaction::STATUS_GATEEXPRESS_FAIL,
        ];
    }
}
