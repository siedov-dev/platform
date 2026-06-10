<?php


namespace App\Payout;


use App\ApplicationForPayment;
use App\Models\PaymentSystems;
use App\Models\UserNotifications;
use App\MUser;
use App\Payin\PayCraftPayin;
use App\PaymentsAPI\Jupiter\Jupiter;
use App\PaymentsAPI\NashObmen\NashObmen;
use App\PaymentsAPI\PayCraft;
use App\PaymentsAPI\Repay\Repay;
use App\PaymentsAPI\XPayPro\XPayPro;
use App\PayoutTransaction;
use App\UserWallet;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class JupiterPayout extends Payout implements AutoPayoutSystem
{
    private Jupiter $api;

    public function __construct()
    {
        $this->api = new Jupiter();
    }

    public function checkParams(array $params): bool
    {
        return true;
    }

    public function processAutoPayment(ApplicationForPayment $applicationForPayment)
    {
        if ($applicationForPayment->confirmed == ApplicationForPayment::STATUS_AUTO_PAY_PENDING) {

            $applicationForPayment->comentar = 'init';

            if ($applicationForPayment->save()) {

                $method = explode('.', $applicationForPayment->system_pay)[1];
                $requisites = json_decode($applicationForPayment->requisites, true);

                $response = $this->api->payoutMobile(
                    tr: $applicationForPayment,
                    amount: $applicationForPayment->money,
                    method: $method,
                    currency: $applicationForPayment->currency,
                    requisites: $requisites,
                );

                Log::channel('payout')->info(JupiterPayout::class . ' payout result: ', (array)$response);

                if (!empty($response) && !empty($response['transaction_id'])) {
                    $applicationForPayment->comentar = 'created';
                    $applicationForPayment->confirmed = ApplicationForPayment::STATUS_AUTO_PAY_PROCESS;
                    $applicationForPayment->outer_id = $response['transaction_id'];
                } else {
                    $applicationForPayment->comentar = $response['message'] ?? 'unknown error';
                    $applicationForPayment->confirmed = ApplicationForPayment::STATUS_AUTO_PAY_ERROR;
                }

                $applicationForPayment->save();

            }

        } elseif ($applicationForPayment->confirmed == ApplicationForPayment::STATUS_AUTO_PAY_PROCESS) {

            if (!empty($applicationForPayment->outer_id)) {
                $result = $this->api->status($applicationForPayment->outer_id);

                log::channel('payout')->info(__CLASS__ . ' info result: ' . var_export($result, true));

                if (empty($result['status'])) {
                    $applicationForPayment->confirmed = ApplicationForPayment::STATUS_AUTO_PAY_ERROR;
                    $applicationForPayment->comentar = $result['message'] ?? 'Ошибка запроса, останов...';
                    $applicationForPayment->save();

                } elseif (!empty($result['status']['error_code'])) {
                    $applicationForPayment->confirmed = ApplicationForPayment::STATUS_AUTO_PAY_ERROR;
                    $applicationForPayment->comentar = $result['status']['error_description'] ?? 'Ошибка запроса, останов...';
                    $applicationForPayment->save();

                } elseif (in_array($result['status']['type'], ['cancelled', 'declined', 'error', 'disputed'])) {
                    $applicationForPayment->confirmed = ApplicationForPayment::STATUS_AUTO_PAY_ERROR;
                    $applicationForPayment->comentar = $result['status']['type'];
                    $applicationForPayment->save();

                } elseif (in_array($result['status']['type'], ['success'])) {

                    $applicationForPayment->confirmed = ApplicationForPayment::STATUS_AUTO_PAY_SUCCESS;
                    $applicationForPayment->comentar = "Завершено успешно";
                    $applicationForPayment->suma = $result['amount']['value'] ?? 0;
                    $applicationForPayment->save();
                    $fee = 0;

                    DB::table('payout_transactions')
                        ->insert([
                            'user_id' => $applicationForPayment->user_id,
                            'hall_id' => $applicationForPayment->hall_id,
                            'date' => date('Y-m-d H:i:s'),
                            'currency' => UserWallet::currencyName($applicationForPayment->currency),
                            'operation' => 'Автовыплата Jupiter',
                            'sum' => $applicationForPayment->money,
                            'fee' => $fee,
                            'total_sum' => $applicationForPayment->money + $fee,
                            'status_id' => '45',
                        ]);
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
        $cached = ApplicationForPayment::where('system_pay', $method)
            ->where('user_id', Auth::id())->get()->last();
        $requisites = json_decode($cached->requisites ?? '{}', true) ?? [];

        return view('pay.requisites.payout.phonemail', [
            'requisites' => $requisites,
            'method' => $method,
        ])->render();
    }

}
