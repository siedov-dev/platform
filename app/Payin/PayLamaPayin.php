<?php

namespace App\Payin;

use App\MUser;
use App\PaymentsAPI\PayLama\PayLama;
use App\PayoutTransaction;
use Illuminate\Support\Facades\Auth;

class PayLamaPayin extends Payin
{
    protected $api;

    public function __construct(MUser $user)
    {
        parent::__construct($user);
        $this->name = "PayLama";
        $this->type = self::TYPE_BRPAY;
        $this->api = new PayLama($user->currency);
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
        $transaction->operation = 'Пополнение счета через PayLama';
        $transaction->status_id = PayoutTransaction::STATUS_PAYLAMA_PENDING;
        $transaction->save();

        $resp = $this->api->depositForm($amount, $method, $transaction->id);
        if ($resp === false || empty($resp) || $resp['success'] !== true) {
            $this->_error = $resp['cause']?? 'unknown error';
            return false;
        };

        $transaction->coment = $resp['externalID'] ?? ($resp['billID'] ?? $resp['id']);
        $transaction->save();

        if ($method === 'PayLama.card') return ['url' => $resp['paymentURL'], 'in_frame' => true];
        return ['url' => $resp['paymentLinks'][0]];
    }

    public function getStatusCodes(): array
    {
        return [
            PayoutTransaction::STATUS_PAYLAMA_PENDING,
            PayoutTransaction::STATUS_PAYLAMA_SUCCESS,
            PayoutTransaction::STATUS_PAYLAMA_FAIL,
        ];
    }
}
