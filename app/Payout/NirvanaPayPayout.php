<?php


namespace App\Payout;


use App\ApplicationForPayment;
use App\Models\PaymentSystems;
use App\Models\UserNotifications;
use App\PaymentsAPI\DualiPay\DualiPay;
use App\PaymentsAPI\NirvanaPay\NirvanaPay;
use App\UserWallet;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class NirvanaPayPayout extends Payout implements AutoPayoutSystem
{
    private NirvanaPay $api;

    public function __construct()
    {
        $this->api = new NirvanaPay();
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

                if ($applicationForPayment->system_pay === 'NirvanaPay.SBP_RUB' || $applicationForPayment->system_pay === 'NirvanaPay.TransSBP_RUB') {
                    $account = '+' . $requisites['receiver'];
                } else {
                    $account = $applicationForPayment->getCardNumber();
                }

                $response = $this->api->payout(
                    tr_id: 'o' . $applicationForPayment->getId(),
                    amount: $applicationForPayment->money,
                    currency: UserWallet::currencyName($applicationForPayment->currency),
                    method: $requisites['extra_info']['bank_alias'],
                    receiver: $account,
                    requisites: $requisites,
                );

                Log::channel('payout')->info(__CLASS__ . ' payout result: ' . var_export($response, true));

                if (isset($response['status']) && $response['status'] === 'CREATED') {
                    $applicationForPayment->comentar = 'created';
                    $applicationForPayment->confirmed = ApplicationForPayment::STATUS_AUTO_PAY_PROCESS;
                    $applicationForPayment->outer_id = $response['trackerID'] ?? '';
                } else {
                    $applicationForPayment->comentar = $response->reason ?? 'response incorrect';
                    $applicationForPayment->confirmed = ApplicationForPayment::STATUS_AUTO_PAY_ERROR;
                }

                $applicationForPayment->save();

            }

        } elseif ($applicationForPayment->confirmed == ApplicationForPayment::STATUS_AUTO_PAY_PROCESS) {


            $result = $this->api->payoutStatus('o'.$applicationForPayment->getId());

            log::channel('payout')->info(__CLASS__ . ' info result: ' . var_export($result, true));

            if (empty($result)) {
                $applicationForPayment->confirmed = ApplicationForPayment::STATUS_AUTO_PAY_ERROR;
                $applicationForPayment->comentar = $result['errors']['general_error'] ?? 'Ошибка запроса, останов...';
                $applicationForPayment->save();
            } elseif ($result['status'] === 'ERROR') {
                $applicationForPayment->confirmed = ApplicationForPayment::STATUS_AUTO_PAY_ERROR;
                $applicationForPayment->comentar = $result['status'];
                $applicationForPayment->save();
            } elseif ($result['status'] === 'SUCCESS') {

                $applicationForPayment->confirmed = ApplicationForPayment::STATUS_AUTO_PAY_SUCCESS;
                $applicationForPayment->comentar = "Завершено успешно";
                $applicationForPayment->suma = $result['amountFiatReceived'] ?? 0;
                $applicationForPayment->save();

                $fee = $applicationForPayment->suma * $result['commission'];

                DB::table('payout_transactions')
                    ->insert([
                        'user_id' => $applicationForPayment->user_id,
                        'hall_id' => $applicationForPayment->hall_id,
                        'date' => date('Y-m-d H:i:s'),
                        'currency' => UserWallet::currencyName($applicationForPayment->currency),
                        'operation' => 'Автовыплата DualiPay',
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
        if ($method === 'NirvanaPay.SBP_RUB') {
            return view('pay.requisites.payout.nirvana-sbp', [
                'method' => $method,
                'bank_alias' => $pay_method->bank_alias ?? '',
            ]);
        }
        if ($method === 'NirvanaPay.TransSBP_RUB') {
            return view('pay.requisites.payout.nirvana-sbp', [
                'method' => $method,
                'bank_alias' => $pay_method->bank_alias ?? '',
            ]);
        }

        return view('pay.requisites.payout.nirvana-card', [
            'method' => $method,
            'bank_alias' => $pay_method->bank_alias ?? '',
        ]);
    }

}
