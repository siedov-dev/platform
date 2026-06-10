<?php

namespace App\Payout;

use App\ApplicationForPayment;
use App\Models\PaymentSystems;
use App\Models\UserNotifications;
use App\MUser;
use App\PaymentsAPI\SredaPay\SredaPay;
use App\UserWallet;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SredaPayPayout extends Payout implements AutoPayoutSystem
{
    private SredaPay $api;

    public function __construct()
    {
        $this->api = new SredaPay(true);
    }

    public function checkParams(array $params): bool
    {
        return true;
    }

    public function processAutoPayment(ApplicationForPayment $applicationForPayment)
    {
        if ($applicationForPayment->confirmed == ApplicationForPayment::STATUS_AUTO_PAY_PENDING) {

            $applicationForPayment->confirmed = ApplicationForPayment::STATUS_AUTO_PAY_PROCESS;
            $applicationForPayment->comentar = 'обрабатывается автовыплата SredaPay';

            if ($applicationForPayment->save()) {
                /** @var MUser $user */
                $user = MUser::findOrFail($applicationForPayment->user_id);

                $method = explode('.', $applicationForPayment->system_pay)[1];
                $reqisites = json_decode($applicationForPayment->requisites, true);

                $this->api = new SredaPay(true, $method);
                if ($applicationForPayment->getCurrency() === UserWallet::CURRENCY_EUR) {

                    [$direction, $service] = explode($method, '-');

                    $result = $this->api->payoutEur(
                        tr_id: $applicationForPayment->id,
                        dt: $applicationForPayment->date,
                        amount: $applicationForPayment->suma,
                        credentials: $reqisites,
                        currency: 'EUR',
                        method: $direction,
                        service: $service,
                    );
                } else {
                    $result = $this->api->payout($applicationForPayment->id, $method, $applicationForPayment->date, $applicationForPayment->suma,
                        $applicationForPayment->card_number, $method, $reqisites['payer_fio']?? '');
                }


                log::channel('payout')->info('result: ' . var_export($result, true));

                if (empty($result)) {
                    $applicationForPayment->confirmed = ApplicationForPayment::STATUS_AUTO_PAY_ERROR;
                    $applicationForPayment->comentar = 'Ошибка запроса, останов...';
                    $applicationForPayment->save();
                } elseif ($result['result'] !== 'OK') {
                    $applicationForPayment->confirmed = ApplicationForPayment::STATUS_AUTO_PAY_ERROR;
                    $applicationForPayment->error = $result['result'] ?? 'unknown error';
                    $applicationForPayment->save();
                } else {
                    $applicationForPayment->piastrix_id = $result['order_id'] ?? '';
                    $applicationForPayment->save();
                }
            }
        } elseif ($applicationForPayment->confirmed == ApplicationForPayment::STATUS_AUTO_PAY_PROCESS) {

                $method = explode('.', $applicationForPayment->system_pay)[1];
                $this->api = new SredaPay(true, $method);

            if ($applicationForPayment->getCurrency() === UserWallet::CURRENCY_EUR) {
                [$direction, $service] = explode($method, '-');
                $result = $this->api->payoutInfoEur(
                    id: $applicationForPayment->id,
                    dt: $applicationForPayment->date,
                    amount: $applicationForPayment->suma,
                    destination: $direction,
                    service: $service
                );

            } else {
                $result = $this->api->payoutInfo($applicationForPayment->id, $method, $applicationForPayment->date,
                    $applicationForPayment->suma, $method);
            }


                log::channel('payout')->info('SredaPay info result: ' . var_export($result, true));

                if (empty($result)) {
                    $applicationForPayment->confirmed = ApplicationForPayment::STATUS_AUTO_PAY_ERROR;
                    $applicationForPayment->comentar = 'Ошибка запроса, останов...';
                    $applicationForPayment->save();
                } elseif ($result['result'] === 'NOT_PAID' || $result['result'] === 'NOT_FOUND') {
                    $applicationForPayment->confirmed = ApplicationForPayment::STATUS_AUTO_PAY_ERROR;
                    $applicationForPayment->error = $result['result']?? 'unknown error';
                    $applicationForPayment->save();
                } elseif ($result['result'] === 'PAID') {

                    $applicationForPayment->confirmed = ApplicationForPayment::STATUS_AUTO_PAY_SUCCESS;
                    $applicationForPayment->comentar = "Завершено успешно";
                    $applicationForPayment->save();
                    $fee = $applicationForPayment->suma * 0.015;

                    DB::table('payout_transactions')
                        ->insert([
                            'user_id' => $applicationForPayment->user_id,
                            'hall_id' => $applicationForPayment->hall_id,
                            'date' => date('Y-m-d H:i:s'),
                            'currency' => UserWallet::currencyName($applicationForPayment->currency),
                            'operation' => 'Автовыплата SredaPay',
                            'sum' => $applicationForPayment->suma,
                            'fee' => $fee,
                            'total_sum' => $applicationForPayment->suma + $fee,
                            'status_id' => '45',
                        ]);

                    if ($applicationForPayment->user_id) {
                        UserNotifications::notify($applicationForPayment->user_id, __('notify.title_payout'),
                            '<span class="gold-text">' . $applicationForPayment->money . ' ' . UserWallet::currencyName($applicationForPayment->currency) . '</span> ' . __('notify.payout_success'));
                    }

                }
            } else {
                $applicationForPayment->confirmed = ApplicationForPayment::STATUS_AUTO_PAY_ERROR;
                $applicationForPayment->error = 'outer id (piastrix_id) not set';
                $applicationForPayment->save();
            }
    }

    public function getFormRequisites(string $method, ?PaymentSystems $pay_method): string
    {
        $cached = ApplicationForPayment::where('system_pay', $method)
            ->where('user_id', Auth::id())->get()->last();
        $requisites = json_decode($cached->requisites ?? '{}', true) ?? [];

        if ($method === 'SredaPay.3-3000' || $method === 'SredaPay.3-3001') {
            return view('pay.requisites.payout.sreda-eur', ['requisites' => $requisites, 'method' => $method,])->render();
        }
        if ($method !== 'SredaPay.7') {
            return view('pay.requisites.payout.card-fio', ['requisites' => $requisites, 'method' => $method,])->render();
        }
        return view('pay.requisites.payout.phone', ['requisites' => $requisites, 'method' => $method,])->render();
    }

    public function getRequestRequisites(string $method, array $data): array
    {
        if (isset($data['phone'])) {
            //clear all non-numeric characters
            $data['phone'] = preg_replace('#[^\d]#', '', $data['phone']);
        }
        return $data;
    }
}
