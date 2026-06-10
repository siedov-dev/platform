<?php

namespace App\Payout;

use App\ApplicationForPayment;
use App\Models\UserNotifications;
use App\MUser;
use App\PaymentsAPI\CooCooPay\CooCooPay;
use App\UserWallet;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CooCooPayout extends Payout implements AutoPayoutSystem
{

    private CooCooPay $api;

    public function __construct()
    {
        $this->api = new CooCooPay();
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
//                $user = MUser::findOrFail($applicationForPayment->user_id);

//                $method = explode('.', $applicationForPayment->system_pay)[1];
//                $requsites = json_decode($applicationForPayment->requisites, true);

                $result = $this->api->payout(
                    tr_id:  'o'.$applicationForPayment->id,
                    amount:  $applicationForPayment->money,
                    currency:  UserWallet::currencyName($applicationForPayment->currency),
                    card_number: $applicationForPayment->card_number,
                    user_id: $applicationForPayment->user_id,
                );

                log::channel('payout')->info('result: ' . var_export($result, true));

                $status = $this->api->status('o'.$applicationForPayment->id);

                if (empty($status)) {
                    $applicationForPayment->confirmed = ApplicationForPayment::STATUS_AUTO_PAY_ERROR;
                    $applicationForPayment->comentar = 'Ошибка запроса, останов...';
                    $applicationForPayment->save();
                } elseif (empty($status['data'])) {
                    $applicationForPayment->confirmed = ApplicationForPayment::STATUS_AUTO_PAY_ERROR;
                    $applicationForPayment->error = $status['error']['code'] ?? 'unknown error';
                    $applicationForPayment->save();
                } else {
                    $applicationForPayment->outer_id = $status['data']['id'] ?? null;
                    $applicationForPayment->save();
                }
            }
        } elseif ($applicationForPayment->confirmed == ApplicationForPayment::STATUS_AUTO_PAY_PROCESS) {

            if ($applicationForPayment->outer_id > 0) {

                $result = $this->api->status('o'.$applicationForPayment->getId());

                log::channel('payout')->info(__CLASS__.' info result: ' . var_export($result, true));

                if (empty($result)) {
                    $applicationForPayment->confirmed = ApplicationForPayment::STATUS_AUTO_PAY_ERROR;
                    $applicationForPayment->comentar = 'Ошибка запроса, останов...';
                    $applicationForPayment->save();
                } elseif (!empty($result['error'])) {
                    $applicationForPayment->confirmed = ApplicationForPayment::STATUS_AUTO_PAY_ERROR;
                    $applicationForPayment->error = $result['error']['code']?? 'result error';
                    $applicationForPayment->save();
                } elseif (in_array($result['data']['status'], ['canceled', 'rejected'])) {
                    $applicationForPayment->confirmed = ApplicationForPayment::STATUS_AUTO_PAY_ERROR;
                    $applicationForPayment->error = $result['data']['status'];
                    $applicationForPayment->save();

                } elseif ($result['data']['status'] === 'completed') {

                    $applicationForPayment->confirmed = ApplicationForPayment::STATUS_AUTO_PAY_SUCCESS;
                    $applicationForPayment->comentar = "Завершено успешно";
                    $applicationForPayment->suma = $result['data']['transactions'][0]['paidOut']['amount'];
                    $applicationForPayment->save();
                    $fee = $result['data']['transactions'][0]['merchantWallet']['amount'] - $applicationForPayment->suma;

                    DB::table('payout_transactions')
                        ->insert([
                            'user_id' => $applicationForPayment->user_id,
                            'hall_id' => $applicationForPayment->hall_id,
                            'date' => date('Y-m-d H:i:s'),
                            'currency' => UserWallet::currencyName($applicationForPayment->currency),
                            'operation' => 'Автовыплата CrossPay',
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
}
