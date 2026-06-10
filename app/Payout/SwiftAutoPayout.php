<?php


namespace App\Payout;


use App\Models\UserNotifications;
use App\MUser;
use App\PaymentsAPI\SwiftPay\SwiftPay;
use App\UserWallet;
use Illuminate\Support\Facades\Auth;
use App\ApplicationForPayment;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SwiftAutoPayout extends Payout implements AutoPayoutSystem
{
    private $api;

    public function __construct()
    {
        $this->api = new SwiftPay();
    }

    public function checkParams(array $params): bool
    {
        list($pay_system_id, $pay_method) = explode('/', $params['payment-method']);
        /** @var MUser $user */
        $user = MUser::find(Auth::id());
        return true;
    }

    public function processAutoPayment(ApplicationForPayment $applicationForPayment)
    {
        if($applicationForPayment->confirmed == ApplicationForPayment::STATUS_AUTO_PAY_PENDING){

            $applicationForPayment->confirmed = ApplicationForPayment::STATUS_AUTO_PAY_PROCESS;
            $applicationForPayment->comentar = 'обрабатывается автовыплата';

            if($applicationForPayment->save()){

                $result = $this->api->createPayout((float)$applicationForPayment->money, $applicationForPayment->card_number);

                log::channel('payout')->info('Swift create payout result: '.var_export($result,true));

                if(empty($result)) {
                    $applicationForPayment->confirmed = ApplicationForPayment::STATUS_AUTO_PAY_ERROR;
                    $applicationForPayment->comentar = 'Ошибка запроса, останов...';
                    $applicationForPayment->save();
                } elseif(isset($result['errorStatus']) && $result['errorStatus'] != 0){
                    $applicationForPayment->confirmed = ApplicationForPayment::STATUS_AUTO_PAY_ERROR;
                    $applicationForPayment->error = "".$result['errorStatus']." ".$result['error]'];
                    $applicationForPayment->save();
                }else {
                    $applicationForPayment->piastrix_id = $result['data']['id'];
                    $applicationForPayment->save();
                }
            }
        } elseif($applicationForPayment->confirmed == ApplicationForPayment::STATUS_AUTO_PAY_PROCESS){

            if($applicationForPayment->piastrix_id > 0 ) {
                $result = $this->api->payoutStatus((int)$applicationForPayment->piastrix_id);

                log::channel('payout')->info('Swift payout status result: '.var_export($result,true));

                if(empty($result) || !empty($result['error'])){
                    $applicationForPayment->confirmed = ApplicationForPayment::STATUS_AUTO_PAY_ERROR;
                    $applicationForPayment->error = "".($result['error']??'response error');
                    $applicationForPayment->save();
                }elseif($result['data']['status'] == 3){ //todo check response
                    $applicationForPayment->confirmed = ApplicationForPayment::STATUS_AUTO_PAY_ERROR;
                    $applicationForPayment->comentar = "Отклонен";
                    $applicationForPayment->save();

                }elseif($result['data']['status'] == 2){ //todo check response
                    $applicationForPayment->confirmed = ApplicationForPayment::STATUS_AUTO_PAY_SUCCESS;
                    $applicationForPayment->comentar = "Завершено успешно";
                    $applicationForPayment->suma = $applicationForPayment->money;
                    $applicationForPayment->save();
                    $fee = $applicationForPayment->money * 0.02;
                    // Записуємо транзакцію
                    DB::table('payout_transactions')
                        ->insert([
                            'user_id'   => $applicationForPayment->user_id,
                            'hall_id'   => $applicationForPayment->hall_id,
                            'date'      => date('Y-m-d H:i:s'),
                            'currency' => UserWallet::currencyName($applicationForPayment->currency),
                            'operation' => 'Автовыплата SwiftPay',
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
