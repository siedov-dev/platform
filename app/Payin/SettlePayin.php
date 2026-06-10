<?php


namespace App\Payin;


use App\Helpers\Popup;
use App\Models\PaymentSystems;
use App\MUser;
use App\PaymentsAPI\SettlePay\SettlePay;
use App\PayoutTransaction;
use App\UserWallet;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SettlePayin extends Payin
{
    protected $api;

    public function __construct(MUser $user)
    {
        parent::__construct($user);
        $this->name = "Settlepay";
        $this->type = self::TYPE_SETTLEPAY;
        $this->api = new SettlePay();
    }

    public function getPayMethods()
    {
        return [
            UserWallet::CURRENCY_RUB => [
                'Settle.RUB' => '/img/paysystem/settle.png',
            ],
            UserWallet::CURRENCY_UAH => [
                'Settle.UAH' => '/img/paysystem/settle.png',
            ]
        ];
    }

    /**
     * can deposit only after 5 successful pays with other systems
     * @param float $amount
     * @param string $method
     * @param array $data
     * @param int $method_id
     * @return bool
     * @throws \Exception
     */
    public function canProceed(float $amount, string $method, array $data, int $method_id): bool
    {
        $pay_system = PaymentSystems::getPayinSystemForMethod($method);

        if ($pay_system === null) {
            Log::channel('pay')->error('PaymentSystems not found for alias ' . $method);
            $this->_error = "system error";
            throw new \Exception('PaymentSystems not found for alias ' . $method);
        }

        if ($pay_system->additionalCheck()) {
            $currency = $method == 'Settle.RUB' ? 'RUB' : 'UAH';
            $limit = $currency == 'RUB' ? 1500 : 500;

            $payed = PayoutTransaction::selectRaw('user_id, currency, sum(`sum`) as sum, count(id) as cnt')
                ->where('user_id', $this->user->id)
                ->whereIn('status_id', PayoutTransaction::getIncomingStatuses())
                ->where('currency', $currency)
                ->groupBy('user_id')
                ->groupBy('currency')
                ->first();

            if (!$payed || $payed->sum < $limit || $payed->cnt < 5) {
//            Popup::error("Для использовании этой системы платежа сделайте 5 пополнений на сумму $limit $currency в других доступных системах");
                if ($currency === 'RUB') {
//                $this->_error = "Для использовании этой системы платежа сделайте 5 пополнений на сумму $limit $currency в других доступных системах";
                    $this->_error = __('wallet.required_other_payins_with_sum', ['count' => 5, 'amount' => $limit, 'curr' => $currency]);
                    return false;
                }
                return true;
            }
        }

        if ($currency === 'RUB') {
            $this->api->setMode(SettlePay::MODE_RUB_UNVERIFIED);
        } else {
            $this->api->setMode(SettlePay::MODE_VERIFIED);
        }

        return parent::canProceed($amount, $method, $data, $method_id);
    }

    public function process($amount, $method, array $data, ?PayoutTransaction $transaction, int $method_id)
    {
        if ($transaction === null) $transaction = new PayoutTransaction();
        //just to set mode
        if (!$this->canProceed($amount, $method, [])) return back();

        $currency = $method == 'Settle.RUB' ? UserWallet::CURRENCY_RUB : UserWallet::CURRENCY_UAH;

        $transaction->user_id = $this->user->hall_id;
        $transaction->hall_id = $this->user->id;
        $transaction->date = date("Y-m-d H:i:s");
        $transaction->sum = $amount;
        $transaction->currency = $this->user->getWallet($currency)->getCurrencyName();
        $transaction->operation = 'Пополнение счета через SettlePay';
        $transaction->status_id = PayoutTransaction::STATUS_SETTLE_PENDING;
        $transaction->save();

        $result = $this->api->transactionCreate($transaction->id, $this->user->id, request()->ip(), intval($amount * 100),
            $this->user->getWallet($currency)->getCurrencyName(), $this->api->getPayinService());

        if ($this->api->is_error) {
            return ['error' => $this->api->error_descr];
        }

        return ['url' => $result['result']['pay_url']];
    }

    public function getStatusCodes(): array
    {
        return [
            PayoutTransaction::STATUS_SETTLE_PENDING,
            PayoutTransaction::STATUS_SETTLE_SUCCESS,
            PayoutTransaction::STATUS_SETTLE_FAIL,
        ];
    }
}
