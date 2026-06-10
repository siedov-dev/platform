<?php

namespace App\Payin;

use App\ApplicationForPayment;
use App\Models\PaymentSystems;
use App\MUser;
use App\PaymentsAPI\StreamPay;
use App\PaymentsAPI\Turcode;
use App\PayoutTransaction;
use Illuminate\Support\Facades\Http;

class TurcodePayin extends Payin
{

    protected Turcode $api;

    public function __construct(MUser $user)
    {
        parent::__construct($user);
        $this->type = self::TYPE_TURCODE;
        $this->name = class_basename(Turcode::class);
        $this->api = new Turcode;
    }

    public function process($amount, $method, array $data, ?PayoutTransaction $transaction, int $method_id)
    {
        if($transaction === null) $transaction = new PayoutTransaction;

        $ps = PaymentSystems::where('type', 0)->where('method_alias', $method)->firstOrFail();

        $transaction->user_id = $this->user->id;
        $transaction->hall_id = $this->user->hall_id;
        $transaction->date = date(format: 'Y-m-d H:i:s');
        $transaction->sum = $amount;
        $transaction->currency = $this->user->getWallet($this->user->currency)->getCurrencyName();
        $transaction->operation = class_basename(Payin::class) . ' ' . $this->name;
        $transaction->status_id = PayoutTransaction::STATUS_TURCODE_PENDING;
        $transaction->method = $ps->method_alias;
        $transaction->save();

        $method_name = explode('.', $ps->method_alias)[1];
        $bank = explode('_', $method_name)[1]?? 0;
        $bank = intval($bank);
        if ($bank === 0) $bank = 100;

        $response = $this->api->hpp(
            transaction: $transaction,
            bank: $bank,
        );

        switch ($this->api->last_http_code){
            case 200:
                return ['url' => $response->url];
            default:

                $this->_error = 'System error. Please try again later.';

                $comment = match($this->api->last_http_code){
                    401 => 'Unauthorized',
                    402 => 'User ID has been blocked due to violation of our TOS',
                    405 => 'Invalid request',
                    500 => 'Internal Server Error',
                    default => 'http code ' . $this->api->last_http_code,
                };

                $transaction->status_id = PayoutTransaction::STATUS_TURCODE_FAIL;
                $transaction->coment = $comment;
                $transaction->save();

                return response('', 500);
        }

    }

    public function getStatusCodes(): array
    {
        return [
            PayoutTransaction::STATUS_TURCODE_PENDING,
            PayoutTransaction::STATUS_TURCODE_SUCCESS,
            PayoutTransaction::STATUS_TURCODE_FAIL,
        ];
    }

}
