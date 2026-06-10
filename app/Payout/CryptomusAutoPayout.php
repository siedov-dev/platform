<?php

namespace App\Payout;

use App\ApplicationForPayment;
use App\Models\UserNotifications;
use App\PaymentsAPI\Cryptomus\Cryptomus;
use App\PaymentsAPI\PayLama\PayLama;
use App\UserWallet;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CryptomusAutoPayout extends Payout implements AutoPayoutSystem
{
    private Cryptomus $api;

    public function __construct()
    {
        $this->api = new Cryptomus();
    }

    public function checkParams(array $params): bool
    {
        return true;
    }

    public function processAutoPayment(ApplicationForPayment $applicationForPayment)
    {
        if ($applicationForPayment->confirmed == ApplicationForPayment::STATUS_AUTO_PAY_PENDING) {

            $applicationForPayment->confirmed = ApplicationForPayment::STATUS_AUTO_PAY_PROCESS;
            $applicationForPayment->comentar = 'обрабатывается автовыплата Cryptomus';

            if ($applicationForPayment->save()) {

                $network = explode('.', $applicationForPayment->system_pay)[1];

                $result = $this->api->payout($applicationForPayment->money, $applicationForPayment->currency,
                    $applicationForPayment->id, $network, $applicationForPayment->card_number);

                log::channel('payout')->info('result: ' . var_export($result, true));

                if (empty($result)) {
                    $applicationForPayment->confirmed = ApplicationForPayment::STATUS_AUTO_PAY_ERROR;
                    $applicationForPayment->comentar = 'Ошибка запроса, останов...';
                    $applicationForPayment->save();
                } elseif ($result['state'] !== 0) {
                    $applicationForPayment->confirmed = ApplicationForPayment::STATUS_AUTO_PAY_ERROR;
                    $applicationForPayment->error = $result['message'] ?? 'unknown error';
                    $applicationForPayment->save();
                } else {
                    $applicationForPayment->piastrix_id = $result['result']['uuid'] ?? null;
                    $applicationForPayment->save();
                }
            }
        } elseif ($applicationForPayment->confirmed == ApplicationForPayment::STATUS_AUTO_PAY_PROCESS) {

            if ($applicationForPayment->piastrix_id > 0) {

                $result = $this->api->payoutInfo($applicationForPayment->id);

                log::channel('payout')->info('Cryptomus info result: ' . var_export($result, true));

                if (empty($result)) {
                    $applicationForPayment->confirmed = ApplicationForPayment::STATUS_AUTO_PAY_ERROR;
                    $applicationForPayment->comentar = 'Ошибка запроса, останов...';
                    $applicationForPayment->save();
                } elseif ($result['state'] !== 0) {
                    $applicationForPayment->confirmed = ApplicationForPayment::STATUS_AUTO_PAY_ERROR;
                    $applicationForPayment->error = 'payout fail';
                    $applicationForPayment->save();
                } elseif ($result['result']['is_final'] === true) {

                    if ($result['result']['status'] !== 'paid') {
                        $applicationForPayment->confirmed = ApplicationForPayment::STATUS_AUTO_PAY_ERROR;
                        $applicationForPayment->error = $result['result']['status'] ?? 'unknown error';
                        $applicationForPayment->save();
                    } else {

                        $applicationForPayment->confirmed = ApplicationForPayment::STATUS_AUTO_PAY_SUCCESS;
                        $applicationForPayment->comentar = "Завершено успешно";
                        $applicationForPayment->suma = $result['result']['amount'];
                        $applicationForPayment->save();
                        $fee = round($applicationForPayment->suma * 0.02, 2);

                        DB::table('payout_transactions')
                            ->insert([
                                'user_id' => $applicationForPayment->user_id,
                                'hall_id' => $applicationForPayment->hall_id,
                                'date' => date('Y-m-d H:i:s'),
                                'currency' => UserWallet::currencyName($applicationForPayment->currency),
                                'operation' => 'Автовыплата Cryptomus',
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

                }
            } else {
                $applicationForPayment->confirmed = ApplicationForPayment::STATUS_AUTO_PAY_ERROR;
                $applicationForPayment->error = 'uuid (piastrix_id) not set';
                $applicationForPayment->save();
            }
        }
    }
}
