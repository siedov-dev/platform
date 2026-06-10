<?php


namespace App\Payout;


use App\ApplicationForPayment;
use App\Models\PaymentSystems;
use App\Models\UserNotifications;
use App\PaymentsAPI\DualiPay\DualiPay;
use App\PaymentsAPI\InfinityPay\InfinityPay;
use App\PaymentsAPI\NirvanaPay\NirvanaPay;
use App\UserWallet;
use App\Wallet;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class InfinityPayPayout extends Payout implements AutoPayoutSystem
{
    private InfinityPay $api;

    public function __construct()
    {
        $this->api = new InfinityPay();
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
                    pay_method_id: $requisites['pay_method_id'],
                    requisites: $requisites,
                );

                Log::channel('payout')->info(__CLASS__ . ' payout result: ' . var_export($response, true));

                if (!empty($response) && $response['success']) {
                    $applicationForPayment->comentar = $response['data']['status'];
                    $applicationForPayment->confirmed = ApplicationForPayment::STATUS_AUTO_PAY_PROCESS;
                    $applicationForPayment->outer_id = $response['data']['order_hash'] ?? '';
                } else {
                    $applicationForPayment->comentar = 'response incorrect';
                    $applicationForPayment->confirmed = ApplicationForPayment::STATUS_AUTO_PAY_ERROR;
                }

                $applicationForPayment->save();

            }

        } elseif ($applicationForPayment->confirmed == ApplicationForPayment::STATUS_AUTO_PAY_PROCESS) {


            $result = $this->api->payoutStatus('o'.$applicationForPayment->getId());

            log::channel('payout')->info(__CLASS__ . ' info result: ' . var_export($result, true));

            if (empty($result) || !$result['success']) {
                $applicationForPayment->confirmed = ApplicationForPayment::STATUS_AUTO_PAY_ERROR;
                $applicationForPayment->comentar = $result['errors']['general_error'] ?? 'Ошибка запроса, останов...';
                $applicationForPayment->save();
            } elseif (in_array($result['data']['status'], ['failed', 'rejected', 'expired'])) {
                $applicationForPayment->confirmed = ApplicationForPayment::STATUS_AUTO_PAY_ERROR;
                $applicationForPayment->comentar = $result['data']['status'];
                $applicationForPayment->save();
            } elseif ($result['data']['status'] === 'success') {

                $applicationForPayment->confirmed = ApplicationForPayment::STATUS_AUTO_PAY_SUCCESS;
                $applicationForPayment->comentar = "Завершено успешно";
                $applicationForPayment->suma = $result['data']['amount'] / 100;
                $applicationForPayment->save();

                $fee = $applicationForPayment->suma * 0;

                DB::table('payout_transactions')
                    ->insert([
                        'user_id' => $applicationForPayment->user_id,
                        'hall_id' => $applicationForPayment->hall_id,
                        'date' => date('Y-m-d H:i:s'),
                        'currency' => UserWallet::currencyName($applicationForPayment->currency),
                        'operation' => 'Автовыплата InfinityPay',
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
        if ($method === 'InfinityPay.card_RUB') {
            $methods = $this->api->methods('RUB');
            $card_methods = [];
            foreach ($methods['data'] as $m) {
                if (in_array('card', $m['requisite_types'])) {
                    $card_methods[$m['id']] = $m['name'];
                }
            }
            return view('pay.requisites.payout.infinity-card', [
                'method' => $method,
                'methods' => $card_methods,
            ]);
        }

        $methods = $this->api->methods('RUB');
        $card_methods = [];
        foreach ($methods['data'] as $m) {
            if (in_array('phone', $m['requisite_types'])) {
                $card_methods[$m['id']] = $m['name'];
            }
        }
        return view('pay.requisites.payout.infinity-phone', [
            'method' => $method,
            'methods' => $card_methods,
        ]);
    }

}
