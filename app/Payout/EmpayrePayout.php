<?php

namespace App\Payout;

use App\ApplicationForPayment;
use App\Models\PaymentSystems;
use App\Models\UserNotifications;
use App\MUser;
use App\PaymentsAPI\Empayre\Empayre;
use App\PaymentsAPI\GateExpress\GateExpress;
use App\UserWallet;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class EmpayrePayout extends Payout implements AutoPayoutSystem
{

    private Empayre $api;

    public function __construct()
    {
        $this->api = new Empayre();
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
                $requsites = json_decode($applicationForPayment->requisites, true);
                $destination = $this->api->createDestination($user->getId().':'.$requsites['phone'], UserWallet::currencyName($applicationForPayment->currency), $requsites['phone']);
                if (empty($destination) || !empty($destination['errorType']) || empty($destination['id'])) {
                    $applicationForPayment->confirmed = ApplicationForPayment::STATUS_AUTO_PAY_ERROR;
                    $applicationForPayment->comentar = $destination['description']?? 'Ошибка destination, останов...';
                    $applicationForPayment->save();
                }

                if ($destination['status'] !== 'Authorized') {
                    $applicationForPayment->comentar = $destination['status'];
                    $applicationForPayment->save();
                    return;
                }

                $result = $this->api->payout('o'.$applicationForPayment->id, $destination['id'], $applicationForPayment->money * 100, UserWallet::currencyName($applicationForPayment->currency));

                log::channel('payout')->info('result: ' . var_export($result, true));

                if (empty($result)) {
                    $applicationForPayment->confirmed = ApplicationForPayment::STATUS_AUTO_PAY_ERROR;
                    $applicationForPayment->comentar = 'Ошибка запроса, останов...';
                    $applicationForPayment->save();
                } elseif (empty($result['id'])) {
                    $applicationForPayment->confirmed = ApplicationForPayment::STATUS_AUTO_PAY_ERROR;
                    $applicationForPayment->error = $result['description'] ?? 'unknown error';
                    $applicationForPayment->save();
                } else {
                    $applicationForPayment->outer_id = $result['id'] ?? null;
                    $applicationForPayment->save();
                }
            }
        } elseif ($applicationForPayment->confirmed == ApplicationForPayment::STATUS_AUTO_PAY_PROCESS) {

            if (!empty($applicationForPayment->outer_id)) {

                $result = $this->api->payoutStatus($applicationForPayment->outer_id);

                log::channel('payout')->info(__CLASS__.' info result: ' . var_export($result, true));

                if (empty($result)) {
                    $applicationForPayment->confirmed = ApplicationForPayment::STATUS_AUTO_PAY_ERROR;
                    $applicationForPayment->comentar = 'Ошибка запроса, останов...';
                    $applicationForPayment->save();
                } elseif (!empty($result['errorType'])) {
                    $applicationForPayment->confirmed = ApplicationForPayment::STATUS_AUTO_PAY_ERROR;
                    $applicationForPayment->error = $result['description'];
                    $applicationForPayment->save();
                } elseif (in_array($result['status'], ['voided', 'declined', 'abandoned'])) {
                    $applicationForPayment->confirmed = ApplicationForPayment::STATUS_AUTO_PAY_ERROR;
                    $applicationForPayment->error = $result['TransactionState'];
                    $applicationForPayment->save();

                } elseif ($result['status'] === 'success') {

                    $applicationForPayment->confirmed = ApplicationForPayment::STATUS_AUTO_PAY_SUCCESS;
                    $applicationForPayment->comentar = "Завершено успешно";
                    $applicationForPayment->suma = $result['body']['amount'] / 100;
                    $applicationForPayment->save();
                    $fee = $result['fee']['amount'] /100;

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
        return view('pay.requisites.payout.phone', ['method' => $method]);
    }
}
