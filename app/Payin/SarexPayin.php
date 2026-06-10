<?php

namespace App\Payin;

use App\Models\PaymentMethodData;
use App\Models\PaymentSystems;
use App\MUser;
use App\PaymentsAPI\Exotic\ExoticApi;
use App\PaymentsAPI\SarexPays\SarexPays;
use App\PaymentsAPI\SequoiaPay\SequoiaPayApi;
use App\PayoutTransaction;
use Illuminate\Support\Facades\Auth;

class SarexPayin extends Payin
{
    public function __construct(MUser $user)
    {
        parent::__construct($user);
        $this->name = "SarexPays";
        $this->type = self::TYPE_SAREX;
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
        $transaction->operation = 'Пополнение счета через SarexPays';
        $transaction->status_id = PayoutTransaction::STATUS_SAREX_PENDING;
        $transaction->save();

//        $method_data = PaymentMethodData::getMethodData($method_id);
        $api = new SarexPays();
        $sub_method = explode('.', $method)[1];
        [$p_method, $p_type] = explode(':', $sub_method);
        $resp = $api->deposit($transaction,$user, $p_method, $p_type, $data);

        if (empty($resp['status'])) {
            $error = $resp['error']['message']?? 'system error';
            $this->_error = $error;
            return false;
        }

        $transaction->o_tr_id = $resp['result']['id']?? null;
        $transaction->save();
        return ['url' => $resp['url']];
    }

    public function getStatusCodes(): array
    {
        return [
            PayoutTransaction::STATUS_SAREX_PENDING,
            PayoutTransaction::STATUS_SAREX_SUCCESS,
            PayoutTransaction::STATUS_SAREX_FAIL,
        ];
    }

    public function getFormRequisites($method, ?PaymentSystems $pay_method): string
    {
        return view('pay.requisites.payin.sarex-mob_kz', [
            'method' => $method,
            'pay_method' => $pay_method,
            'email' => Auth::user()?->email ?? '',
            'phone' => Auth::user()?->phone ?? '',
        ])->render();
    }
}
