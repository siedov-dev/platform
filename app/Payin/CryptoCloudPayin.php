<?php

namespace App\Payin;

use App\Models\PaymentSystems;
use App\MUser;
use App\PaymentsAPI\CryptoCloud\CryptoCloud;
use App\PayoutTransaction;
use Illuminate\Support\Facades\Auth;

class CryptoCloudPayin extends Payin
{
    protected $api;

    public function __construct(MUser $user)
    {
        parent::__construct($user);
        $this->name = "CryptoCloud";
        $this->type = self::TYPE_CRYPTOCLOUD;
        $this->api  = new CryptoCloud();
    }

    public function process($amount, $method, array $data, ?PayoutTransaction $transaction, int $method_id)
    {
        if ($transaction === null) $transaction = new PayoutTransaction();
        $method = explode('.', $method)[1];
        $user_id = Auth::id();
        /** @var MUser $user */
        $user = MUser::find($user_id);

        $currency = $this->user->currency;

        $transaction->user_id = $user->id;
        $transaction->hall_id = $user->hall_id;
        $transaction->date = date("Y-m-d H:i:s");
        $transaction->sum = $amount;
        $transaction->currency = $user->getWallet($currency)->getCurrencyName();
        $transaction->operation = 'Пополнение счета через CryptoCloud';
        $transaction->status_id = PayoutTransaction::STATUS_CRYPTOCLOUD_PENDING;
        $transaction->save();

        $resp = $this->api->invoiceCreate($transaction->sum, $transaction->id);

        if (!empty($resp) && $resp['status'] == 'success' && !empty($resp['pay_url'])) {
            return ['url' => $resp['pay_url'], 'in_frame' => true];
        }

        $this->_error = $resp['message']??'system error';
        return false;
    }

    public function getFormRequisites($method, ?PaymentSystems $pay_method): string
    {
        return view('pay.requisites.payin.CloudCoin', ['method' => $method])->render();
    }

    public function getStatusCodes(): array
    {
        return [
            PayoutTransaction::STATUS_CRYPTOCLOUD_PENDING,
            PayoutTransaction::STATUS_CRYPTOCLOUD_SUCCESS,
            PayoutTransaction::STATUS_CRYPTOCLOUD_FAIL,
        ];
    }
}
