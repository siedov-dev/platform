<?php

namespace App\Payout;

use App\ApplicationForPayment;
use App\PaymentsAPI\Alphapo\AlphapoApi;
use App\UserWallet;
use Illuminate\Support\Facades\Log;

class AlphapoPayout extends Payout implements AutoPayoutSystem
{

    private AlphapoApi $api;

    public function __construct()
    {
        $this->api = new AlphapoApi();
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
                $requisites = json_decode($applicationForPayment->requisites, true);
                Log::channel('payout')->info('requisites ' . var_export($requisites, true));

                $pay_method = explode('.', $applicationForPayment->system_pay)[1];

                $result = $this->api->payout($applicationForPayment->money, $applicationForPayment->id, $pay_method,
                    UserWallet::currencyName($applicationForPayment->currency), $applicationForPayment->card_number);

                log::channel('payout')->info('result: ' . var_export($result, true));
                log::channel('payout')->info('method: ' . var_export($pay_method, true));

                if (empty($result['status']) || $result['status'] !== 'processing' ) {
                    $applicationForPayment->confirmed = ApplicationForPayment::STATUS_AUTO_PAY_ERROR;
                    $applicationForPayment->comentar = $result['errors'][0]??'Ошибка запроса, останов...';
                } else {
                    $applicationForPayment->outer_id = $result['id'];
                }
                $applicationForPayment->save();
            }
        } elseif ($applicationForPayment->confirmed == ApplicationForPayment::STATUS_AUTO_PAY_PROCESS) {
            log::channel('payout')->info('alphapo payout check made in callback');
        }
    }
}
