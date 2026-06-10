<?php


namespace App\Payout;


use App\ApplicationForPayment;
use App\Models\PaymentSystems;
use App\Models\UserNotifications;
use App\PaymentsAPI\Payscrow\Payscrow;
use App\UserWallet;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PayscrowPayout extends Payout implements AutoPayoutSystem
{
    private Payscrow $api;

    public function __construct()
    {
        $this->api = new Payscrow();
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
                $method = explode('.', $applicationForPayment->system_pay)[1];

                if ($applicationForPayment->system_pay === 'Payscrow.2ec6dbd6-49a5-45d0-bd6d-b0134ee4639a') {
                    $requisites = json_decode($applicationForPayment->getRequisites(), true);
                    $account = $requisites['phone'];
                    $method = $requisites['bank'];
                } else {
                    $account = $applicationForPayment->getCardNumber();
                }

                $response = $this->api->payout(
                    id: $applicationForPayment->id,
                    amount: $applicationForPayment->money,
                    method_id: $method,
                    account: $account,
                );

                Log::channel('payout')->info(__CLASS__ . ' payout result: ' . var_export($response, true));

                if (isset($response['id'])) {
                        $applicationForPayment->comentar = 'created';
                        $applicationForPayment->confirmed = ApplicationForPayment::STATUS_AUTO_PAY_PROCESS;
                        $applicationForPayment->outer_id = $response['id'];
                } else {
                    $applicationForPayment->comentar = $response->reason ?? 'response incorrect';
                    $applicationForPayment->confirmed = ApplicationForPayment::STATUS_AUTO_PAY_ERROR;
                }

                $applicationForPayment->save();

            }

        } elseif ($applicationForPayment->confirmed == ApplicationForPayment::STATUS_AUTO_PAY_PROCESS) {

            if ($applicationForPayment->outer_id > 0) {

                $result = $this->api->orderInfo($applicationForPayment->outer_id);

                log::channel('payout')->info(__CLASS__.' info result: ' . var_export($result, true));

                if (empty($result) || !$result['success']) {
                    $applicationForPayment->confirmed = ApplicationForPayment::STATUS_AUTO_PAY_ERROR;
                    $applicationForPayment->comentar = $result['message']?? 'Ошибка запроса, останов...';
                    $applicationForPayment->save();
                } elseif ($result['order']['status'] === 'CanceledByTimeout' || $result['order']['status'] === 'CanceledByService') {
                    $applicationForPayment->confirmed = ApplicationForPayment::STATUS_AUTO_PAY_ERROR;
                    $applicationForPayment->comentar = $result['order']['status'];
                    $applicationForPayment->save();
                } elseif ($result['order']['status'] === 'Completed') {

                    $applicationForPayment->confirmed = ApplicationForPayment::STATUS_AUTO_PAY_SUCCESS;
                    $applicationForPayment->comentar = "Завершено успешно";
                    $applicationForPayment->suma = $result['order']['amount']?? 0;
                    $applicationForPayment->save();

                    $fee = ($result['order']['fee']?? 0) * $applicationForPayment->suma / 100;


                    DB::table('payout_transactions')
                        ->insert([
                            'user_id' => $applicationForPayment->user_id,
                            'hall_id' => $applicationForPayment->hall_id,
                            'date' => date('Y-m-d H:i:s'),
                            'currency' => UserWallet::currencyName($applicationForPayment->currency),
                            'operation' => 'Автовыплата Payscrow',
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

        if ($method === 'Payscrow.2ec6dbd6-49a5-45d0-bd6d-b0134ee4639a') {
            $m = $this->api->methods();
            $m = collect($m['payment_methods']);
            $sbp = $m->where('method_type', 'SBP');

            return view('pay.requisites.payout.luckypay.phone', [
                'method' => $method,
                'banks' => $sbp,
            ]);
        }

        return parent::getFormRequisites($method, $pay_method);
    }

}
