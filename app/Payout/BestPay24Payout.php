<?php

namespace App\Payout;

use App\ApplicationForPayment;
use App\Models\PaymentSystems;
use App\Models\UserNotifications;
use App\MUser;
use App\PaymentsAPI\BestPay24\BestPay24;
use App\UserWallet;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class BestPay24Payout extends Payout implements AutoPayoutSystem
{
    private BestPay24 $api;

    public function __construct()
    {
        $this->api = new BestPay24();
    }

    public function checkParams(array $params): bool
    {
        if (empty($params['accountNum'])) {
            $this->_error = "Please enter account number";
            return false;
        }
        if (empty($params['accountName'])) {
            $this->_error = "Please enter account name";
            return false;
        }
        if (empty($params['accountIFSC'])) {
            $this->_error = "Please enter account IFSC";
            return false;
        }
        if (empty($params['bankCode'])) {
            $this->_error = "Please enter bank code";
            return false;
        }

        return true;
    }

    public function processAutoPayment(ApplicationForPayment $applicationForPayment)
    {
        if ($applicationForPayment->confirmed == ApplicationForPayment::STATUS_AUTO_PAY_PENDING) {

            $applicationForPayment->confirmed = ApplicationForPayment::STATUS_AUTO_PAY_PROCESS;
            $applicationForPayment->comentar = 'обрабатывается автовыплата';

            if ($applicationForPayment->save()) {


                $result = $this->api->payout($applicationForPayment->id, $applicationForPayment->money, UserWallet::currencyName($applicationForPayment->currency),
                    $applicationForPayment->card_number, $applicationForPayment->system_pay, json_decode($applicationForPayment->requisites, true));

                log::channel('payout')->info('BestPay24 payout result: ' . var_export($result, true));

                if (empty($result) ) {
                    $applicationForPayment->confirmed = ApplicationForPayment::STATUS_AUTO_PAY_ERROR;
                    $applicationForPayment->comentar = 'Ошибка запроса, останов...';
                    $applicationForPayment->save();
                    return false;
                }

                if ($result['Status'] == false || $result['Status'] == 'false') {
                    $applicationForPayment->confirmed = ApplicationForPayment::STATUS_AUTO_PAY_ERROR;
                    $applicationForPayment->error = $result['Message']?? 'payout uknown error';
                    $applicationForPayment->save();
                    return false;
                }

                $applicationForPayment->fee  = $result['Data']['Fee'];
                $applicationForPayment->save();

                if ($applicationForPayment->user_id) {
                    UserNotifications::notify($applicationForPayment->user_id, __('notify.title_payout'),
                        '<span class="gold-text">' . $applicationForPayment->money . ' ' . UserWallet::currencyName($applicationForPayment->currency) . '</span> ' . __('notify.payout_success'));
                }

            }
        }
    }

    public function getFormRequisites(string $method, ?PaymentSystems $pay_method): string
    {
        return view('pay.requisites.payout.best-pay24', [
            'requisites' => '',
            'transfer_type' => '',
        ]);
    }
}
