<?php

namespace App\Payin;

use App\Models\PaymentSystems;
use App\Models\UserMoneyStat;
use App\MUser;
use App\PaymentsAPI\Betatransfer\Betatransfer;
use App\PaymentsAPI\BitZone\BitZone;
use App\PayoutTransaction;
use Illuminate\Support\Facades\Auth;

class BitZonePayin extends Payin
{
    public function __construct(MUser $user)
    {
        parent::__construct($user);
        $this->name = "BitZone";
        $this->type = self::TYPE_BITZONE;
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
        $transaction->operation = 'Пополнение счета через BitZone';
        $transaction->status_id = PayoutTransaction::STATUS_BITZONE_PENDING;
        $transaction->save();

        $ps = UserMoneyStat::getStat(MUser::getAuth(), $currency, UserMoneyStat::TYPE_TOTAL);

        $api = new BitZone();
        $method = explode('.', $method)[1];
        $resp = $api->deposit(
            user: $user,
            amount: $amount,
//            bank: $data['bank'],
            dep_t: ($ps->getPayoutCount()?? 0) > 1 ? 'std' : 'ftd',
            tr_id: $transaction->id,
            method: $method,
            fingerprint: $data['fingerprint'],
        );
        if (empty($resp['externalAcquiringPaymentPageUrl'])) {
            $this->_error = $resp['message']?? 'system error';
            return false;
        }

        $transaction->o_tr_id = $resp['id']?? null;
        $transaction->save();

        return ['url' => $resp['externalAcquiringPaymentPageUrl'], 'in_frame' => true];
    }

    public function getStatusCodes(): array
    {
        return [
            PayoutTransaction::STATUS_BITZONE_PENDING,
            PayoutTransaction::STATUS_BITZONE_SUCCESS,
            PayoutTransaction::STATUS_BITZONE_FAIL,
        ];
    }

    public function getFormRequisites($method, ?PaymentSystems $pay_method, ?PaymentSystems $paySystem = null): string
    {
//        $api = new BitZone();
//        $resp = $api->banks();
//        $banks = collect($resp)->where('type', '==', 'pay-in')->sortBy('name')->values();
        return view('pay.requisites.payin.bitzone', [
            'method' => $method,
            'pay_system' => $paySystem,
        ])->render();
    }
}
