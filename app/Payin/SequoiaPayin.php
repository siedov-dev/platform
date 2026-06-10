<?php

namespace App\Payin;

use App\Models\PaymentMethodData;
use App\Models\PaymentSystems;
use App\MUser;
use App\PaymentsAPI\SequoiaPay\SequoiaPayApi;
use App\PayoutTransaction;
use Illuminate\Support\Facades\Auth;

class SequoiaPayin extends Payin
{
    public function __construct(MUser $user)
    {
        parent::__construct($user);
        $this->name = "SequoiaPay";
        $this->type = self::TYPE_SEQUOIAPAY;
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
        $transaction->operation = 'Пополнение счета через SequoiaPay';
        $transaction->status_id = PayoutTransaction::STATUS_SEQUOIA_PENDING;
        $transaction->save();

        $method_data = PaymentMethodData::getMethodData($method_id);
        $api = new SequoiaPayApi($method_data['Secret'], $method_data['Webhook']);
        $method = explode('.', $method)[1];

        $add_data = $data;
        if ($method === 'RUB:sbp') {
            $add_data = [
                'add' => [
                    'p2p_scheme' => 2,
                ],
            ];
        }

        if ($method === 'RUB:qr') {
            $resp = $api->depositP2pQr($transaction, $user, $add_data);
        } else {
            $resp = $api->depositP2p($transaction, $user, $add_data);
        }

        if (empty($resp['status']) || $resp['status'] !== 'ok') {
            $this->_error = $resp['message']?? 'system error';
            return false;
        }

        return ['url' => $resp['data']['redirect_url']];
    }

    public function getStatusCodes(): array
    {
        return [
            PayoutTransaction::STATUS_SEQUOIA_PENDING,
            PayoutTransaction::STATUS_SEQUOIA_SUCCESS,
            PayoutTransaction::STATUS_SEQUOIA_FAIL,
        ];
    }

    public function getFormRequisites($method, ?PaymentSystems $pay_method): string
    {
        if ($pay_method?->method_alias === 'SequoiaPay.KZT:p2p') {
            return view('pay.requisites.payin.sequoia-kzt', [
                'method' => $method,
                'pay_method' => $pay_method,
                'email' => MUser::getAuth()?->email?? '',
                ])->render();
        }
        return parent::getFormRequisites($method, $pay_method);
    }
}
