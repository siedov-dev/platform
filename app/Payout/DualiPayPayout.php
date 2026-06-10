<?php


namespace App\Payout;


use App\ApplicationForPayment;
use App\Models\PaymentSystems;
use App\Models\UserNotifications;
use App\PaymentsAPI\DualiPay\DualiPay;
use App\UserWallet;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DualiPayPayout extends Payout implements AutoPayoutSystem
{
    private DualiPay $api;

    public function __construct()
    {
        $this->api = new DualiPay();
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
                $method_id = str_replace('_', " ", explode('.', $applicationForPayment->system_pay)[1]);
                $method_id = str_replace('-', '/', $method_id);
                $requisites = json_decode($applicationForPayment->getRequisites(), true);

                if ($applicationForPayment->system_pay === 'DualiPay.SBP_P2P_RUB') {
                    $account = '+'.$requisites['phone'];
                } else {
                    $account = $applicationForPayment->getCardNumber();
                }

                $response = $this->api->payout(
                    tr_id: 'o'.$applicationForPayment->id,
                    user_id: $applicationForPayment->user_id,
                    method: $method_id,
                    amount: $applicationForPayment->money,
                    currency: UserWallet::currencyName($applicationForPayment->currency),
                    identifier: $account,
                    requisites: $requisites,
                );

                Log::channel('payout')->info(__CLASS__ . ' payout result: ' . var_export($response, true));

                if (isset($response['ticket']['token'])) {
                        $applicationForPayment->comentar = 'created';
                        $applicationForPayment->confirmed = ApplicationForPayment::STATUS_AUTO_PAY_PROCESS;
                        $applicationForPayment->outer_id = $response['ticket']['token'];
                } else {
                    $applicationForPayment->comentar = $response->reason ?? 'response incorrect';
                    $applicationForPayment->confirmed = ApplicationForPayment::STATUS_AUTO_PAY_ERROR;
                }

                $applicationForPayment->save();

            }

        } elseif ($applicationForPayment->confirmed == ApplicationForPayment::STATUS_AUTO_PAY_PROCESS) {

            if ($applicationForPayment->outer_id > 0) {

                $result = $this->api->getPayoutTicket($applicationForPayment->outer_id);

                log::channel('payout')->info(__CLASS__.' info result: ' . var_export($result, true));

                if (empty($result) || !$result['success']) {
                    $applicationForPayment->confirmed = ApplicationForPayment::STATUS_AUTO_PAY_ERROR;
                    $applicationForPayment->comentar = $result['errors']['general_error']?? 'Ошибка запроса, останов...';
                    $applicationForPayment->save();
                } elseif ($result['ticket']['status'] === 'EXPIRED' || $result['ticket']['status'] === 'DECLINED') {
                    $applicationForPayment->confirmed = ApplicationForPayment::STATUS_AUTO_PAY_ERROR;
                    $applicationForPayment->comentar = $result['ticket']['status'];
                    $applicationForPayment->save();
                } elseif ($result['ticket']['status'] === 'COMPLETED') {

                    $applicationForPayment->confirmed = ApplicationForPayment::STATUS_AUTO_PAY_SUCCESS;
                    $applicationForPayment->comentar = "Завершено успешно";
                    $applicationForPayment->suma = $result['ticket']['amount']?? 0;
                    $applicationForPayment->save();

                    $fee = $applicationForPayment->suma * 0.035;

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
            } else {
                $applicationForPayment->confirmed = ApplicationForPayment::STATUS_AUTO_PAY_ERROR;
                $applicationForPayment->error = 'external id not set';
                $applicationForPayment->save();
            }
        }
    }

    public function getFormRequisites(string $method, ?PaymentSystems $pay_method): string
    {
        if ($method === 'DualiPay.SBP_P2P_RUB') {
            return view('pay.requisites.payout.duali-sbp', [
                'method' => $method,
            ]);
        }

        return parent::getFormRequisites($method, $pay_method);
    }

}
