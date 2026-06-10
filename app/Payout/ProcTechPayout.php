<?php

namespace App\Payout;

use App\ApplicationForPayment;
use App\Models\PaymentSystems;
use App\Models\UserNotifications;
use App\MUser;
use App\PaymentsAPI\Exotic\ExoticApi;
use App\PaymentsAPI\ProcTech\ProcTechApi;
use App\PaymentsAPI\Pspy\PspyApi;
use App\PaymentsAPI\SarexPays\SarexPays;
use App\PaymentsAPI\ShadowPays\ShadowPays;
use App\PayoutTransaction;
use App\UserWallet;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProcTechPayout extends Payout implements AutoPayoutSystem
{

    private ProcTechApi $api;

    public function __construct()
    {
        $this->api = new ProcTechApi();
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

                if (empty($result['success'])) {
                    $applicationForPayment->confirmed = ApplicationForPayment::STATUS_AUTO_PAY_ERROR;
                    $applicationForPayment->comentar = $result['errors'][0]['description']?? 'Ошибка запроса, останов...';
                    $applicationForPayment->save();
                } elseif (empty($result['payout']['token'])) {
                    $applicationForPayment->confirmed = ApplicationForPayment::STATUS_AUTO_PAY_ERROR;
                    $applicationForPayment->error = $result['errors'][0]['description'] ?? 'no token in response';
                    $applicationForPayment->save();
                } else {
                    $applicationForPayment->outer_id = $result['payout']['token'];
                    $applicationForPayment->save();
                }
            }
        } elseif ($applicationForPayment->confirmed == ApplicationForPayment::STATUS_AUTO_PAY_PROCESS) {

            if (!empty($applicationForPayment->outer_id)) {

                $result = $this->api->getPaymentStatus($applicationForPayment->outer_id);

                log::channel('payout')->info(__CLASS__.' info result: ' . var_export($result, true));

                if (empty($result['success'])) {
                    $applicationForPayment->confirmed = ApplicationForPayment::STATUS_AUTO_PAY_ERROR;
                    $applicationForPayment->comentar = $result['errors'][0]['description']?? 'Ошибка запроса, останов...';
                    $applicationForPayment->save();
                } elseif (in_array($result['payment']['status'], ['declined'])) {
                    $applicationForPayment->confirmed = ApplicationForPayment::STATUS_AUTO_PAY_ERROR;
                    $applicationForPayment->error = $result['state'];
                    $applicationForPayment->save();

                } elseif ($result['payment']['status'] === 'approved') {

                    $applicationForPayment->confirmed = ApplicationForPayment::STATUS_AUTO_PAY_SUCCESS;
                    $applicationForPayment->comentar = "Завершено успешно";
                    $applicationForPayment->suma = $result['payment']['amount'];
                    $applicationForPayment->save();
                    $fee = $result['payment']['commission']?? $result['payment']['amount'] * 0.06;

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
        return view('pay.requisites.payout.proctech', ['method' => $method, 'pay_method' => $pay_method]);
    }
}
