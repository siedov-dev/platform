<?php


namespace App\Payout;


use App\ApplicationForPayment;
use App\Models\UserNotifications;
use App\MUser;
use App\PaymentsAPI\FreeKassa\FkWalletApi;
use App\PayoutTransaction;
use App\UserWallet;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class FreeKassaAutoPayout extends Payout implements AutoPayoutSystem
{
    private $api;

    public function __construct()
    {
        $this->api = new FkWalletApi();
    }

    public function checkParams(array $params): bool
    {
//        list($pay_system_id, $pay_method) = explode('/', $params['payment-method']);
        return true;
    }

    public function processAutoPayment(ApplicationForPayment $applicationForPayment)
    {
        if($applicationForPayment->confirmed == ApplicationForPayment::STATUS_AUTO_PAY_PENDING){

            $applicationForPayment->confirmed = ApplicationForPayment::STATUS_AUTO_PAY_PROCESS;
            $applicationForPayment->comentar = 'обрабатывается автовыплата';

            if($applicationForPayment->save()){

                $service_id = $this->api->getSystemId($applicationForPayment->system_pay);

                $result = $this->api->payout($applicationForPayment->id, $applicationForPayment->money, $applicationForPayment->card_number, $service_id);


                log::channel('payout')->info('freekassa payout result: '.var_export($result,true));

                if(empty($result)) {
                    $applicationForPayment->confirmed = ApplicationForPayment::STATUS_AUTO_PAY_ERROR;
                    $applicationForPayment->comentar = 'Ошибка запроса, останов...';
                    $applicationForPayment->save();
                }

                if($this->api->is_error){
                    $applicationForPayment->confirmed = ApplicationForPayment::STATUS_AUTO_PAY_ERROR;
                    $applicationForPayment->error = "".$this->api->error_descr;
                    $applicationForPayment->save();
                }

                if(empty($result['payment_id'])){
                    $applicationForPayment->confirmed = ApplicationForPayment::STATUS_AUTO_PAY_ERROR;
                    $applicationForPayment->error = "no payment id";
                    $applicationForPayment->save();
                }

                $applicationForPayment->piastrix_id = $result['payment_id'];
                $applicationForPayment->save();
            }
        } elseif($applicationForPayment->confirmed == ApplicationForPayment::STATUS_AUTO_PAY_PROCESS){

            if($applicationForPayment->piastrix_id > 0 ) {

                $tr_status = $this->api->getPayoutStatus($applicationForPayment->piastrix_id);

                Log::channel('pay')->info('freekassa payments status '.var_export($tr_status, true));

                if($tr_status === false || $this->api->is_error){
                    Log::channel('pay')->error('Can`t get transaction status');
                    $applicationForPayment->error = "Can`t get transaction status id#'.$applicationForPayment->piastrix_id";
                    $applicationForPayment->save();
                    return;
                }

                if($tr_status['status'] == 'Canceled'){
                    $applicationForPayment->confirmed = ApplicationForPayment::STATUS_AUTO_PAY_ERROR;
                    $applicationForPayment->error = "error status#".intval($tr_status['status']);
                    $applicationForPayment->save();
                    return;
                }

                if($tr_status['status'] == 'Completed') {
                    $applicationForPayment->confirmed = ApplicationForPayment::STATUS_AUTO_PAY_SUCCESS;
                    $applicationForPayment->comentar = "Завершено успешно";
                    $applicationForPayment->error = "";
                    $applicationForPayment->save();

                    $tr_id = DB::table('payout_transactions')
                        ->insertGetId([
                            'user_id'   => $applicationForPayment->user_id,
                            'hall_id'   => $applicationForPayment->hall_id,
                            'date'      => date('Y-m-d H:i:s'),
                            'currency' => UserWallet::currencyName($applicationForPayment->currency),
                            'operation' => 'Автовыплата freekassa',
                            'sum'       => $applicationForPayment->money,
                            'fee'       => $applicationForPayment->money * 0.07,
                            'total_sum' => $applicationForPayment->money * 1.07,
                            'status_id' => PayoutTransaction::PAID_OUT_VISA,
                        ]);

                    if($applicationForPayment->user_id) {
                        UserNotifications::notify($applicationForPayment->user_id, __('notify.title_payout'),
                            '<span class="gold-text">' . $applicationForPayment->money . ' ' . UserWallet::currencyName($applicationForPayment->currency) . '</span> ' . __('notify.payout_success'));
                    }
                }

            }

        }
    }
}
