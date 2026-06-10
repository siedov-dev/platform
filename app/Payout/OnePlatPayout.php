<?php


namespace App\Payout;


use App\ApplicationForPayment;
use App\Models\PaymentSystems;
use App\Models\UserNotifications;
use App\PaymentsAPI\DualiPay\DualiPay;
use App\PaymentsAPI\InfinityPay\InfinityPay;
use App\PaymentsAPI\NirvanaPay\NirvanaPay;
use App\PaymentsAPI\OnePlat\OnePlat;
use App\UserWallet;
use App\Wallet;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class OnePlatPayout extends Payout implements AutoPayoutSystem
{
    private OnePlat $api;

    public function __construct()
    {
        $this->api = new OnePlat();
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

            if ($applicationForPayment->save()) {
                $requisites = json_decode($applicationForPayment->getRequisites(), true);

                $response = $this->api->payout(
                    tr_id: 'o' . $applicationForPayment->getId(),
                    amount: $applicationForPayment->money,
                    pay_method_id: $this->methodId($applicationForPayment->system_pay),
                    requisites: $requisites,
                );

                Log::channel('payout')->info(__CLASS__ . ' payout result: ' . var_export($response, true));

                if (!empty($response) && $response['success']) {
                    $applicationForPayment->comentar = $response['withdraw']['status'];
                    $applicationForPayment->confirmed = ApplicationForPayment::STATUS_AUTO_PAY_PROCESS;
                    $applicationForPayment->outer_id = $response['withdraw']['id'] ?? '';
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

            $result = $this->api->payoutStatus($applicationForPayment->outer_id);

            log::channel('payout')->info(__CLASS__ . ' info result: ' . var_export($result, true));

            if (empty($result) || !$result['success']) {
                $applicationForPayment->confirmed = ApplicationForPayment::STATUS_AUTO_PAY_ERROR;
                $applicationForPayment->comentar = $result['errors']['general_error'] ?? 'Ошибка запроса, останов...';
                $applicationForPayment->save();
            } elseif (in_array($result['withdraw']['status'], [-3, -2, -1])) {
                $applicationForPayment->confirmed = ApplicationForPayment::STATUS_AUTO_PAY_ERROR;
                $applicationForPayment->comentar = $result['status'];
                $applicationForPayment->save();
            } elseif ($result['withdraw']['status'] === 2) {

                $applicationForPayment->confirmed = ApplicationForPayment::STATUS_AUTO_PAY_SUCCESS;
                $applicationForPayment->comentar = "Завершено успешно";
                $applicationForPayment->suma = $result['withdraw']['amount'];
                $applicationForPayment->save();

                $fee = $applicationForPayment->suma - $result['withdraw']['amount_to_pay'];

                DB::table('payout_transactions')
                    ->insert([
                        'user_id' => $applicationForPayment->user_id,
                        'hall_id' => $applicationForPayment->hall_id,
                        'date' => date('Y-m-d H:i:s'),
                        'currency' => UserWallet::currencyName($applicationForPayment->currency),
                        'operation' => 'Автовыплата 1Plat',
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
    }

    public function getFormRequisites(string $method, ?PaymentSystems $pay_method): string
    {
        $banks = $this->api->payoutMethods()['banks'];
        if ($method === '1Plat.card_RUB') {
            return view('pay.requisites.payout.1plat-card', [
                'method' => $method,
                'banks' => $banks,
            ]);
        }

        return view('pay.requisites.payout.1plat-sbp', [
            'method' => $method,
            'banks' => $banks,
        ]);
    }

    private function methodId(string $method): int
    {
        return match ($method) {
            '1Plat.card_RUB' => 9,
            '1Plat.sbp_RUB' => 10,
        };
    }

}
