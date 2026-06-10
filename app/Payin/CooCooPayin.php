<?php

namespace App\Payin;

use App\MUser;
use App\PaymentsAPI\CooCooPay\CooCooPay;
use App\PayoutTransaction;

final class CooCooPayin extends Payin
{

    public function __construct(MUser $user)
    {
        parent::__construct($user);
        $this->name = "CooCooPay";
        $this->type = self::TYPE_COOCOOPAY;
    }

    public function process($amount, $method, array $data, ?PayoutTransaction $transaction, int $method_id)
    {
        if ($transaction === null) $transaction = new PayoutTransaction();

        $user = MUser::getAuth();

        $currency = $this->user->currency;

        $transaction->user_id = $user->id;
        $transaction->hall_id = $user->hall_id;
        $transaction->date = date("Y-m-d H:i:s");
        $transaction->sum = $amount;
        $transaction->currency = $user->getWallet($currency)->getCurrencyName();
        $transaction->operation = 'Пополнение счета через CooCooPay';
        $transaction->status_id = PayoutTransaction::STATUS_COOCOO_PENDING;
        $transaction->save();

        $api = new CooCooPay();
        $method = explode('.', $method)[1];
        $trusted = $user->getDepositsCount() >= 3;

        $resp = $api->deposit(
            tr_id: $transaction->id,
            client_id: $user->getId(),
            amount: $amount,
            currency: $user->getWallet($currency)->getCurrencyName(),
            trusted: $trusted,
        );

        if (empty($resp) || !empty($resp['error'])) {
            $this->_error = $resp['error']['code'] ?? 'system error';
            return false;
        }

        $transaction->o_tr_id = $resp['data']['id'];
        $transaction->save();

        return ['url' => $resp['data']['auth']['redirectUrl'], 'by_link' => true,];
    }

    public function getStatusCodes(): array
    {
        return [
            PayoutTransaction::STATUS_COOCOO_PENDING,
            PayoutTransaction::STATUS_COOCOO_SUCCESS,
            PayoutTransaction::STATUS_COOCOO_FAIL,
        ];
    }
}
