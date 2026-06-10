<?php


namespace App\Payin;


use App\Helpers\Popup;
use App\MUser;
use App\PaymentsAPI\ObmenkaUa;
use App\PayoutTransaction;
use App\UserWallet;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;

class ObmenkaUaPayin extends Payin
{
    public function __construct(MUser $user)
    {
        parent::__construct($user);
        $this->name = "Obmenka.Ua";
        $this->type = self::TYPE_OBMENKA;
    }

    public function getPayMethods()
    {
        return [
            UserWallet::CURRENCY_RUB => [
                'Obmenka.visamaster.rur' => '/img/paysystem/obmenka/visacard_1.png',
                'Obmenka.wmr' => '/img/paysystem/obmenka/webmoney_1.png',
                'Obmenka.qiwi' => '/img/33.png',
            ],
            UserWallet::CURRENCY_UAH => [
                'Obmenka.visamaster.uah' => '/img/paysystem/obmenka/visacard_1.png',
            ]
        ];
    }

    public function process($amount, $method, array $data, ?PayoutTransaction $transaction, int $method_id)
    {
        if ($transaction === null) $transaction = new PayoutTransaction();
        $user = $this->user;

        $currency = $user->currency;

        if ($transaction === null) $transaction = new PayoutTransaction();
        $transaction->user_id = $this->user->id;
        $transaction->hall_id = $this->user->hall_id;
        $transaction->date = date("Y-m-d H:i:s");
        $transaction->sum = $amount;
        $transaction->currency =$this->user->getWallet()->getCurrencyName();
        $transaction->operation = 'Пополнение счета через Obmenka';
        $transaction->status_id = PayoutTransaction::STATUS_OBMENKA_PENDING;
        $transaction->save();

        $api = new ObmenkaUa();

        $api->createBill($transaction->id, ObmenkaUa::getObmenkaCurrency($method), $amount);
        if($api->isError){
            Popup::error($api->errDescr);
            return ['error' => $api->errDescr];
        }

        $url = $api->processBill($transaction->id);
        if($api->isError){
            Popup::error($api->errDescr);
            return ['error' => $api->errDescr];
        }

        return ['url' => $url];
    }

    public function getStatusCodes(): array
    {
        return [
            PayoutTransaction::STATUS_OBMENKA_PENDING,
            PayoutTransaction::STATUS_OBMENKA_SUCCESS,
            PayoutTransaction::STATUS_OBMENKA_FAIL,
        ];
    }
}
