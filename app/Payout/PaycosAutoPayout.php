<?php

namespace App\Payout;

use App\ApplicationForPayment;
use App\Models\PaymentSystems;
use App\Models\UserNotifications;
use App\PaymentsAPI\Paycos\Paycos;
use App\UserWallet;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PaycosAutoPayout extends Payout implements AutoPayoutSystem
{
    private $api;

    public function __construct()
    {
//        $this->api = new Paycos();
    }

    public function checkParams(array $params): bool
    {
        list($pay_system_id, $pay_method) = explode('/', $params['payment-method']);

        if ($pay_method === 'Paycos.qiwi' && empty($params['qiwi_phone'])) {
            $this->_error = __('wallet.not_enough_data');
            return false;
        }
        return true;
    }

    public function processAutoPayment(ApplicationForPayment $applicationForPayment)
    {
        $this->api = new Paycos(UserWallet::currencyName($applicationForPayment->currency), $applicationForPayment->system_pay);
        if ($applicationForPayment->confirmed == ApplicationForPayment::STATUS_AUTO_PAY_PENDING) {
            $payout_method = 'q_payout';

            $applicationForPayment->confirmed = ApplicationForPayment::STATUS_AUTO_PAY_PROCESS;
            $applicationForPayment->comentar = 'обрабатывается автовыплата';

            if ($applicationForPayment->save()) {
                $requisites = json_decode($applicationForPayment->requisites, true);

                if ($applicationForPayment->currency != UserWallet::CURRENCY_INR && $applicationForPayment->system_pay !== 'Paycos.inr_95') {
                    $payout_method = 'qiwi';
                    if ($applicationForPayment->system_pay !== 'Paycos.qiwi') {
                        $payout_method = 'ob_card';
                    }
                    if ($applicationForPayment->currency == UserWallet::CURRENCY_EUR) $payout_method = "card";

                    $result = $this->api->payOut(
                        orderId: $applicationForPayment->id,
                        cents: (int)$applicationForPayment->money * 100,
                        requisites: $requisites,
                        pay_method: $applicationForPayment->system_pay,
                        currency: UserWallet::currencyName($applicationForPayment->currency)
                    );

                } else {
                    $result = $this->api->payOutInr(
                        orderId: $applicationForPayment->id,
                        cents: (int)$applicationForPayment->money * 100,
                        requisites: $requisites,
                        pay_method: $applicationForPayment->system_pay,
                        currency: UserWallet::currencyName($applicationForPayment->currency)
                    );
                }

                log::channel('payout')->info('result: ' . var_export($result, true));
                log::channel('payout')->info('method: ' . var_export($applicationForPayment->system_pay, true));

                if (($result['success'] ?? false) !== true) {
                    $applicationForPayment->confirmed = ApplicationForPayment::STATUS_AUTO_PAY_ERROR;
                    $applicationForPayment->comentar = 'Ошибка запроса, останов...';
                    $applicationForPayment->save();
//                } elseif ( empty($result['host_to_host_url']['q_'.$pay_method])) {
                } elseif (empty($result['host_to_host_url'][$payout_method])) {
                    $applicationForPayment->confirmed = ApplicationForPayment::STATUS_AUTO_PAY_ERROR;
                    $applicationForPayment->comentar = 'Ошибка, адрес метода не установлен';
                    $applicationForPayment->save();
                } else {
                    $resp = $this->api->h2hConfirm($result['host_to_host_url'][$payout_method]);
                    log::channel('payout')->info('confirm h2h resp: ' . var_export($resp, true));

                    if (($resp['success'] ?? false) !== true) {
                        $applicationForPayment->confirmed = ApplicationForPayment::STATUS_AUTO_PAY_ERROR;
                        $applicationForPayment->comentar = 'Ошибка подтверждения, останов...';
                        $applicationForPayment->save();
                    } else {
                        $applicationForPayment->outer_id = $resp['token'];
                        $applicationForPayment->save();
                    }
                }
            }
        } elseif ($applicationForPayment->confirmed == ApplicationForPayment::STATUS_AUTO_PAY_PROCESS) {

            if (!empty($applicationForPayment->outer_id)) {
                $result = $this->api->getPayment($applicationForPayment->outer_id);

                log::channel('payout')->info('paycos payout check result: ' . var_export($result, true));

                if (($result['success'] ?? false) !== true) {
                    $applicationForPayment->confirmed = ApplicationForPayment::STATUS_AUTO_PAY_ERROR;
                    $applicationForPayment->error = "get status error";
                    $applicationForPayment->save();
                } elseif ($result['payment']['status'] === 'declined') {
                    $applicationForPayment->confirmed = ApplicationForPayment::STATUS_AUTO_PAY_ERROR;
                    $applicationForPayment->error = "declined";
                    $applicationForPayment->comentar = "declined";
                    $applicationForPayment->save();
                } elseif ($result['payment']['status'] === 'approved') {
                    $applicationForPayment->confirmed = ApplicationForPayment::STATUS_AUTO_PAY_SUCCESS;
                    $applicationForPayment->comentar = "Завершено успешно";
                    $applicationForPayment->suma = $result['payment']['amount'] / 100;
                    $applicationForPayment->save();
                    $fee = 0;

                    if ($applicationForPayment->currency == UserWallet::CURRENCY_RUB || $applicationForPayment->currency == UserWallet::CURRENCY_UAH) {
                        $fee = $applicationForPayment->suma * 0.03;
                        if ($applicationForPayment->system_pay === 'Paycos.ob_card' || $applicationForPayment->system_pay === 'ob_card') {
                            $fee = $applicationForPayment->suma * 0.035;
                        }
                    }
                    // Записуємо транзакцію
                    DB::table('payout_transactions')
                        ->insert([
                            'user_id' => $applicationForPayment->user_id,
                            'hall_id' => $applicationForPayment->hall_id,
                            'date' => date('Y-m-d H:i:s'),
                            'currency' => UserWallet::currencyName($applicationForPayment->currency),
                            'operation' => 'Автовыплата Paycos',
                            'sum' => $applicationForPayment->suma,
                            'fee' => round($fee, 2),
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
        $requisites = json_decode($cached->requisites ?? '{}', true) ?? [];

        $phone = preg_replace('#[^+\d]#', '', Auth::user()->phone ?? '');
        if (preg_match('#^(\+)?(91)?(\d{10})$#', $phone, $match)) {
            $phone = $match[3];
        }

        if (empty($requisites['phone'])) $requisites['phone'] = $phone;
        if (empty($requisites['name'])) $requisites['name'] = (Auth::user()->name ?? '') . ' ' . (Auth::user()->surname ?? '');

        if ($method === 'Paycos.qiwi') {
            return view('pay.requisites.payout.paycos-qiwi', [
                'requisites' => $requisites,
                'transfer_type' => $method,
                'method' => $method,
            ])->render();
        }

        if ($method === 'Paycos.inr_95') {
            return view('pay.requisites.payout.paycos-inr-95', [
                'requisites' => $requisites,
                'transfer_type' => $method,
                'method' => $method,
            ])->render();
        }

        if ($method === 'Paycos.payout_card' || $method === 'Paycos.payout_card-uah') {
            return view('pay.requisites.payout.paycos-card', [
                'requisites' => $requisites,
                'transfer_type' => $method,
                'method' => $method,
            ])->render();
        }

        if ($method === 'Paycos.visa-eur' || $method === 'Paycos.mc-eur') {
            return view('pay.requisites.payout.paycos-visamc', [
                'requisites' => $requisites,
                'transfer_type' => $method,
                'method' => $method,
            ])->render();
        }

        return view('pay.requisites.payout.Paycos', [
            'requisites' => $requisites,
            'transfer_type' => $method,
            'method' => $method,
        ])->render();
    }
}
