<?php


namespace App\Payout;


use App\ApplicationForPayment;
use App\Models\UserNotifications;
use App\MUser;
use App\PaymentsAPI\NashObmen\NashObmen;
use App\PayoutTransaction;
use App\UserWallet;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class NashObmenPayout extends Payout implements AutoPayoutSystem
{
    private NashObmen $api;

    public function __construct()
    {
        $this->api = new NashObmen();
    }

    public function checkParams(array $params): bool
    {
        return true;
    }

    public function processAutoPayment(ApplicationForPayment $applicationForPayment)
    {
        if ($applicationForPayment->confirmed == ApplicationForPayment::STATUS_AUTO_PAY_PENDING) {

            $applicationForPayment->confirmed = ApplicationForPayment::STATUS_AUTO_PAY_PROCESS;
            $applicationForPayment->comentar = 'обрабатывается автовыплата';

            if ($applicationForPayment->save()) {


                $result = $this->api->payout($applicationForPayment->id, $applicationForPayment->money, UserWallet::currencyName($applicationForPayment->currency),
                    $applicationForPayment->card_number, $applicationForPayment->system_pay);

                log::channel('payout')->info('NashObmen payout result: ' . var_export($result, true));

                // if (!isset($result['status'])) {
                //     $applicationForPayment->confirmed = ApplicationForPayment::STATUS_AUTO_PAY_ERROR;
                //     $applicationForPayment->comentar = 'Ошибка запроса, останов...';
                //     $applicationForPayment->save();
                //     return false;
                // }

                

                // $applicationForPayment->outer_id  = '';
                // $applicationForPayment->confirmed = ApplicationForPayment::STATUS_AUTO_PAY_SUCCESS;
                // $applicationForPayment->save();

                if ($applicationForPayment->user_id) {
                    UserNotifications::notify($applicationForPayment->user_id, __('notify.title_payout'),
                        '<span class="gold-text">' . $applicationForPayment->money . ' ' . UserWallet::currencyName($applicationForPayment->currency) . '</span> ' . __('notify.payout_success'));
                }

            }
        }
    }
}
