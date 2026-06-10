<?php

namespace App\Payout;

use App\ApplicationForPayment;
use App\Models\PaymentSystems;
use App\Models\UserNotifications;
use App\MUser;
use App\PaymentsAPI\Exotic\ExoticApi;
use App\PaymentsAPI\ShadowPays\ShadowPays;
use App\UserWallet;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ExoticPayout extends Payout implements AutoPayoutSystem
{

    private ExoticApi $api;

    public function __construct()
    {
        $this->api = new ExoticApi();
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
                [$p_method, $p_type] = explode(':', $sub_method);
                $requisites = json_decode($applicationForPayment->requisites, true);

                $result = $this->api->payout(
                    tr: $applicationForPayment,
                    p_method: $p_method,
                    fiat_currency: $applicationForPayment->currency,
                    rerqusites: $requisites,
                );

                log::channel('payout')->info('result: ' . var_export($result, true));

                if (empty($result)) {
                    $applicationForPayment->confirmed = ApplicationForPayment::STATUS_AUTO_PAY_ERROR;
                    $applicationForPayment->comentar = 'Ошибка запроса, останов...';
                    $applicationForPayment->save();
                } elseif (empty($result['internal_transaction_id'])) {
                    $applicationForPayment->confirmed = ApplicationForPayment::STATUS_AUTO_PAY_ERROR;
                    $applicationForPayment->error = $result['message'] ?? 'unknown error';
                    $applicationForPayment->save();
                } else {
                    $applicationForPayment->outer_id = $result['internal_transaction_id'];
                    $applicationForPayment->save();
                }
            }
        } elseif ($applicationForPayment->confirmed == ApplicationForPayment::STATUS_AUTO_PAY_PROCESS) {

            if (!empty($applicationForPayment->outer_id)) {

                $result = $this->api->orderState('o'.$applicationForPayment->id);

                log::channel('payout')->info(__CLASS__.' info result: ' . var_export($result, true));

                if (empty($result)) {
                    $applicationForPayment->confirmed = ApplicationForPayment::STATUS_AUTO_PAY_ERROR;
                    $applicationForPayment->comentar = 'Ошибка запроса, останов...';
                    $applicationForPayment->save();
                } elseif (empty($result['ok'])) {
                    $applicationForPayment->confirmed = ApplicationForPayment::STATUS_AUTO_PAY_ERROR;
                    $applicationForPayment->error = $result['message'] ?? 'unknown error';
                    $applicationForPayment->save();
                } elseif (in_array($result['status'], ['rejected_timeout', 'rejected_merchant', 'rejected_gate'])) {
                    $applicationForPayment->confirmed = ApplicationForPayment::STATUS_AUTO_PAY_ERROR;
                    $applicationForPayment->error = $result['status'];
                    $applicationForPayment->save();

                } elseif ($result['status'] === 'successful') {

                    $applicationForPayment->confirmed = ApplicationForPayment::STATUS_AUTO_PAY_SUCCESS;
                    $applicationForPayment->comentar = "Завершено успешно";
                    $applicationForPayment->suma = $result['fiat_amount'];
                    $applicationForPayment->save();
                    $fee = ($result['merchant_spent_usdt'] - $result['usdt_amount']) * $result['exchange_rate'];

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
                            'status_id' => '45',
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
        if ($method === 'Exotic.sbp:rub') {
            return view('pay.requisites.payout.exotic-sbp', ['method' => $method, 'pay_method' => $pay_method]);
        }
        return parent::getFormRequisites($method, $pay_method);
    }
}
