<?php

namespace App\Payin;

use App\MUser;
use App\PaymentsAPI\Einpayz\Einpayz;
use App\PaymentsAPI\Paycos\Paycos;
use App\PaymentsAPI\Paze\PazeApi;
use App\PayoutTransaction;
use App\UserWallet;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class EinpayzPayin extends Payin
{
    private Einpayz $api;

    public function __construct(MUser $user)
    {
        parent::__construct($user);
        $this->name = "Einpayz";
        $this->type = self::TYPE_EINPAYZ;
        $this->api  = new Einpayz();
    }

    public function process($amount, $method, array $data, ?PayoutTransaction $transaction, int $method_id)
    {
        if ($transaction === null) $transaction = new PayoutTransaction();
        $method = explode('.', $method)[1];
        $currency = $this->user->currency;
        Log::channel('pay')->info(__CLASS__."@".__METHOD__.' method'. $method);
        $user_id = Auth::id();
        /** @var MUser $user */
        $user = MUser::find($user_id);

        $transaction->user_id = $user->id;
        $transaction->hall_id = $user->hall_id;
        $transaction->date = date("Y-m-d H:i:s");
        $transaction->sum = $amount;
        $transaction->currency = $user->getWallet($currency)->getCurrencyName();
        $transaction->operation = 'Пополнение счета через Einpayz';
        $transaction->status_id = PayoutTransaction::STATUS_EINPAYZ_PENDING;
        $transaction->save();

        $resp = $this->api->deposit($amount, $transaction->id, $method);

        if (!empty($resp) && !empty($resp['payload']['available_methods'])) {
            foreach ($resp['payload']['available_methods'] as $method_data) {
                if ($method_data['name'] === $method) {
//                    $form = view('pay.redirect-form', ['url' => $method_data['payment_link']])->render();
//                    return ['redirect_form' => $form];
                    return ['url' => $method_data['payment_link']];
                    return ['url' => $method_data['payment_link'], 'in_frame' => true];
                }
            }
            $this->_error = 'method not found';
            return false;
        }

        $this->_error = $resp['error']??'system error';
        return false;
    }

    public function getStatusCodes(): array
    {
        return [
            PayoutTransaction::STATUS_EINPAYZ_PENDING,
            PayoutTransaction::STATUS_EINPAYZ_SUCCESS,
            PayoutTransaction::STATUS_EINPAYZ_FAIL,
        ];
    }
}
