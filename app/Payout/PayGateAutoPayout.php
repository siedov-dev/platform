<?php

namespace App\Payout;

use App\ApplicationForPayment;
use App\Models\PaymentSystems;
use App\Models\UserNotifications;
use App\PaymentsAPI\PayGate10\PayGate10;
use App\UserWallet;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PayGateAutoPayout extends Payout implements AutoPayoutSystem
{
    private $api;

    public function __construct()
    {
        $this->api = new PayGate10();
    }

    public function checkParams(array $params): bool
    {
        return true;
    }

    public function processAutoPayment(ApplicationForPayment $applicationForPayment)
    {
        if ($applicationForPayment->confirmed == ApplicationForPayment::STATUS_AUTO_PAY_PENDING) {

            $applicationForPayment->confirmed = ApplicationForPayment::STATUS_AUTO_PAY_PROCESS;
            $applicationForPayment->comentar = 'обрабатывается автовыплата';

            if ($applicationForPayment->save()) {
                $requisites = json_decode($applicationForPayment->requisites, true);

                $pay_method = explode('.', $applicationForPayment->system_pay)[1];

                $result = $this->api->payout($applicationForPayment->id, (int)$applicationForPayment->money, $pay_method,
                    $requisites, UserWallet::currencyName($applicationForPayment->currency));

                log::channel('payout')->info('result: ' . var_export($result, true));
                log::channel('payout')->info('method: ' . var_export($pay_method, true));

                if (empty($result) || $result['status'] === 'DECLINED' ) {
                    $applicationForPayment->confirmed = ApplicationForPayment::STATUS_AUTO_PAY_ERROR;
                    $applicationForPayment->comentar = $result['message']??'Ошибка запроса, останов...';
                } else {
                    $applicationForPayment->outer_id = $result['payoutid'];
                }
                $applicationForPayment->save();
            }
        } elseif ($applicationForPayment->confirmed == ApplicationForPayment::STATUS_AUTO_PAY_PROCESS) {

            if (!empty($applicationForPayment->outer_id)) {
                $result = $this->api->getPayout($applicationForPayment->outer_id);

                log::channel('payout')->info('paycos payout check result: ' . var_export($result, true));

                if (empty($result)) {
                    $applicationForPayment->confirmed = ApplicationForPayment::STATUS_AUTO_PAY_ERROR;
                    $applicationForPayment->error = "get status error";
                    $applicationForPayment->save();
                } elseif (empty($result['ResponseResult']) || mb_strtolower($result['ResponseResult']['Status']) === 'declined') {
                    $applicationForPayment->confirmed = ApplicationForPayment::STATUS_AUTO_PAY_ERROR;
                    $applicationForPayment->error = "declined";
                    $applicationForPayment->comentar = "declined";
                    $applicationForPayment->save();
                } elseif (mb_strtolower($result['ResponseResult']['Status']) === 'approved' && $result['ResponseResult']['depositedamount'] > 0) {
                    $applicationForPayment->confirmed = ApplicationForPayment::STATUS_AUTO_PAY_SUCCESS;
                    $applicationForPayment->comentar = "Завершено успешно";
                    $applicationForPayment->suma = $result['ResponseResult']['depositedamount'];
                    $applicationForPayment->save();
                    $fee = 0;
                    // Записуємо транзакцію
                    DB::table('payout_transactions')
                        ->insert([
                            'user_id' => $applicationForPayment->user_id,
                            'hall_id' => $applicationForPayment->hall_id,
                            'date' => date('Y-m-d H:i:s'),
                            'currency' => UserWallet::currencyName($applicationForPayment->currency),
                            'operation' => 'Автовыплата PayGate',
                            'sum' => $applicationForPayment->suma,
                            'fee' => $fee,
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

    public function getFormRequisites(string $method, ?PaymentSystems $pay_method): string
    {
        $cached = ApplicationForPayment::where('system_pay', $method)
            ->where('user_id', Auth::id())->get()->last();
        $requisites = json_decode($cached->requisites??'{}', true)?? [];
        return view('pay.requisites.payout.PayGate', ['requisites' => $requisites, 'method' => $method,])->render();
    }

    public function getRequestRequisites(string $method, array $data): array
    {
        if (isset($data['_token'])) unset($data['_token']);
        $data['username'] = Auth::user()->login;
        return $data;
    }
}
