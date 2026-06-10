<?php

namespace App\Payout;

use App\ApplicationForPayment;
use App\Models\UserNotifications;
use App\PaymentsAPI\Paydex\Paydex;
use App\UserWallet;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PaydexPayout extends Payout implements AutoPayoutSystem
{

    private Paydex $api;

    public function __construct()
    {
        $this->api = new Paydex();
    }

    public function checkParams(array $params): bool
    {
        return true;
    }

    public function processAutoPayment(ApplicationForPayment $applicationForPayment)
    {
        if ($applicationForPayment->confirmed == ApplicationForPayment::STATUS_AUTO_PAY_PENDING) {
            $payout_method = 'payout';

            $applicationForPayment->confirmed = ApplicationForPayment::STATUS_AUTO_PAY_PROCESS;
            $applicationForPayment->comentar = __CLASS__.'обрабатывается автовыплата';

            if ($applicationForPayment->save()) {
                $requisites = json_decode($applicationForPayment->requisites, true);

                $result = $this->api->payOut($applicationForPayment->id, (int)$applicationForPayment->money,
                    $applicationForPayment->system_pay, $requisites, UserWallet::currencyName($applicationForPayment->currency));

                log::channel('payout')->info('result: ' . var_export($result, true));
                $pay_method = explode('.', $applicationForPayment->system_pay)[1];
                log::channel('payout')->info('method: ' . var_export($pay_method, true));

                if ($result['data']['attributes']['status'] !== 'created') {
                    $applicationForPayment->confirmed = ApplicationForPayment::STATUS_AUTO_PAY_ERROR;
                    $applicationForPayment->comentar = 'Ошибка запроса, останов...';
                    $applicationForPayment->save();
                } else {
                    $applicationForPayment->outer_id = $result['data']['id'];
                    $applicationForPayment->save();
                    $resp = $this->api->payoutProcess($applicationForPayment->outer_id);
                    log::channel('payout')->info('process resp: ' . var_export($resp, true));

                    if ($result['data']['attributes']['status'] !== 'created' && $result['data']['attributes']['status'] !== 'process_pending') {
                        $applicationForPayment->confirmed = ApplicationForPayment::STATUS_AUTO_PAY_ERROR;
                        $applicationForPayment->comentar = 'Ошибка подтверждения, останов...';
                        $applicationForPayment->save();
                    } else {
                        $applicationForPayment->save();
                    }
                }
            }
        } elseif ($applicationForPayment->confirmed == ApplicationForPayment::STATUS_AUTO_PAY_PROCESS) {

            if (!empty($applicationForPayment->outer_id)) {
                $result = $this->api->payoutStatus($applicationForPayment->outer_id);

                log::channel('payout')->info('paycos payout check result: ' . var_export($result, true));

                if (empty($result['data']['status'])) {
                    $applicationForPayment->confirmed = ApplicationForPayment::STATUS_AUTO_PAY_ERROR;
                    $applicationForPayment->error = "get status error";
                    $applicationForPayment->save();
                } elseif (in_array($result['data']['status'], ['terminated', 'expired', 'canceled', 'process_failed'])) {
                    $applicationForPayment->confirmed = ApplicationForPayment::STATUS_AUTO_PAY_ERROR;
                    $applicationForPayment->error = $result['data']['status'];
                    $applicationForPayment->comentar = "declined";
                    $applicationForPayment->save();
                } elseif (in_array($result['data']['status'], ['processed', 'partially_processed', 'partially_canceled'])) {
                    $applicationForPayment->confirmed = ApplicationForPayment::STATUS_AUTO_PAY_SUCCESS;
                    $applicationForPayment->comentar = "Завершено успешно";
                    $applicationForPayment->suma = $result['data']['processed_amount'];
                    $applicationForPayment->save();
                    $fee = 0;


                    // Записуємо транзакцію
                    DB::table('payout_transactions')
                        ->insert([
                            'user_id' => $applicationForPayment->user_id,
                            'hall_id' => $applicationForPayment->hall_id,
                            'date' => date('Y-m-d H:i:s'),
                            'currency' => UserWallet::currencyName($applicationForPayment->currency),
                            'operation' => 'Автовыплата Paydex',
                            'sum' => $applicationForPayment->suma,
                            'fee' => round($fee,2),
                            'total_sum' => $applicationForPayment->suma,
                            'status_id' => '45',
                        ]);

                    if ($applicationForPayment->user_id) {
                        UserNotifications::notify($applicationForPayment->user_id, __('notify.title_payout'),
                            '<span class="gold-text">' . $applicationForPayment->suma . ' ' . UserWallet::currencyName($applicationForPayment->currency) . '</span> ' . __('notify.payout_success'));
                    }
                }
            } else {
                $applicationForPayment->confirmed = ApplicationForPayment::STATUS_AUTO_PAY_ERROR;
                $applicationForPayment->error = "outer id not set";
                $applicationForPayment->save();
            }
        }
    }
}
