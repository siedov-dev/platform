<?php

namespace App\Payin;

use App\MUser;
use App\PaymentsAPI\Alfakit\Alfakit;
use App\PaymentsAPI\Cryptomus\Cryptomus;
use App\PayoutTransaction;
use Illuminate\Support\Facades\Auth;

class AlfakitPayin extends Payin
{
    public function __construct(MUser $user)
    {
        parent::__construct($user);
        $this->name = "Alfakit";
        $this->type = self::TYPE_ALFAKIT;
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
        $transaction->operation = 'Пополнение счета через Alfakit';
        $transaction->status_id = PayoutTransaction::STATUS_ALFAKIT_PENDING;
        $transaction->save();

        $api = new Alfakit();
        $method = explode('.', $method)[1];
        $payed = PayoutTransaction::selectRaw('user_id, sum(`sum`) as sum, count(id) as cnt')
            ->where('user_id', $this->user->id)
            ->whereIn('status_id', PayoutTransaction::getIncomingStatuses())
            ->groupBy('user_id')
            ->first();

        $verified = $payed->cnt >= 4;
        $resp = $api->deposit($amount, $currency, $transaction->id, $verified);
        if (empty($resp['link'])) {
            $this->_error = $resp['errorMessage']?? 'system error';
            return false;
        }

        $transaction->coment = $resp['uuid']??'';
        return ['url' => $resp['link']];
    }

    public function getStatusCodes(): array
    {
        return [
            PayoutTransaction::STATUS_ALFAKIT_PENDING,
            PayoutTransaction::STATUS_ALFAKIT_SUCCESS,
            PayoutTransaction::STATUS_ALFAKIT_FAIL,
        ];
    }
}
