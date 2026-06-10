<?php

namespace App\Payout;

use App\ApplicationForPayment;
use App\Models\PaymentMethodData;
use App\Models\PaymentSystems;
use App\Models\UserNotifications;
use App\MUser;
use App\PaymentsAPI\SequoiaPay\SequoiaPayApi;
use App\PaymentsAPI\ShadowPays\ShadowPays;
use App\UserWallet;
use App\Wallet;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SequoiaPayPayout extends Payout implements AutoPayoutSystem
{

    private SequoiaPayApi $api;

    public function __construct()
    {
        $this->api = new SequoiaPayApi();
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

                $method = explode('.', $applicationForPayment->system_pay)[1];
                $p_method = PaymentSystems::where('method_alias', $applicationForPayment->system_pay)
                    ->where('type', PaymentSystems::TYPE_PAYOUT)
                    ->where('currency_id', $applicationForPayment->currency)
                    ->first();
                $keys = PaymentMethodData::getMethodData($p_method->id);
                $this->api = new SequoiaPayApi($keys['Secret'], $keys['Webhook'] ?? '');

                if ($applicationForPayment->system_pay === 'SequoiaPay.RUB:sbp') {
                    $requisites = json_decode($applicationForPayment->requisites, true);
                    $result = $this->api->payoutSbp(
                        tr: $applicationForPayment,
                        card_number: $applicationForPayment->card_number,
                        data: $requisites,
                    );
                } else {
                    $result = $this->api->payout(
                        tr: $applicationForPayment,
                        card_number: $applicationForPayment->card_number,
                    );
                }

                log::channel('payout')->info('result: ' . var_export($result, true));

                if (empty($result['status']) || $result['status'] !== 'ok' ) {
                    $applicationForPayment->confirmed = ApplicationForPayment::STATUS_AUTO_PAY_ERROR;
                    $applicationForPayment->comentar = $result['message']?? 'Ошибка запроса, останов...';
                    $applicationForPayment->save();
                } elseif (empty($result['data']['internal_id'])) {
                    $applicationForPayment->confirmed = ApplicationForPayment::STATUS_AUTO_PAY_ERROR;
                    $applicationForPayment->error = $result['message'] ?? 'unknown error';
                    $applicationForPayment->save();
                } else {
                    $applicationForPayment->outer_id = ['data']['internal_id'] ?? null;
                    $applicationForPayment->save();
                }
            }
        } elseif ($applicationForPayment->confirmed == ApplicationForPayment::STATUS_AUTO_PAY_PROCESS) {

            if (!empty($applicationForPayment->outer_id)) {

                $p_method = PaymentSystems::where('method_alias', $applicationForPayment->system_pay)
                    ->where('type', PaymentSystems::TYPE_PAYOUT)
                    ->where('currency_id', $applicationForPayment->currency)
                    ->first();
                $keys = PaymentMethodData::getMethodData($p_method->id);
                $this->api = new SequoiaPayApi($keys['Secret'], $keys['Webhook'] ?? '');

                $result = $this->api->payoutStatus('o'.$applicationForPayment->id);

                log::channel('payout')->info(__CLASS__.' info result: ' . var_export($result, true));

                if (empty($result['status']) || $result['status'] !== 'ok' ) {
                    $applicationForPayment->confirmed = ApplicationForPayment::STATUS_AUTO_PAY_ERROR;
                    $applicationForPayment->comentar = $result['message']?? 'Ошибка запроса, останов...';
                    $applicationForPayment->save();
                } elseif (in_array($result['data']['status'], ['fail'])) {
                    $applicationForPayment->confirmed = ApplicationForPayment::STATUS_AUTO_PAY_ERROR;
                    $applicationForPayment->error = $result['data']['status'];
                    $applicationForPayment->save();

                } elseif ($result['data']['status'] === 'success') {

                    $applicationForPayment->confirmed = ApplicationForPayment::STATUS_AUTO_PAY_SUCCESS;
                    $applicationForPayment->comentar = "Завершено успешно";
                    $applicationForPayment->suma = $result['data']['amount'];
                    $applicationForPayment->save();
                    $fee = $result['data']['commission_amount'];

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
        if ($method === 'SequoiaPay.RUB:sbp') {
            $api = new SequoiaPayApi();
            $banks = $api->sbpBanks();
            return view('pay.requisites.payout.sequoia-sbp', [
                'method' => $method,
                'banks' => $banks,
            ]);
        }
        return parent::getFormRequisites($method, $pay_method);
    }
}
