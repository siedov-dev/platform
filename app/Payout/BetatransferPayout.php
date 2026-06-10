<?php

namespace App\Payout;

use App\ApplicationForPayment;
use App\Models\UserNotifications;
use App\PaymentsAPI\Betatransfer\Betatransfer;
use App\PaymentsAPI\SkyPay;
use App\UserWallet;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class BetatransferPayout extends Payout implements AutoPayoutSystem
{
    private Betatransfer $api;

    public function __construct()
    {
        $this->api = new Betatransfer();
    }

    public function checkParams(array $params): bool
    {
        return true;
    }

    public function processAutoPayment(ApplicationForPayment $applicationForPayment)
    {
        if ($applicationForPayment->confirmed == ApplicationForPayment::STATUS_AUTO_PAY_PENDING) {

            $applicationForPayment->confirmed = ApplicationForPayment::STATUS_AUTO_PAY_PROCESS;
            $applicationForPayment->comentar = 'обрабатывается автовыплата Betatransfer';

            if ($applicationForPayment->save()) {

                $method = explode('.', $applicationForPayment->system_pay)[1];

                $result = $this->api->withdraw($applicationForPayment->money, $applicationForPayment->currency,
                    $applicationForPayment->id, $method, $applicationForPayment->card_number);

                log::channel('payout')->info('result: ' . var_export($result, true));

                if (empty($result)) {
                    $applicationForPayment->confirmed = ApplicationForPayment::STATUS_AUTO_PAY_ERROR;
                    $applicationForPayment->comentar = 'Ошибка запроса, останов...';
                    $applicationForPayment->save();
                } elseif ($result['status'] !== 'success') {
                    $applicationForPayment->confirmed = ApplicationForPayment::STATUS_AUTO_PAY_ERROR;
                    $applicationForPayment->error = $result['errors'] ?? 'error';
                    $applicationForPayment->save();
                } else {
                    $applicationForPayment->piastrix_id = $result['id'] ?? null;
                    $applicationForPayment->save();
                }
            }
        } elseif ($applicationForPayment->confirmed == ApplicationForPayment::STATUS_AUTO_PAY_PROCESS) {

            if ($applicationForPayment->piastrix_id > 0) {

                $result = $this->api->info($applicationForPayment->piastrix_id);

                log::channel('payout')->info('Betatransfer info result: ' . var_export($result, true));

                if (empty($result)) {
                    $applicationForPayment->confirmed = ApplicationForPayment::STATUS_AUTO_PAY_ERROR;
                    $applicationForPayment->comentar = 'Ошибка запроса, останов...';
                    $applicationForPayment->save();
                } elseif (!in_array($result['status'], ['success', 'processing', 'pending', 'new', 'partial_payment'])) {
                    $applicationForPayment->confirmed = ApplicationForPayment::STATUS_AUTO_PAY_ERROR;
                    $applicationForPayment->error = $result['status'] ?? 'payout fail';
                    $applicationForPayment->save();
                } elseif ($result['status'] === 'success' || $result['status'] === 'partial_payment') {

                    $applicationForPayment->confirmed = ApplicationForPayment::STATUS_AUTO_PAY_SUCCESS;
                    $applicationForPayment->comentar = "Завершено успешно";
                    $applicationForPayment->suma = $result['amount'];
                    $applicationForPayment->save();
//                    $fee = $result['paymentCommission']?? $result['commission']?? $result['amount'] * 0.035;
                    $fee = $result['amount'] * 0.035;

                    DB::table('payout_transactions')
                        ->insert([
                            'user_id' => $applicationForPayment->user_id,
                            'hall_id' => $applicationForPayment->hall_id,
                            'date' => date('Y-m-d H:i:s'),
                            'currency' => UserWallet::currencyName($applicationForPayment->currency),
                            'operation' => 'Автовыплата Betatransfer',
                            'sum' => $applicationForPayment->money,
                            'fee' => $fee,
                            'total_sum' => $applicationForPayment->money + $fee,
                            'status_id' => '45',
                        ]);

                    if ($applicationForPayment->user_id) {
                        UserNotifications::notify($applicationForPayment->user_id, __('notify.title_payout'),
                            '<span class="gold-text">' . $applicationForPayment->money . ' ' . UserWallet::currencyName($applicationForPayment->currency) . '</span> ' . __('notify.payout_success'));
                    }

                } else {
                    $applicationForPayment->comentar = $result['status']?? 'unknown status';
                    $applicationForPayment->save();
                }
            } else {
                $applicationForPayment->confirmed = ApplicationForPayment::STATUS_AUTO_PAY_ERROR;
                $applicationForPayment->error = 'id (piastrix_id) not set';
                $applicationForPayment->save();
            }
        }
    }
}
