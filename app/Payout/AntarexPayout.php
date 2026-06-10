<?php

namespace App\Payout;

use App\ApplicationForPayment;
use App\Models\UserNotifications;
use App\MUser;
use App\PaymentsAPI\Antarex\Antarex;
use App\PayoutTransaction;
use App\UserWallet;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AntarexPayout extends Payout implements AutoPayoutSystem
{
    private Antarex $api;

    public function __construct()
    {
        $this->api = new Antarex();
    }

    public function checkParams(array $params): bool
    {
        return true;
    }

    public function processAutoPayment(ApplicationForPayment $applicationForPayment)
    {
        if ($applicationForPayment->confirmed == ApplicationForPayment::STATUS_AUTO_PAY_PENDING) {

            $applicationForPayment->confirmed = ApplicationForPayment::STATUS_AUTO_PAY_PROCESS;
            $applicationForPayment->comentar = 'обрабатывается автовыплата Antarex';

            if ($applicationForPayment->save()) {
                /** @var MUser $user */
                $user = MUser::findOrFail($applicationForPayment->user_id);

                $method = explode('.', $applicationForPayment->system_pay)[1];

                if (explode('_', $method)[1] === 'p2p') {
                    $result = $this->api->payoutPtp($applicationForPayment->money, $applicationForPayment->card_number, $applicationForPayment);
                } else {
                    $result = $this->api->
                    payout($applicationForPayment->money, $applicationForPayment->currency,
                        explode('_', $method)[1], $applicationForPayment->card_number, $applicationForPayment->id,
                        $user->id, $user->last_ip);
                }

                log::channel('payout')->info('result: ' . var_export($result, true));

                if (empty($result)) {
                    $applicationForPayment->confirmed = ApplicationForPayment::STATUS_AUTO_PAY_ERROR;
                    $applicationForPayment->comentar = 'Ошибка запроса, останов...';
                    $applicationForPayment->save();
                } elseif ($result['success'] !== true) {
                    $applicationForPayment->confirmed = ApplicationForPayment::STATUS_AUTO_PAY_ERROR;
                    $applicationForPayment->error = $result['message'] ?? 'unknown error';
                    $applicationForPayment->save();
                } else {
                    $applicationForPayment->outer_id = $result['uuid'] ?? null;
                    $applicationForPayment->piastrix_id = $result['id'] ?? null;
                    $applicationForPayment->save();
                }
            }
        } elseif ($applicationForPayment->confirmed == ApplicationForPayment::STATUS_AUTO_PAY_PROCESS) {

            if ($applicationForPayment->piastrix_id > 0) {

                $method = explode('.', $applicationForPayment->system_pay)[1];
                if (explode('_', $method)[1] === 'p2p') {
                    $result = $this->api->getOrderDetails($applicationForPayment->outer_id);
                    log::channel('payout')->info('Antarex info ptp result: ' . var_export($result, true));

                } else {
                    $result = $this->api->paymentInfo($applicationForPayment->id, 'withdraw');
                    log::channel('payout')->info('Antarex info result: ' . var_export($result, true));
                }

                if (empty($result)) {
                    $applicationForPayment->confirmed = ApplicationForPayment::STATUS_AUTO_PAY_ERROR;
                    $applicationForPayment->comentar = 'Ошибка запроса, останов...';
                    $applicationForPayment->save();
                } elseif ($result['status'] === 'Failed') {
                    $applicationForPayment->confirmed = ApplicationForPayment::STATUS_AUTO_PAY_ERROR;
                    $applicationForPayment->error = 'payout fail';
                    $applicationForPayment->save();
                } elseif ($result['status'] === 'Succeed') {

                    $applicationForPayment->confirmed = ApplicationForPayment::STATUS_AUTO_PAY_SUCCESS;
                    $applicationForPayment->comentar = "Завершено успешно";
                    $applicationForPayment->suma = $result['sum']?? $result['amount'];
                    $applicationForPayment->save();
                    $fee = $result['commission']?? $result['fee'] ?? 0;

                    DB::table('payout_transactions')
                        ->insert([
                            'user_id' => $applicationForPayment->user_id,
                            'hall_id' => $applicationForPayment->hall_id,
                            'date' => date('Y-m-d H:i:s'),
                            'currency' => UserWallet::currencyName($applicationForPayment->currency),
                            'operation' => 'Автовыплата Antarex',
                            'sum' => $applicationForPayment->money,
                            'fee' => $fee,
                            'total_sum' => $applicationForPayment->money + $fee,
                            'status_id' => PayoutTransaction::PAID_OUT_VISA,
                        ]);

                    if ($applicationForPayment->user_id) {
                        UserNotifications::notify($applicationForPayment->user_id, __('notify.title_payout'),
                            '<span class="gold-text">' . $applicationForPayment->money . ' ' . UserWallet::currencyName($applicationForPayment->currency) . '</span> ' . __('notify.payout_success'));
                    }

                }

            } else {
                $applicationForPayment->confirmed = ApplicationForPayment::STATUS_AUTO_PAY_ERROR;
                $applicationForPayment->error = '(piastrix_id) not set';
                $applicationForPayment->save();
            }
        }
    }

}
