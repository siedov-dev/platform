<?php


namespace App\Payin;


use App\MUser;
use App\PaymentsAPI\AliKassa\AliKassa;
use App\PayoutTransaction;
use App\UserWallet;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;

class AliKassaPayin extends Payin
{
    protected $api;

    public function __construct(MUser $user)
    {
        parent::__construct($user);
        $this->name = "FreeKassa";
        $this->type = self::TYPE_ALIKASSA;
        $this->api = new AliKassa();
    }

    public function getPayMethods()
    {
        return [
            UserWallet::CURRENCY_RUB => [
                'Ali.rub' => '/img/paysystem/alikassa.svg',
            ],
            UserWallet::CURRENCY_UAH => [
                'Ali.uah' => '/img/paysystem/alikassa.svg',
            ]
        ];
    }

    public function process($amount, $method, array $data, ?PayoutTransaction $transaction, int $method_id)
    {
        if ($transaction === null) $transaction = new PayoutTransaction();
        $transaction->user_id = $this->user->id;
        $transaction->hall_id = $this->user->hall_id;
        $transaction->date = date("Y-m-d H:i:s");
        $transaction->sum = $amount;
        $transaction->currency = $this->user->getWallet()->getCurrencyName();
        $transaction->operation = 'Пополнение AliKassa';
        $transaction->status_id = PayoutTransaction::STATUS_ALI_PENDING;
        $transaction->save();

        return ['redirect_form' => $this->api->viewRedirectForm($transaction)];
    }

    public function getStatusCodes(): array
    {
        return [
            PayoutTransaction::STATUS_ALI_PENDING,
            PayoutTransaction::STATUS_ALI_SUCCESS,
            PayoutTransaction::STATUS_ALI_FAIL,
//            PayoutTransaction::STATUS_ALI_PAYOUT,
        ];
    }
}
