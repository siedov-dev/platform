<?php


namespace App\Payout;


use App\ApplicationForPayment;
use App\Models\UserNotifications;
use App\MUser;
use App\PaymentsAPI\InterKassa\InterKassa;
use App\PaymentsAPI\SwiftPay\SwiftPay;
use App\UserWallet;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class InterKassaAutoPayout extends Payout implements AutoPayoutSystem
{
    private $api;

    public function __construct()
    {
        $this->api = new InterKassa();
    }

    public function checkParams(array $params): bool
    {
        list($pay_system_id, $pay_method) = explode('/', $params['payment-method']);
        /** @var MUser $user */
//        $user = MUser::find(Auth::id());
        return true;
    }

    public function processAutoPayment(ApplicationForPayment $applicationForPayment)
    {
        if($applicationForPayment->confirmed == ApplicationForPayment::STATUS_AUTO_PAY_PENDING){

            $applicationForPayment->confirmed = ApplicationForPayment::STATUS_AUTO_PAY_PROCESS;
            $applicationForPayment->comentar = 'обрабатывается автовыплата';

            if($applicationForPayment->save()){

                $result = $this->api->createWithdraw((float)$applicationForPayment->money, $applicationForPayment->id, $applicationForPayment->card_number, $applicationForPayment->currency);

                log::channel('payout')->info('InterKassa create payout result: '.var_export($result,true));

                if(empty($result)) {
                    $applicationForPayment->confirmed = ApplicationForPayment::STATUS_AUTO_PAY_ERROR;
                    $applicationForPayment->comentar = 'Ошибка запроса, останов...';
                    $applicationForPayment->save();
                } elseif($result['status'] == 'error'){
                    $applicationForPayment->confirmed = ApplicationForPayment::STATUS_AUTO_PAY_ERROR;
                    $applicationForPayment->error = $result['status']."code ".$result['code']." ".$result['message'];
                    $applicationForPayment->save();
                }else {
                    $applicationForPayment->confirmed = ApplicationForPayment::STATUS_AUTO_PAY_PROCESS;
                    $applicationForPayment->piastrix_id = $result['data']['transaction']['id'];
                    $applicationForPayment->save();
                }
            }
        } elseif($applicationForPayment->confirmed == ApplicationForPayment::STATUS_AUTO_PAY_PROCESS){

            if($applicationForPayment->piastrix_id > 0 ) {
                $result = $this->api->payoutStatus((int)$applicationForPayment->id);

                log::channel('payout')->info('InterKassa payout status result: '.var_export($result,true));

                if(empty($result) || $result['status'] == 'error'){
                    $applicationForPayment->confirmed = ApplicationForPayment::STATUS_AUTO_PAY_ERROR;
                    $applicationForPayment->error = "response error";
                    $applicationForPayment->save();

                } elseif($result['status'] == 'ok' && in_array($result['data']['state'], [3 , 9])){
                    $applicationForPayment->confirmed = ApplicationForPayment::STATUS_AUTO_PAY_ERROR;
                    $applicationForPayment->error = "status ".$result['data']['state'];
                    $applicationForPayment->save();

                } elseif($result['status'] == 'ok' && $result['data']['state'] == 8){
                    $applicationForPayment->confirmed = ApplicationForPayment::STATUS_AUTO_PAY_SUCCESS;
                    $applicationForPayment->comentar = "Завершено успешно";
                    $applicationForPayment->suma = $result['data']['psAmount'];
                    $applicationForPayment->save();
                    $fee = $result['data']['ikFee'];
                    // Записуємо транзакцію
                    DB::table('payout_transactions')
                        ->insert([
                            'user_id'   => $applicationForPayment->user_id,
                            'hall_id'   => $applicationForPayment->hall_id,
                            'date'      => date('Y-m-d H:i:s'),
                            'currency' => UserWallet::currencyName($applicationForPayment->currency),
                            'operation' => 'Автовыплата InterKassa',
                            'sum'       => $applicationForPayment->money,
                            'fee'       => $fee,
                            'total_sum' => $applicationForPayment->money + $fee,
                            'status_id' => '45',
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
