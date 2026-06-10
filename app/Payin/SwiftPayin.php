<?php


namespace App\Payin;


use App\Helpers\Popup;
use App\MUser;
use App\PaymentsAPI\SwiftPay\SwiftPay;
use App\PayoutTransaction;
use App\UserWallet;
use Illuminate\Support\Facades\Auth;

class SwiftPayin extends Payin
{
    public function __construct(MUser $user)
    {
        parent::__construct($user);
        $this->name = "SwiftPay";
        $this->type = self::TYPE_SWIFT;
        $this->min_sum = 300;
    }

    public function getPayMethods()
    {
        return [
            UserWallet::CURRENCY_RUB => [
                'swift.visa' => '/img/paysystem/swift_visa.svg',
                'swift.p2p' => '/img/paysystem/qiwi.svg',
            ],
        ];
    }

    public function process($amount, $method, array $data, ?PayoutTransaction $transaction, int $method_id)
    {
        if ($transaction === null) $transaction = new PayoutTransaction();
        $user_id = Auth::id();
        /** @var MUser $user */
        $user = MUser::find($user_id);

        $currency = $this->user->currency;
        $api = new SwiftPay();

        $transaction->user_id = $user->id;
        $transaction->hall_id = $user->hall_id;
        $transaction->date = date("Y-m-d H:i:s");
        $transaction->sum = $amount;
        $transaction->currency = $user->getWallet($currency)->getCurrencyName();
        $transaction->operation = 'Пополнение счета через SwiftPay';
        $transaction->status_id = PayoutTransaction::STATUS_SWIFT_PENDING;
        $transaction->save();

        if ($method == 'swift.visa') {
            $resp = $api->orderCreate($transaction->id, $amount, $transaction->id);
            return ['url' => $resp['data']['link'] ?? ''];
        }

        if ($method == 'swift.p2p'){
            $resp = $api->p2pCreatePayin($user_id);
            return ['url' => $resp ?? ''];
        }

        return '';
    }

    public function getStatusCodes(): array
    {
        return [
            PayoutTransaction::STATUS_SWIFT_PENDING,
            PayoutTransaction::STATUS_SWIFT_SUCCESS,
            PayoutTransaction::STATUS_SWIFT_FAIL,
        ];
    }
}
