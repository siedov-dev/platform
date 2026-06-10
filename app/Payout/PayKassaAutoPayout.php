<?php


namespace App\Payout;


use App\ApplicationForPayment;
use App\Models\UserNotifications;
use App\MUser;
use App\PaymentsAPI\FreeKassa\FkWalletApi;
use App\PaymentsAPI\PayKassa\PayKassa;
use App\PayoutTransaction;
use App\UserWallet;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PayKassaAutoPayout extends Payout implements AutoPayoutSystem
{
    private PayKassa $api;

    public function __construct()
    {
        $this->api = new PayKassa();
    }

    public function checkParams(array $params): bool
    {
//        list($pay_system_id, $pay_method) = explode('/', $params['payment-method']);
        return true;
    }

    public function processAutoPayment(ApplicationForPayment $applicationForPayment)
    {
        if ($applicationForPayment->confirmed == ApplicationForPayment::STATUS_AUTO_PAY_PENDING) {

            $applicationForPayment->confirmed = ApplicationForPayment::STATUS_AUTO_PAY_PROCESS;
            $applicationForPayment->comentar = 'обрабатывается автовыплата';

            if ($applicationForPayment->save()) {


                $result = $this->api->payout($applicationForPayment->money, UserWallet::currencyName($applicationForPayment->currency),
                    $applicationForPayment->card_number, $applicationForPayment->system_pay);

                log::channel('payout')->info('PayKassa payout result: ' . var_export($result, true));

                if (empty($result)) {
                    $applicationForPayment->confirmed = ApplicationForPayment::STATUS_AUTO_PAY_ERROR;
                    $applicationForPayment->comentar = 'Ошибка запроса, останов...';
                    $applicationForPayment->save();
                }

                if ($result['error'] !== false) {
                    $applicationForPayment->confirmed = ApplicationForPayment::STATUS_AUTO_PAY_ERROR;
                    $applicationForPayment->error = $result['message']?? 'payout uknown error';
                    $applicationForPayment->save();
                }

                if (empty($result['payment_id'])) {
                    $applicationForPayment->confirmed = ApplicationForPayment::STATUS_AUTO_PAY_ERROR;
                    $applicationForPayment->error = "no payment id";
                    $applicationForPayment->save();
                }

                $applicationForPayment->outer_id  = $result['data']['transaction']?? '';
                $applicationForPayment->confirmed = ApplicationForPayment::STATUS_AUTO_PAY_SUCCESS;
                $applicationForPayment->save();

                if ($applicationForPayment->user_id) {
                    UserNotifications::notify($applicationForPayment->user_id, __('notify.title_payout'),
                        '<span class="gold-text">' . $applicationForPayment->money . ' ' . UserWallet::currencyName($applicationForPayment->currency) . '</span> ' . __('notify.payout_success'));
                }

            }
        }
    }
}
