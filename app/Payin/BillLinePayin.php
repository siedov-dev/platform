<?php


namespace App\Payin;


use App\Helpers\Currency;
use App\Helpers\Popup;
use App\Models\PaymentSystems;
use App\MUser;
use App\PaymentsAPI\BillLine;
use App\PaymentsAPI\SettlePay\SettlePay;
use App\PayoutTransaction;
use App\UserWallet;
use Illuminate\Support\Facades\Auth;

class BillLinePayin extends Payin
{

    public function __construct(MUser $user)
    {
        parent::__construct($user);
        $this->name = "billLine";
        $this->type = self::TYPE_BILLINE;
    }

    /**
     * can deposit only after 5 successful pays with other systems
     * @param float $amount
     * @param string $method
     * @param array $data
     * @param int $method_id
     * @return bool
     */
    public function canProceed(float $amount, string $method, array $data, int $method_id): bool
    {
        $this->_error = null;

//        $payed = PayoutTransaction::selectRaw('user_id, currency, sum(`sum`) as sum, count(id) as cnt')
//            ->where('user_id', $this->user->id)
//            ->whereIn('status_id', PayoutTransaction::getIncomingStatuses())
//            ->groupBy('user_id')
//            ->groupBy('currency')
//            ->first();

//        if(!$payed || $payed->cnt < 3){
//            $this->_error = __('wallet.required_other_payins', ['pay_count' => 3]);
//            return false;
//        }

        return parent::canProceed($amount, $method, $data, $method_id);
    }

    public function process($amount, $method, array $data, ?PayoutTransaction $transaction, int $method_id)
    {
        if ($transaction === null) $transaction = new PayoutTransaction();
        $user_id = Auth::id();
        /** @var MUser $user */
        $user = MUser::find($user_id);

        $currency = $this->user->currency;

        $uah_amount = $amount;
//        if ($currency != UserWallet::CURRENCY_UAH){
//            $uah_amount = ceil(Currency::convert($amount, $user->getWallet($currency)->getCurrencyName(), 'UAH'));
//        }

        $transaction->user_id = $user->id;
        $transaction->hall_id = $user->hall_id;
        $transaction->date = date("Y-m-d H:i:s");
        $transaction->sum = $amount;
        $transaction->currency = $user->getWallet($currency)->getCurrencyName();
        $transaction->operation = 'Пополнение счета через BillLine';
        $transaction->status_id = PayoutTransaction::STATUS_BL_PENDING;
        $transaction->save();

        if ($currency === UserWallet::CURRENCY_KZT) {
            $thread = 5;
        } else {
            $deps_count = PayoutTransaction::where('user_id', $user->id)
                ->whereIn('status_id', PayoutTransaction::getIncomingStatuses())->count('id');
            $thread = $deps_count > 3? 2 : 1;
            if ($method === 'billLine.UAH_USDEUR') $thread += 2;
            if ($thread === 2) $thread = 1; //disable second thread
        }

        $bl = new BillLine($thread);

//        return $bl->viewRedirectForm($transaction);
        return ['redirect_form' => $bl->viewRedirectForm([
            'id' => $transaction->id,
            'amount' => $uah_amount,
            'currency' => $user->getWallet($currency)->getCurrencyName(),
        ], $method, $data)];
    }

    public function getFormRequisites($method, ?PaymentSystems $pay_method): string
    {
        $deps_count = PayoutTransaction::where('user_id', Auth::id())
            ->whereIn('status_id', PayoutTransaction::getIncomingStatuses())->count('id');
        if ($method === 'billLine.UAH_USDEUR' && $deps_count > 3) {
            $email = $this->getLastRequisite('email')?? $this->user->email;
            $first_name = $this->getLastRequisite('from_first_name')?? '';
            $last_name = $this->getLastRequisite('from_last_name')?? '';
            $billing_street = $this->getLastRequisite('billing_street')?? '';
            $billing_town = $this->getLastRequisite('billing_town')?? '';
            $from_country = $this->getLastRequisite('from_country')?? '';
            $billing_post_code = $this->getLastRequisite('billing_post_code')?? '';

            return view('pay.requisites.payin.billine-usd', [
                'email' => $email,
                'from_first_name' => $first_name,
                'from_last_name' => $last_name,
                'billing_street' => $billing_street,
                'billing_town' => $billing_town,
                'from_country' => $from_country,
                'billing_post_code' => $billing_post_code,
                'method' => $method,
            ]);
        }

        $first_name = $this->getLastRequisite('first_name')?? '';
        $last_name = $this->getLastRequisite('last_name')?? '';

        return view('pay.requisites.payin.first-last-name', [
            'first_name' => $first_name,
            'last_name' => $last_name,
            'method' => $method,
        ]);
    }

    public function getStatusCodes(): array
    {
        return [
            PayoutTransaction::STATUS_BL_PENDING,
            PayoutTransaction::STATUS_BL_SUCCESS,
            PayoutTransaction::STATUS_BL_FAIL,
        ];
    }

}
