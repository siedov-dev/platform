<?php

namespace App\Payout;

use App\ApplicationForPayment;
use App\Models\PaymentSystems;
use App\Models\UserNotifications;
use App\MUser;
use App\PaymentsAPI\Payadmit\Payadmit;
use App\UserWallet;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PayadmitPayout extends Payout implements AutoPayoutSystem
{
    private Payadmit $api;

    public function __construct()
    {
        $this->api = new Payadmit();
    }

    public function checkParams(array $params): bool
    {
        return true;
    }

    public function processAutoPayment(ApplicationForPayment $applicationForPayment)
    {
        if ($applicationForPayment->confirmed == ApplicationForPayment::STATUS_AUTO_PAY_PENDING) {

            $applicationForPayment->confirmed = ApplicationForPayment::STATUS_AUTO_PAY_PROCESS;
            $applicationForPayment->comentar = 'обрабатывается автовыплата Payadmit';

            if ($applicationForPayment->save()) {
                /** @var MUser $user */
                $user = MUser::findOrFail($applicationForPayment->user_id);

                /** @var PaymentSystems $pay_method */
                $pay_method = PaymentSystems::where('currency_id', $applicationForPayment->currency)
                    ->where('method_alias', $applicationForPayment->system_pay)
                    ->where('type', PaymentSystems::TYPE_PAYOUT)
                    ->first();

                $this->api = new Payadmit($pay_method?->getId());

                $full_method_name = explode('.', $applicationForPayment->system_pay)[1];
                $method_prefix = explode(':', $full_method_name)[0]?? '';
                $pay_admit_method = explode(':', $full_method_name)[1]?? "BAZPAYUAH";

                $requisites = json_decode($applicationForPayment->getRequisites(), true);

                if (str_starts_with($method_prefix, "mobile")) {
                    $result = $this->api->payoutMobile(
                        tr_id: $applicationForPayment->getId(),
                        amount: $applicationForPayment->money,
                        requisites: $requisites,
                        currency: UserWallet::currencyName($applicationForPayment->currency),
                        method: $pay_admit_method,
                        countryCode: "KZ",
                        user: $user,
                    );

                } else {
                    $result = $this->api->payout(
                        tr_id: $applicationForPayment->getId(),
                        amount: $applicationForPayment->money,
                        requisites: $requisites,
                        currency: UserWallet::currencyName($applicationForPayment->currency),
                        method: $pay_admit_method,
                        countryCode: "KZ",
                    );
                }

                log::channel('payout')->info('result: ' . var_export($result, true));

                if (empty($result)) {
                    $applicationForPayment->confirmed = ApplicationForPayment::STATUS_AUTO_PAY_ERROR;
                    $applicationForPayment->comentar = 'Ошибка запроса, останов...';
                    $applicationForPayment->save();
                } elseif (empty($result['result']['id']) || $result['result']['state'] === 'DECLINED') {
                    $applicationForPayment->confirmed = ApplicationForPayment::STATUS_AUTO_PAY_ERROR;
                    $applicationForPayment->error = $result['message'] ?? 'unknown error';
                    $applicationForPayment->save();
                } else {
                    $applicationForPayment->outer_id = $result['result']['id'];
                    $applicationForPayment->save();
                }
            }
        } elseif ($applicationForPayment->confirmed == ApplicationForPayment::STATUS_AUTO_PAY_PROCESS) {

            if (!empty($applicationForPayment->outer_id)) {
                /** @var PaymentSystems $pay_method */
                $pay_method = PaymentSystems::where('currency_id', $applicationForPayment->currency)
                    ->where('method_alias', $applicationForPayment->system_pay)
                    ->where('type', PaymentSystems::TYPE_PAYOUT)
                    ->first();

                $this->api = new Payadmit($pay_method?->getId());

                $result = $this->api->paymentInfo($applicationForPayment->outer_id);

                log::channel('payout')->info('Payadmit info result: ' . var_export($result, true));

                if (empty($result) || empty($result['result']['state'])) {
                    $applicationForPayment->confirmed = ApplicationForPayment::STATUS_AUTO_PAY_ERROR;
                    $applicationForPayment->comentar = 'Ошибка запроса, останов...';
                    $applicationForPayment->save();
                } elseif ($result['result']['state'] === 'CANCELLED' || $result['result']['state'] === 'DECLINED') {
                    $applicationForPayment->confirmed = ApplicationForPayment::STATUS_AUTO_PAY_ERROR;
                    $applicationForPayment->error = $result['result']['state'];
                    $applicationForPayment->save();
                } elseif ($result['result']['state'] === 'COMPLETED') {

                    $applicationForPayment->confirmed = ApplicationForPayment::STATUS_AUTO_PAY_SUCCESS;
                    $applicationForPayment->comentar = "Завершено успешно";
                    $applicationForPayment->suma = $result['result']['amount'];
                    $applicationForPayment->save();
                    $fee = $result['result']['amount'] * 0.025 + 2;

                    DB::table('payout_transactions')
                        ->insert([
                            'user_id' => $applicationForPayment->user_id,
                            'hall_id' => $applicationForPayment->hall_id,
                            'date' => date('Y-m-d H:i:s'),
                            'currency' => UserWallet::currencyName($applicationForPayment->currency),
                            'operation' => 'Автовыплата Payadmit',
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
                $applicationForPayment->error = 'billID (piastrix_id) not set';
                $applicationForPayment->save();
            }
        }
    }


    public function getRequestRequisites(string $method, array $data): array
    {
        if (isset($data['phone'])) {
            //clear all non-numeric characters
            $data['phone'] = preg_replace('#\D#', '', $data['phone']);
            $data['phone'] = substr($data['phone'], 0, 3).' '.substr($data['phone'], 3);
        }
        return $data;
    }

    public function getFormRequisites(string $method, ?PaymentSystems $pay_method): string
    {
        $cached = ApplicationForPayment::where('system_pay', $method)
            ->where('user_id', Auth::id())->get()->last();
        $requisites = json_decode($cached->requisites??'{}', true)?? [];

        $full_method_name = explode('.', $pay_method->method_alias)[1];
        $method_prefix = explode(':', $full_method_name)[0]?? '';
        if (str_starts_with($method_prefix, "mobile")) {
            return view('pay.requisites.payout.phonemail', ['method' => $method, 'requisites' => $requisites,]);
        }

        return view('pay.requisites.payout.payadmit', ['method' => $method, 'requisites' => $requisites,]);
    }
}
