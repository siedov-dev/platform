<?php


namespace App\Payin;


use App\MUser;
use App\PaymentsAPI\InterKassa\IkInrPtp;
use App\PaymentsAPI\InterKassa\InterKassa;
use App\PayoutTransaction;
use App\UserWallet;
use Illuminate\Support\Facades\Auth;

class IkInrPtpPayin extends Payin
{
    public function __construct(MUser $user)
    {
        parent::__construct($user);
        $this->name = "InterKassa";
        $this->type = self::TYPE_IKINR_PTP;
    }

    public function process($amount, $method, array $data, ?PayoutTransaction $transaction, int $method_id)
    {
        if ($transaction === null) $transaction = new PayoutTransaction();

        $method = explode('.', $method)[1];
        $user_id = Auth::id();
        /** @var MUser $user */
        $user = MUser::find($user_id);

        $currency = $this->user->currency;


        $transaction->user_id = $user->id;
        $transaction->hall_id = $user->hall_id;
        $transaction->date = date("Y-m-d H:i:s");
        $transaction->sum = $amount;
        $transaction->currency = $user->getWallet($currency)->getCurrencyName();
        $transaction->operation = 'Пополнение счета через InterKassa Inr P2P';
        $transaction->status_id = PayoutTransaction::STATUS_IK_PENDING;
        $transaction->save();

        $api = new IkInrPtp();
        $resp = $api->redirectToSci($transaction->id, $amount, $method, $user->getWallet($currency)->getCurrencyName());
        return ['url' => $resp];
    }

    public function getStatusCodes(): array
    {
        return [
            PayoutTransaction::STATUS_IK_PENDING,
            PayoutTransaction::STATUS_IK_SUCCESS,
            PayoutTransaction::STATUS_IK_FAIL,
        ];
    }
}
