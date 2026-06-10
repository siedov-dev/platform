<?php


namespace App\Payout;


use App\ApplicationForPayment;
use App\Models\UserNotifications;
use App\MUser;
use App\PaymentsAPI\AliKassa\AliKassa;
use App\PayoutTransaction;
use App\UserWallet;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AliKassaAutoPayout extends Payout implements AutoPayoutSystem
{
    private $api;

    public function __construct()
    {
        $this->api = new AliKassa();
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
//        log::channel('payout')->info('ali processing application: '.var_export($applicationForPayment,true));
        /** @var MUser $user */
        $user = MUser::find($applicationForPayment->user_id);
        if(!$user) return;

        if($applicationForPayment->confirmed == ApplicationForPayment::STATUS_AUTO_PAY_PENDING){

            $applicationForPayment->confirmed = ApplicationForPayment::STATUS_AUTO_PAY_PROCESS;
            $applicationForPayment->comentar = 'обрабатывается автовыплата';

            if($applicationForPayment->save()){

                $tr_id = DB::table('payout_transactions')
                    ->insertGetId([
                        'user_id'   => $applicationForPayment->user_id,
                        'hall_id'   => $applicationForPayment->hall_id,
                        'date'      => date('Y-m-d H:i:s'),
                        'currency' => UserWallet::currencyName($applicationForPayment->currency),
                        'operation' => 'Автовыплата ali',
                        'sum'       => $applicationForPayment->money,
                        'fee'       => 0,
                        'total_sum' => 0,
                        'status_id' => PayoutTransaction::STATUS_ALI_PAYOUT,
                    ]);

                $applicationForPayment->piastrix_id = $tr_id;
                $applicationForPayment->save();

                log::channel('payout')->info('withdraw currency: '.var_export(UserWallet::currencyName($applicationForPayment->currency),true));
                log::channel('payout')->info('withdraw method: '.var_export($this->getPayoutService($applicationForPayment->system_pay),true));
                log::channel('payout')->info('withdraw amount: '.var_export(floatval($applicationForPayment->money),true));

                $result = $this->api->withdrawal($tr_id, floatval($applicationForPayment->money), $this->getPayoutService($applicationForPayment->system_pay),
                    UserWallet::currencyName($applicationForPayment->currency), $applicationForPayment->card_number);


                log::channel('payout')->info('ali payout result: '.var_export($result,true));

                if(empty($result)) {
                    $applicationForPayment->confirmed = ApplicationForPayment::STATUS_AUTO_PAY_ERROR;
                    $applicationForPayment->comentar = 'Ошибка запроса, останов...';
                    $applicationForPayment->save();
                    return;
                }

                if($result['success'] != 'true'){
                    $applicationForPayment->confirmed = ApplicationForPayment::STATUS_AUTO_PAY_ERROR;
                    $applicationForPayment->error = "".$result['error'];
                    $applicationForPayment->save();
                    return;
                }

                if(!isset($result['payStatus']) || in_array($result['payStatus'], ['canceled', 'fail'])){
                    $applicationForPayment->confirmed = ApplicationForPayment::STATUS_AUTO_PAY_ERROR;
                    $applicationForPayment->error = "".$result['payStatus'];
                    $applicationForPayment->save();
                }
            }
        } elseif($applicationForPayment->confirmed == ApplicationForPayment::STATUS_AUTO_PAY_PROCESS){

            if($applicationForPayment->piastrix_id > 0 ) {

                /** @var PayoutTransaction $transaction */
                $transaction = PayoutTransaction::find($applicationForPayment->piastrix_id);

                if(!$transaction){
                    Log::channel('pay')->error('Can`t get transaction id#'.$applicationForPayment->piastrix_id);
                    $applicationForPayment->error = "Can`t get transaction id#'.$applicationForPayment->piastrix_id";
                    $applicationForPayment->save();
                    return;
                }

                $tr_status = $this->api->withdrawalHistory(['orderId' => $transaction->id]);

                log::channel('payout')->info('ali application status: '.var_export($tr_status,true));


                if(empty($tr_status) || !empty($tr_status['return']['items'])){
                    Log::channel('pay')->error('Can`t get transaction status');
                    $applicationForPayment->error = "Can`t get transaction status id#'.$applicationForPayment->piastrix_id";
                    $applicationForPayment->save();
                    return;
                }

                $status = $tr_status['return']['items'][0];

                if(empty($tr_status['return']['items'])){
                    $transaction->error_code_id = 1;
                    $transaction->status_id = PayoutTransaction::STATUS_SETTLE_FAIL;
                    $transaction->save();

                    $applicationForPayment->confirmed = ApplicationForPayment::STATUS_AUTO_PAY_ERROR;
                    $applicationForPayment->error = "error status not found";
                    $applicationForPayment->save();
                    return;
                }

                if(in_array($status['payStatus'], ['canceled', 'fail'])){
                    $transaction->error_code_id = 1;
                    $transaction->status_id = PayoutTransaction::STATUS_SETTLE_FAIL;
                    $transaction->save();

                    $applicationForPayment->confirmed = ApplicationForPayment::STATUS_AUTO_PAY_ERROR;
                    $applicationForPayment->error = "error status#".$status;
                    $applicationForPayment->save();

                    return;
                }

                if($status['payStatus'] == 'success') {
                    $transaction->sum = $status['amount'];
                    $transaction->total_sum = abs($status["merchantCommission"]);
                    $transaction->fee = $transaction->total_sum - $transaction->sum;
                    $transaction->status_id = PayoutTransaction::PAID_OUT_VISA;
                    $transaction->save();

                    $applicationForPayment->confirmed = ApplicationForPayment::STATUS_AUTO_PAY_SUCCESS;
                    $applicationForPayment->comentar = "Завершено успешно";
                    $applicationForPayment->error = "";
                    $applicationForPayment->save();

                    if($applicationForPayment->user_id) {
                        UserNotifications::notify($applicationForPayment->user_id, __('notify.title_payout'),
                            '<span class="gold-text">' . $transaction->sum . ' ' . UserWallet::currencyName($applicationForPayment->currency) . '</span> ' . __('notify.payout_success'));
                    }
                }

            }

        }
    }

    public function getPayoutService($method)
    {
        switch ($method){
            case 'Ali.ymrub':
                return 'YandexMoney';
                break;
            case 'Ali.qiwirub':
                return 'Qiwi';
                break;
            case 'Ali.cardrub':
            case 'Ali.carduah':
                return 'Card';
                break;
            case 'Ali.aliuah':
                return 'AliKassa';
                break;
        }
        return 'unknown';
    }

    public function getPayMethods()
    {
        return [
            UserWallet::CURRENCY_RUB => [
//                'Ali.ymrub' => '/public/img/paysystem/ali/YandexMoney.svg',
//                'Ali.qiwirub' => '/public/img/paysystem/ali/Qiwi.svg',
//                'Ali.cardrub' => '/public/img/paysystem/ali/Card.svg',
            ],
            UserWallet::CURRENCY_UAH => [
//                'Ali.aliuah' => '/public/img/paysystem/alikassa.svg',
                'Ali.carduah' => '/img/paysystem/ali/Card.svg',
            ]
        ];
    }
}
