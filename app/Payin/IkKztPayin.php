<?php
namespace App\Payin;

use App\Models\PaymentSystems;
use App\MUser;
use App\PaymentsAPI\InterKassa\IkKzt;
use App\PaymentsAPI\InterKassa\InterKassa;
use App\PayoutTransaction;
use App\UserWallet;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class IkKztPayin extends Payin
{
    public function __construct(MUser $user)
    {
        parent::__construct($user);
        $this->name = "IkKzt";
        $this->type = self::TYPE_IKKZT;
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
        $transaction->operation = 'Пополнение счета через InterKassa';
        $transaction->status_id = PayoutTransaction::STATUS_IK_PENDING;
        $transaction->save();

        $api = new IkKzt($method, $user);
        $method = explode('.', $method)[1];
        $resp = $api->redirectToSci($transaction->id, $amount, $method, $user->getWallet($currency)->getCurrencyName());
        return ['url' => $resp];
    }

    public function isBlocked($method): bool
    {
        $this->_error = null;

        $pay_system = PaymentSystems::getPayinSystemForMethod($method);

        if ($pay_system === null) {
            Log::channel('pay')->error('PaymentSystems not found for alias ' . $method);
            $this->_error = "system error";
            throw new \Exception('PaymentSystems not found for alias ' . $method);
        }

        if ($pay_system->additionalCheck()) {
            $payed = PayoutTransaction::selectRaw('user_id, sum(`sum`) as sum, count(id) as cnt')
                ->where('user_id', $this->user->id)
                ->whereIn('status_id', PayoutTransaction::getIncomingStatuses())
                ->groupBy('user_id')
                ->first();

            if (!$payed || $payed->cnt < 3) {
                return true;
            }
        }

        return false;
    }

    public function blockReason($method): string
    {
        return __('wallet.required_other_payins', ['pay_count' => 3]);
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
