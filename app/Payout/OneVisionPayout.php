<?php


namespace App\Payout;


use App\ApplicationForPayment;
use App\Models\PaymentMethodData;
use App\Models\PaymentSystems;
use App\Models\UserNotifications;
use App\MUser;
use App\PaymentsAPI\OneVision\OneVision;
use App\Wallet;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class OneVisionPayout extends Payout implements AutoPayoutSystem
{

    public function __construct()
    {
    }

    public function checkParams(array $params): bool
    {
        return true;
    }

    public function processAutoPayment(ApplicationForPayment $applicationForPayment)
    {
        if ($applicationForPayment->confirmed == ApplicationForPayment::STATUS_AUTO_PAY_PENDING) {

            $applicationForPayment->confirmed = ApplicationForPayment::STATUS_AUTO_PAY_PROCESS;
            $applicationForPayment->comentar = 'init';

            $pay_method = PaymentSystems::where('method_alias', $applicationForPayment->system_pay)->first();
            $method_data = PaymentMethodData::getMethodData($pay_method->getId());
            $api = new OneVision($method_data['API'], $method_data['SECRET'], $method_data['SID'], $method_data['MID']);

            $pmethod = explode('.', $applicationForPayment->system_pay)[1];
            $pay_method = explode('_', $pmethod)[1];

            $user = MUser::find($applicationForPayment->getUserId());

            if ($applicationForPayment->save()) {
                $requisites = json_decode($applicationForPayment->getRequisites(), true);

                $response = $api->payout(
                    user: $user,
                    amount: $applicationForPayment->money,
                    tr_id: 'o' . $applicationForPayment->getId(),
                    requisites: $requisites,
                    currency: Wallet::currencyName($applicationForPayment->currency),
                    method: $pay_method,
                );

                Log::channel('payout')->info(__CLASS__ . ' payout result: ' . var_export($response, true));


                if (!empty($response) && $response['success']) {
                    $resp_data = json_decode(base64_decode($response['data']), true);
                    $applicationForPayment->comentar = $resp_data['payment_status'];
                    $applicationForPayment->confirmed = ApplicationForPayment::STATUS_AUTO_PAY_PROCESS;
                    $applicationForPayment->outer_id = $resp_data['payment_id'];
                } else {
                    $applicationForPayment->comentar = 'response incorrect';
                    $applicationForPayment->confirmed = ApplicationForPayment::STATUS_AUTO_PAY_ERROR;
                }

                $applicationForPayment->save();

            }

        } elseif ($applicationForPayment->confirmed == ApplicationForPayment::STATUS_AUTO_PAY_PROCESS) {

            if (empty($applicationForPayment->outer_id)) {
                $applicationForPayment->confirmed = ApplicationForPayment::STATUS_AUTO_PAY_ERROR;
                $applicationForPayment->comentar = 'outer_id is empty';
                $applicationForPayment->save();
                return;
            }

            $pay_method = PaymentSystems::where('method_alias', $applicationForPayment->system_pay)->first();
            $method_data = PaymentMethodData::getMethodData($pay_method->getId());
            $api = new OneVision($method_data['API'], $method_data['SECRET'], $method_data['SID'], $method_data['MID']);

            $result = $api->paymentStatus('o'.$applicationForPayment->getId());

            log::channel('payout')->info(__CLASS__ . ' info result: ' . var_export($result, true));
            if (empty($result) || !$result['success']) {
                $applicationForPayment->confirmed = ApplicationForPayment::STATUS_AUTO_PAY_ERROR;
                $applicationForPayment->comentar = $result['error_msg'] ?? 'Ошибка запроса, останов...';
                $applicationForPayment->save();
                return;
            }

            $result = json_decode(base64_decode($result['data']), true);

            if (in_array($result['payment_status'], ["refunded", "canceled", "error", "cancel"])) {
                $applicationForPayment->confirmed = ApplicationForPayment::STATUS_AUTO_PAY_ERROR;
                $applicationForPayment->comentar = $result['payment_status'];
                $applicationForPayment->save();
            } elseif ($result['payment_status'] === "refill") {

                $applicationForPayment->confirmed = ApplicationForPayment::STATUS_AUTO_PAY_SUCCESS;
                $applicationForPayment->comentar = "Завершено успешно";
                $applicationForPayment->suma = $result['amount'];
                $applicationForPayment->save();
                $fee = 0;

                if ($applicationForPayment->system_pay === 'UZS_ecom_p2p') {
                    $fee = $applicationForPayment->suma * 0.03;
                }
                if ($applicationForPayment->system_pay === 'KZT_ecom') {
                    $fee = $applicationForPayment->suma * 0.016;
                    if ($fee < 300) $fee = 300;
                }
                if ($applicationForPayment->system_pay === 'KZT_mc') {
                    $fee = $applicationForPayment->suma * 0.02;
                }


                DB::table('payout_transactions')
                    ->insert([
                        'user_id' => $applicationForPayment->user_id,
                        'hall_id' => $applicationForPayment->hall_id,
                        'date' => date('Y-m-d H:i:s'),
                        'currency' => Wallet::currencyName($applicationForPayment->currency),
                        'operation' => 'Автовыплата 1Plat',
                        'sum' => $applicationForPayment->money,
                        'fee' => $fee,
                        'total_sum' => $applicationForPayment->money + $fee,
                        'status_id' => '45',
                    ]);

                if ($applicationForPayment->user_id) {
                    UserNotifications::notify($applicationForPayment->user_id, __('notify.title_payout'),
                        '<span class="gold-text">' . $applicationForPayment->money . ' ' . Wallet::currencyName($applicationForPayment->currency) . '</span> ' . __('notify.payout_success'));
                }

            }

        }
    }

    public function getFormRequisites(string $method, ?PaymentSystems $pay_method): string
    {
        $smethod = explode('.',$method)[1];
        $pmethod = explode('_', $smethod)[1];

        if ($pmethod === 'ecom') {
            return view('pay.requisites.payout.onevision-ecom', [
                'method' => $method,
            ]);
        }

        if ($pmethod === 'mc') {
            return view('pay.requisites.payout.onevision-mc', [
                'method' => $method,
            ]);
        }

        return parent::getFormRequisites($method, $pay_method);
    }

}
