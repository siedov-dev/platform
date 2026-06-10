<?php


namespace App\Payout;


use App\ApplicationForPayment;
use App\Models\PaymentSystems;
use App\Models\UserNotifications;
use App\MUser;
use App\Payin\PayCraftPayin;
use App\PaymentsAPI\BitZone\BitZone;
use App\PaymentsAPI\LuckyPay\LuckyPay;
use App\PaymentsAPI\NashObmen\NashObmen;
use App\PaymentsAPI\PayCraft;
use App\PayoutTransaction;
use App\UserWallet;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class BitZonePayout extends Payout implements AutoPayoutSystem
{
    private BitZone $api;

    public function __construct()
    {
        $this->api = new BitZone();
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
            $user = MUser::find($applicationForPayment->user_id);

            if ($applicationForPayment->save()) {
                $method = explode('.', $applicationForPayment->system_pay)[1];
                $requisites = json_decode($applicationForPayment->requisites, true);

                $response = $this->api->payout(
                    user: $user,
                    amount: $applicationForPayment->money,
                    tr_id: $applicationForPayment->id,
                    requisites: $requisites,
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

                log::channel('payout')->info(__CLASS__ . ' info result: ' . var_export($result, true));

                if (empty($result)) {
                    $applicationForPayment->confirmed = ApplicationForPayment::STATUS_AUTO_PAY_ERROR;
                    $applicationForPayment->comentar = 'Ошибка запроса, останов...';
                    $applicationForPayment->save();
                } elseif ($result['status'] === 'canceled' || $result['status'] === 'failed') {
                    $applicationForPayment->confirmed = ApplicationForPayment::STATUS_AUTO_PAY_ERROR;
                    $applicationForPayment->comentar = $result['status'];
                    $applicationForPayment->save();
                } elseif ($result['status'] === 'success' || $result['status'] === 'closed') {

                    $applicationForPayment->confirmed = ApplicationForPayment::STATUS_AUTO_PAY_SUCCESS;
                    $applicationForPayment->comentar = "Завершено успешно";
                    $applicationForPayment->suma = $result['fiatAmount'] ?? 0;
                    $applicationForPayment->save();

                    $fee = $applicationForPayment->suma * 0.03;

                    DB::table('payout_transactions')
                        ->insert([
                            'user_id' => $applicationForPayment->user_id,
                            'hall_id' => $applicationForPayment->hall_id,
                            'date' => date('Y-m-d H:i:s'),
                            'currency' => UserWallet::currencyName($applicationForPayment->currency),
                            'operation' => 'Автовыплата BitZone',
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
        return view('pay.requisites.payout.bitzone', [
            'method' => $method,
            'pay_method' => $pay_method,
        ]);
    }

}
