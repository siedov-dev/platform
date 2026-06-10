<?php


namespace App\Payin;


use App\Helpers\Currency;
use App\Helpers\Popup;
use App\MUser;
use App\PayoutTransaction;
use App\UserWallet;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PerfectMoneyPayin extends Payin
{
    public function __construct(MUser $user)
    {
        parent::__construct($user);
        $this->name = "PerfectMoney";
        $this->type = self::TYPE_PM;
    }

    public function getPayMethods()
    {
        return [
            UserWallet::CURRENCY_RUB => [
                'pm' => '/img/enot/pm.png',
            ],
            UserWallet::CURRENCY_UAH => [
            ]
        ];
    }

    public function process($amount, $method, array $data, ?PayoutTransaction $transaction, int $method_id)
    {
        if ($transaction === null) $transaction = new PayoutTransaction();
        $user_id = $this->user->id;
        $user = $this->user;

        $transaction->user_id = $user->id;
        $transaction->hall_id = $user->hall_id;
        $transaction->date = date("Y-m-d H:i:s");
        $transaction->sum = $amount;
        $transaction->currency = 'RUB';
        $transaction->operation = 'Пополнение счета через Perfect Money';
        $transaction->status_id = PayoutTransaction::STATUS_PM_PENDING;
        $transaction->save();

        //convert amount to USD
        $usd = $this->convertRub($amount, 'USD');
        $usd = (float) ( ceil($usd * 100) ) /100;

        if($usd <= 0){
//            Popup::error(__('wallet.currency_convert_err'));
            $this->_error = __('wallet.currency_convert_err');
            return back();
        }

        return ['redirect_form' => view('pay.perfectmoney-form', ['transaction_id' => $transaction->id, 'amount' => $usd])->render()];
    }

    /**
     * Converts rub to another currency.
     * @param float $amount
     * @param string $currency Currency code (USD, UAH, etc.)
     * @return float
     */
    protected function convertRub($amount, string $currency)
    {
        $currency = strtoupper($currency);
        if (!array_key_exists($currency, Currency::getCourses())) {
            return $amount;
        }
        return $amount / Currency::getCourses()[$currency];
    }

    public function getStatusCodes(): array
    {
        return [
            PayoutTransaction::STATUS_PM_PENDING,
            PayoutTransaction::STATUS_PM_SUCCESS,
            PayoutTransaction::STATUS_PM_FAIL,
        ];
    }
}
