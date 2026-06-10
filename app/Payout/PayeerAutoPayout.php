<?php


namespace App\Payout;


use App\ApplicationForPayment;
use App\Models\UserNotifications;
use App\PaymentsAPI\Payeer;
use App\UserWallet;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PayeerAutoPayout extends Payout implements AutoPayoutSystem
{
    private $payeer_api;

    public function __construct()
    {
        $this->payeer_api = new Payeer();
    }

    public function checkParams(array $params): bool
    {
        if(!preg_match('#^[Pp]{1}[0-9]{7,15}$#', $params['cardNumber'])) {
            $this->_error = __('wallet.invalid_account_no').' (P1234567)';
            return false;
        }

        $balance = $this->payeer_api->getBalance();
        if(!$balance || $balance['available'] < $params['sum']){
            log::channel('payout')->info('Payeer low balance '.var_export($balance,true));
//            session()->flash('error', 'Выплата временно не доступна');
//            return false;
        }

        log::channel('payout')->info('Payeer balance '.var_export($balance,true));

        if(!$this->payeer_api->accountExists($params['cardNumber'])){
            $this->_error = __('wallet.account_not_found');
            return false;
        }

        return true;
    }

    public function processAutoPayment(ApplicationForPayment $applicationForPayment)
    {
        if($applicationForPayment->confirmed == ApplicationForPayment::STATUS_AUTO_PAY_PENDING){

            $applicationForPayment->confirmed = ApplicationForPayment::STATUS_AUTO_PAY_PROCESS;
            $applicationForPayment->comentar = 'обрабатывается автовыплата';

            if($applicationForPayment->save()){

                $result = $this->payeer_api->transfer((float)$applicationForPayment->money, $applicationForPayment->card_number,
                    'payout #'.$applicationForPayment->id, $applicationForPayment->id, (float)$applicationForPayment->money);

                log::channel('payout')->info('result: '.var_export($result,true));

                if(empty($result) || $result === false){
                    $applicationForPayment->confirmed = ApplicationForPayment::STATUS_AUTO_PAY_ERROR;
                    $applicationForPayment->error = "payeer error";
                    $applicationForPayment->save();
                }else {
                    $applicationForPayment->piastrix_id = $result;
                    $applicationForPayment->save();
                }
            }
        } elseif($applicationForPayment->confirmed == ApplicationForPayment::STATUS_AUTO_PAY_PROCESS){

            if($applicationForPayment->piastrix_id >0 ) {
                $result = $this->payeer_api->paymentDetails($applicationForPayment->id);

                log::channel('payout')->info('result: '.var_export($result,true));

                if($result === false){
                    $applicationForPayment->confirmed = ApplicationForPayment::STATUS_AUTO_PAY_ERROR;
                    $applicationForPayment->error = "error, can't check status";
                    $applicationForPayment->save();
                }elseif(array_key_first($result) && $result[array_key_first($result)]['status'] == 'success'){
                    $applicationForPayment->confirmed = ApplicationForPayment::STATUS_AUTO_PAY_SUCCESS;
                    $applicationForPayment->comentar = "Завершено успешно";
                    $applicationForPayment->suma = $result[array_key_first($result)]['creditedAmount'];
                    $applicationForPayment->save();
                    // Записуємо транзакцію
                    DB::table('payout_transactions')
                        ->insert([
                            'user_id'   => $applicationForPayment->user_id,
                            'hall_id'   => $applicationForPayment->hall_id,
                            'date'      => date('Y-m-d H:i:s'),
                            'currency'  => 'RUB',
                            'operation' => 'Автовыплата payeer',
                            'sum'       => $applicationForPayment->money,
                            'fee'       => $result[array_key_first($result)]['payeerFee'],
                            'total_sum' => $result[array_key_first($result)]['debitedAmount'],
                            'status_id' => '45',
                        ]);

                    UserNotifications::notify($applicationForPayment->user_id, __('notify.title_payout'),
                        '<span class="gold-text">' . $applicationForPayment->money . ' ' . UserWallet::currencyName($applicationForPayment->currency) . '</span> ' . __('notify.payout_success'));

                } elseif(array_key_first($result) && $result[array_key_first($result)]['status'] == 'canceled'){
                    $applicationForPayment->confirmed = ApplicationForPayment::STATUS_AUTO_PAY_ERROR;
                    $applicationForPayment->error = 'status canceled';
                    $applicationForPayment->comentar = "Ошибка payeer";
                    $applicationForPayment->save();

                }

            }

        }
    }

}
