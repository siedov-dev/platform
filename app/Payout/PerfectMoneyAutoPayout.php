<?php


namespace App\Payout;

use App\ApplicationForPayment;
use App\Helpers\Currency;
use App\Models\UserNotifications;
use App\PaymentsAPI\PerfectMoney;
use App\UserWallet;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PerfectMoneyAutoPayout extends Payout implements AutoPayoutSystem
{
    private $pm;

    public function __construct()
    {
        $this->pm = new PerfectMoney();
    }

    public function checkParams(array $params): bool
    {
        if(!preg_match('#^[Uu]{1}[0-9]{5,15}$#', $params['cardNumber'])) {
            $this->_error = __('wallet.invalid_account_no').' (U1234567)';
            return false;
        }

        $usd_amount =  floor($params['sum'] / Currency::getCourses()['USD'] * 100) /100;

        $balance = $this->pm->getBalance();
        if(!$balance || $balance < $usd_amount){
            log::channel('payout')->info('PerfectMoney low balance '.var_export($balance,true));
        }

        log::channel('payout')->info('PerfectMoney balance '.var_export($balance,true));

        $acc_name = $this->pm->getAccountName($params['cardNumber']);

        if($acc_name == 'ERROR: Invalid Account')
        {
            $this->_error = __('wallet.account_not_found');
            return false;
        }

        log::channel('payout')->info('PerfectMoney acc_name '.var_export($acc_name,true));

        return true;
    }

    public function processAutoPayment(ApplicationForPayment $applicationForPayment)
    {
        if($applicationForPayment->confirmed == ApplicationForPayment::STATUS_AUTO_PAY_PENDING){

            $usd_amount =  floor((float)$applicationForPayment->money / Currency::getCourses()['USD'] * 100) /100;

            $applicationForPayment->confirmed = ApplicationForPayment::STATUS_AUTO_PAY_PROCESS;
            $applicationForPayment->comentar = 'обрабатывается автовыплата';

            if($applicationForPayment->save()){

                $result = $this->pm->transfer($usd_amount, $applicationForPayment->card_number, $applicationForPayment->id);

                log::channel('payout')->info('result: '.var_export($result,true));

                if(empty($result) || $result === false || !is_array($result) || array_key_exists('ERROR', $result) || !array_key_exists('PAYMENT_BATCH_NUM', $result)){
                    $applicationForPayment->confirmed = ApplicationForPayment::STATUS_AUTO_PAY_ERROR;
                    $applicationForPayment->error = "PerfectMoney error";
                    $applicationForPayment->save();
                }else {
                    $applicationForPayment->piastrix_id = $result['PAYMENT_BATCH_NUM'];
                    $applicationForPayment->confirmed = ApplicationForPayment::STATUS_AUTO_PAY_SUCCESS;
                    $applicationForPayment->comentar = "Завершено успешно";
                    $applicationForPayment->suma = $result['PAYMENT_AMOUNT'];
                    $applicationForPayment->save();

                    // Записуємо транзакцію
                    DB::table('payout_transactions')
                        ->insert([
                            'user_id'   => $applicationForPayment->user_id,
                            'hall_id'   => $applicationForPayment->hall_id,
                            'date'      => date('Y-m-d H:i:s'),
                            'currency' => 'RUB',
                            'operation' => 'Автовыплата PerfectMoney',
                            'sum'       => $applicationForPayment->money,
                            'fee'       => $applicationForPayment->money * 0.02,
                            'total_sum' => $applicationForPayment->money * 1.02,
                            'status_id' => '45',
                        ]);

                    UserNotifications::notify($applicationForPayment->user_id, __('notify.title_payout'),
                        '<span class="gold-text">' . $applicationForPayment->money . ' ' . UserWallet::currencyName($applicationForPayment->currency) . '</span> ' . __('notify.payout_success'));
                }
            }
        }
    }
}
