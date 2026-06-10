<?php


namespace App\Payout;


use App\ApplicationForPayment;
use App\Models\UserNotifications;
use App\MUser;
use App\PaymentsAPI\SettlePay\SettlePay;
use App\PayoutTransaction;
use App\UserWallet;
use App\Wallet;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SettleAutoPayout extends Payout implements AutoPayoutSystem
{
    private $api;

    public function __construct()
    {
        $this->api = new SettlePay();
    }

    public function checkParams(array $params): bool
    {
        list($pay_system_id, $pay_method) = explode('/', $params['payment-method']);
        /** @var MUser $user */
        $user = MUser::find(Auth::id());
        if ($this->api->getBalance(UserWallet::currencyName($params['currency'])) < floatval($params['sum'])) {
            $this->_error = __('wallet.paysystem_balance_error');
//            return false;
        }
        return true;
    }

    public function processAutoPayment(ApplicationForPayment $applicationForPayment)
    {
        /** @var MUser $user */
        $user = MUser::find($applicationForPayment->user_id);
        if (!$user) return;

        //just to set mode
//        if(!$this->canProceed($applicationForPayment->money, $applicationForPayment->system_pay, $user->getWallet($applicationForPayment->currency))) return;
        if ($applicationForPayment->currency == UserWallet::CURRENCY_RUB) {
            $this->api->setMode(SettlePay::MODE_RUB_UNVERIFIED);
        } else {
            $this->api->setMode(SettlePay::MODE_VERIFIED);
        }

        if ($applicationForPayment->confirmed == ApplicationForPayment::STATUS_AUTO_PAY_PENDING) {

            $applicationForPayment->confirmed = ApplicationForPayment::STATUS_AUTO_PAY_PROCESS;
            $applicationForPayment->comentar = 'обрабатывается автовыплата';

            if ($applicationForPayment->save()) {

                $tr_id = DB::table('payout_transactions')
                    ->insertGetId([
                        'user_id' => $applicationForPayment->user_id,
                        'hall_id' => $applicationForPayment->hall_id,
                        'date' => date('Y-m-d H:i:s'),
                        'currency' => UserWallet::currencyName($applicationForPayment->currency),
                        'operation' => 'Автовыплата settle',
                        'sum' => $applicationForPayment->money,
                        'fee' => 0,
                        'total_sum' => 0,
                        'status_id' => PayoutTransaction::STATUS_SETTLE_PAYOUT,
                    ]);

                $applicationForPayment->piastrix_id = $tr_id;
                $applicationForPayment->save();

                $result = $this->api->transactionPay($tr_id, $applicationForPayment->id, Auth::id(), request()->ip(),
                    intval($applicationForPayment->money * 100), UserWallet::currencyName($applicationForPayment->currency),
                    $this->api->getPayoutService(), $applicationForPayment->card_number);


                log::channel('payout')->info('settle payout result: ' . var_export($result, true));

                if (empty($result)) {
                    $applicationForPayment->confirmed = ApplicationForPayment::STATUS_AUTO_PAY_ERROR;
                    $applicationForPayment->comentar = 'Ошибка запроса, останов...';
                    $applicationForPayment->save();
                }

                if ($this->api->is_error) {
                    $applicationForPayment->confirmed = ApplicationForPayment::STATUS_AUTO_PAY_ERROR;
                    $applicationForPayment->error = "" . $this->api->error_descr;
                    $applicationForPayment->save();
                }
            }
        } elseif ($applicationForPayment->confirmed == ApplicationForPayment::STATUS_AUTO_PAY_PROCESS) {

            if ($applicationForPayment->piastrix_id > 0) {

                /** @var PayoutTransaction $transaction */
                $transaction = PayoutTransaction::find($applicationForPayment->piastrix_id);

                if (!$transaction) {
                    Log::channel('pay')->error('Can`t get transaction id#' . $applicationForPayment->piastrix_id);
                    $applicationForPayment->error = "Can`t get transaction id#'.$applicationForPayment->piastrix_id";
                    $applicationForPayment->save();
                    return;
                }

                $tr_status = $this->api->transactionFind($transaction->id);

                if ($tr_status === false || $this->api->is_error) {
                    Log::channel('pay')->error('Can`t get transaction status');
                    $applicationForPayment->error = "Can`t get transaction status id#'.$applicationForPayment->piastrix_id";
                    $applicationForPayment->save();
                    return;
                }

                if ($tr_status['status'] != SettlePay::STATUS_SUCCESS && $this->api->isFinalStatus($tr_status['status'])) {
                    $transaction->error_code_id = intval($tr_status['status']);
                    $transaction->status_id = PayoutTransaction::STATUS_SETTLE_FAIL;
                    $transaction->save();

                    $applicationForPayment->confirmed = ApplicationForPayment::STATUS_AUTO_PAY_ERROR;
                    $applicationForPayment->error = "error status#" . intval($tr_status['status']);
                    $applicationForPayment->save();

                    return;
                }

                if ($tr_status['status'] == SettlePay::STATUS_SUCCESS) {
                    $transaction->sum = $tr_status['amount'] / 100;
                    $transaction->total_sum = abs($tr_status["account_wallet_amount"]) / 100;
                    $transaction->fee = $transaction->total_sum - $transaction->sum;
                    $transaction->status_id = PayoutTransaction::PAID_OUT_VISA;
                    $transaction->save();

                    $applicationForPayment->confirmed = ApplicationForPayment::STATUS_AUTO_PAY_SUCCESS;
                    $applicationForPayment->comentar = "Завершено успешно";
                    $applicationForPayment->error = "";
                    $applicationForPayment->save();

                    if ($applicationForPayment->user_id) {
                        UserNotifications::notify($applicationForPayment->user_id, __('notify.title_payout'),
                            '<span class="gold-text">' . $applicationForPayment->money . ' ' . UserWallet::currencyName($applicationForPayment->currency) . '</span> ' . __('notify.payout_success'));
                    }
                }

            }

        }
    }

    /**
     * can deposit only after 5 successful pays with other systems
     * @param float $amount
     * @param string $method
     * @param UserWallet $wallet
     * @return bool
     */
    public function canProceed(float $amount, string $method, Wallet $wallet): bool
    {
        return parent::canProceed($amount, $method, $wallet);
        /** @var MUser $user */
        $user = $wallet->getUser();

        $currency = $method == 'Settle.RUB' ? 'RUB' : 'UAH';
        $limit = $currency == 'RUB' ? 1500 : 500;

        $payed = PayoutTransaction::selectRaw('user_id, currency, sum(`sum`) as sum, count(id) as cnt')
            ->where('user_id', $user->id)
            ->whereIn('status_id', PayoutTransaction::getIncomingStatuses())
            ->where('currency', $currency)
            ->groupBy('user_id')
            ->groupBy('currency')
            ->first();

        if (!$payed || $payed->sum < $limit || $payed->cnt < 5) {
//            $this->_error = "Для использовании этой системы выплат сделайте 5 пополнений на сумму $limit $currency";
            $this->_error = __('wallet.required_other_payins_with_sum', ['count' => 5, 'amount' => $limit, 'curr' => $currency]);
            return false;
        }

        $this->api->setMode(SettlePay::MODE_VERIFIED);
        return true;
    }
}
