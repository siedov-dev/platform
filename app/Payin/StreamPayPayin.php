<?php

namespace App\Payin;

use App\Models\PaymentSystems;
use App\MUser;
use App\PaymentsAPI\StreamPay;
use App\PayoutTransaction;

class StreamPayPayin extends Payin
{

    protected StreamPay $api;

    public function __construct(MUser $user)
    {
        parent::__construct($user);
        $this->type = self::TYPE_STREAMPAY;
        $this->name = class_basename(StreamPay::class);
        $this->api = new StreamPay;
    }

    public function process($amount, $method, array $data, ?PayoutTransaction $transaction, int $method_id): bool|array
    {
        if($transaction === null) $transaction = new PayoutTransaction;

        $ps = PaymentSystems::where('type', 0)->where('method_alias', $method)->firstOrFail();

        $transaction->user_id = $this->user->id;
        $transaction->hall_id = $this->user->hall_id;
        $transaction->date = date(format: 'Y-m-d H:i:s');
        $transaction->sum = $amount;
        $transaction->currency = $this->user->getWallet($this->user->currency)->getCurrencyName();
        $transaction->operation = class_basename(Payin::class) . ' ' . $this->name;
        $transaction->status_id = PayoutTransaction::STATUS_STREAMPAY_PENDING;
        $transaction->method = $ps->method_alias;
        $transaction->save();

        $response = $this->api->in(
            transaction: $transaction,
        );

        switch ($this->api->last_http_code){
            case 200:

                    $transaction->o_tr_id = $response->data->invoice;
                    $transaction->save();

                return ['url' => $response->data->pay_url];

            default:

                    $this->_error = 'System error. Please try again later.';

                    $comment = match ($this->api->last_http_code) {
                        403 => 'Invalid signature',
                        406 => 'Invalid request data',
                        500 => 'Internal server error',
                        default => $this->_error,
                    };

                    $transaction->status_id = PayoutTransaction::STATUS_STREAMPAY_FAIL;
                    $transaction->coment = $comment;
                    $transaction->save();

                return false;

        }

    }

    public function getStatusCodes(): array
    {
        return [
            PayoutTransaction::STATUS_STREAMPAY_PENDING,
            PayoutTransaction::STATUS_STREAMPAY_SUCCESS,
            PayoutTransaction::STATUS_STREAMPAY_FAIL,
        ];
    }

}
