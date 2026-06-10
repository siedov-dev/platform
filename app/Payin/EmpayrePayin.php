<?php

namespace App\Payin;

use App\MUser;
use App\PaymentsAPI\Empayre\Empayre;
use App\PayoutTransaction;
use App\UserWallet;
use Illuminate\Support\Facades\Auth;

class EmpayrePayin extends Payin
{

    public function __construct(MUser $user)
    {
        parent::__construct($user);
        $this->name = "Empayre";
        $this->type = self::TYPE_EMPAYERE;
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
        $transaction->operation = 'Пополнение счета через Empayre';
        $transaction->status_id = PayoutTransaction::STATUS_EMPAYRE_PENDING;
        $transaction->save();

        $api = new Empayre();
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

        $resp = $api->createInvoice($amount * 100, $user->getWallet($currency)->getCurrencyName(), $transaction->id, $verified? 'wellKnown' : 'unknown');

        if (empty($resp) || empty($resp['invoice'])) {
            $this->_error = $resp['Description'] ?? 'system error';
            return false;
        }

        $transaction->o_tr_id = $resp['invoice']['id'];
//        $transaction->coment = $resp['invoiceAccessToken']['payload'];
        $transaction->save();

        return ['url' => 'https://checkout.pcigate.tech/v1/checkout.html?'.http_build_query([
                'name' => null,
                'description' => null,
                'email' => null,
                'redirectUrl' => route('pay.success'),
                'paymentFlowHold' => false,
                'invoiceID' => $resp['invoice']['id'],
                'invoiceAccessToken' => $resp['invoiceAccessToken']['payload'],
            ])];
    }

//    public function getFormRequisites($method): string
//    {
//        return view('pay.requisites.payin.card-data', ['method' => $method,])->render();
//    }

    public function getStatusCodes(): array
    {
        return [
            PayoutTransaction::STATUS_EMPAYRE_PENDING,
            PayoutTransaction::STATUS_EMPAYRE_SUCCESS,
            PayoutTransaction::STATUS_EMPAYRE_FAIL,
        ];
    }
}
