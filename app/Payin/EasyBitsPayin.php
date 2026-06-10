<?php

namespace App\Payin;

use App\Models\PaymentSystems;
use App\MUser;
use App\PaymentsAPI\EasyBits\EasyBits;
use App\PaymentsAPI\Payscrow\Payscrow;
use App\PayoutTransaction;

class EasyBitsPayin extends Payin
{

    protected EasyBits $api;

    public function __construct(MUser $user)
    {
        parent::__construct($user);
        $this->type = self::TYPE_EASYBITS;
        $this->name = 'EasyBits';
        $this->api = new EasyBits();
    }

    public function process($amount, $method, array $data, ?PayoutTransaction $transaction, int $method_id)
    {
        if ($transaction === null) $transaction = new PayoutTransaction;

        $transaction->user_id = $this->user->id;
        $transaction->hall_id = $this->user->hall_id;
        $transaction->date = date(format: 'Y-m-d H:i:s');
        $transaction->sum = $amount;
        $transaction->currency = $this->user->getWallet($this->user->currency)->getCurrencyName();
        $transaction->operation = class_basename(Payin::class) . ' ' . $this->name;
        $transaction->status_id = PayoutTransaction::STATUS_EASYBITS_PENDING;
        $transaction->method = $method;
        $transaction->save();

        $method_name = explode('.', $method)[1];

        $resp = $this->api->deposit($amount, $transaction->getId(), 'UAH', $this->user);
        if (empty($resp) || empty($resp['data']['link'])) {
            $this->_error = $resp['errorData']['message']?? 'system error';
            return false;
        }

        return ['url' => $resp['data']['link']];
    }

    public function getStatusCodes(): array
    {
        return [
            PayoutTransaction::STATUS_EASYBITS_PENDING,
            PayoutTransaction::STATUS_EASYBITS_SUCCESS,
            PayoutTransaction::STATUS_EASYBITS_FAIL,
        ];
    }

}
