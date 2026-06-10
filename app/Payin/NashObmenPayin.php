<?php

namespace App\Payin;

use App\MUser;
use App\PaymentsAPI\NashObmen\NashObmen;
use App\PayoutTransaction;
use App\UserWallet;
use Illuminate\Support\Facades\Auth;

class NashObmenPayin extends Payin
{
    public function __construct(MUser $user)
    {
        parent::__construct($user);
        $this->name = "NashObmen";
        $this->type = self::TYPE_NASHOBMEN;
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
        $transaction->operation = 'Пополнение счета через NashObmen';
        $transaction->status_id = PayoutTransaction::STATUS_NASHOBMEN_PENDING;

        $api = new NashObmen();
        $method = explode('.', $method)[1];

        $resp = $api->getCard($amount, $method);
        if (empty($resp[0]['result']) || $resp[0]['result'] !== 'success') {
            $this->_error = $resp[0]['message']?? 'system error';
            return false;
        }

        $transaction->coment = $resp[0]['trade'];
        $transaction->save();

        return ['modal_data' => view('pay.nashobmen', ['card' => $resp[0]['card_number'], 'trade' => $resp[0]['trade'], 'amount' => $amount, 'currency' => $method])->render()];
    }

    public function getStatusCodes(): array
    {
        return [
            PayoutTransaction::STATUS_NASHOBMEN_PENDING,
            PayoutTransaction::STATUS_NASHOBMEN_SUCCESS,
            PayoutTransaction::STATUS_NASHOBMEN_FAIL,
        ];
    }
}
