<?php

namespace App\Payout;

use App\ApplicationForPayment;
use App\Models\PaymentSystems;
use App\Models\UserNotifications;
use App\MUser;
use App\PaymentsAPI\Exotic\ExoticApi;
use App\PaymentsAPI\Pspy\PspyApi;
use App\PaymentsAPI\SarexPays\SarexPays;
use App\PaymentsAPI\ShadowPays\ShadowPays;
use App\PayoutTransaction;
use App\UserWallet;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PspyPayout extends Payout implements AutoPayoutSystem
{

    private PspyApi $api;

    public function __construct()
    {
        $this->api = new PspyApi();
    }

    public function checkParams(array $params): bool
    {
        return true;
    }

    public function processAutoPayment(ApplicationForPayment $applicationForPayment)
    {
        if ($applicationForPayment->confirmed == ApplicationForPayment::STATUS_AUTO_PAY_PENDING) {

            $applicationForPayment->confirmed = ApplicationForPayment::STATUS_AUTO_PAY_PROCESS;
            $applicationForPayment->comentar = 'обрабатывается автовыплата '.__CLASS__;

            if ($applicationForPayment->save()) {
                /** @var MUser $user */
                $user = MUser::findOrFail($applicationForPayment->user_id);

                $sub_method = explode('.', $applicationForPayment->system_pay)[1];
                $requisites = json_decode($applicationForPayment->requisites, true);

                $result = $this->api->payout(
                    tr: $applicationForPayment,
                    method: $sub_method,
                    requisites: $requisites,
                );

                log::channel('payout')->info('result: ' . var_export($result, true));

                if (empty($result) || empty($result['status']) || $result['status'] !== 200) {
                    $applicationForPayment->confirmed = ApplicationForPayment::STATUS_AUTO_PAY_ERROR;
                    $applicationForPayment->comentar = $result['error']?? 'Ошибка запроса, останов...';
                    $applicationForPayment->save();
                } elseif (empty($result['result']['id'])) {
                    $applicationForPayment->confirmed = ApplicationForPayment::STATUS_AUTO_PAY_ERROR;
                    $applicationForPayment->error = $result['error']['message'] ?? 'unknown error';
                    $applicationForPayment->save();
                } else {
                    $applicationForPayment->outer_id = $result['result']['id'];
                    $applicationForPayment->save();
                }
            }
        } elseif ($applicationForPayment->confirmed == ApplicationForPayment::STATUS_AUTO_PAY_PROCESS) {

            if (!empty($applicationForPayment->outer_id)) {

                $result = $this->api->getPayment($applicationForPayment->outer_id);

                log::channel('payout')->info(__CLASS__.' info result: ' . var_export($result, true));

                if (empty($result)) {
                    $applicationForPayment->confirmed = ApplicationForPayment::STATUS_AUTO_PAY_ERROR;
                    $applicationForPayment->comentar = 'Ошибка запроса, останов...';
                    $applicationForPayment->save();
                } elseif (empty($result['result']['id'])) {
                    $applicationForPayment->confirmed = ApplicationForPayment::STATUS_AUTO_PAY_ERROR;
                    $applicationForPayment->error = $result['error']['message'] ?? 'unknown error';
                    $applicationForPayment->save();
                } elseif (in_array($result['result']['state'], ['CANCELLED', 'DECLINED'])) {
                    $applicationForPayment->confirmed = ApplicationForPayment::STATUS_AUTO_PAY_ERROR;
                    $applicationForPayment->error = $result['state'];
                    $applicationForPayment->save();

                } elseif ($result['result']['status'] === 'COMPLETED') {

                    $applicationForPayment->confirmed = ApplicationForPayment::STATUS_AUTO_PAY_SUCCESS;
                    $applicationForPayment->comentar = "Завершено успешно";
                    $applicationForPayment->suma = $result['result']['amount'];
                    $applicationForPayment->save();
                    $fee = $result['commission']?? $result['result']['amount'] * 0.069;

                    DB::table('payout_transactions')
                        ->insert([
                            'user_id' => $applicationForPayment->user_id,
                            'hall_id' => $applicationForPayment->hall_id,
                            'date' => date('Y-m-d H:i:s'),
                            'currency' => UserWallet::currencyName($applicationForPayment->currency),
                            'operation' => 'Автовыплата '.__CLASS__,
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
                $applicationForPayment->error = 'outer_id not set';
                $applicationForPayment->save();
            }
        }
    }

    public function getFormRequisites(string $method, ?PaymentSystems $pay_method): string
    {
        return view('pay.requisites.payout.card-data', ['method' => $method, 'pay_method' => $pay_method]);
    }
}
