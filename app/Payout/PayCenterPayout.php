<?php

namespace App\Payout;

use App\ApplicationForPayment;
use App\Models\PaymentSystems;
use App\Models\UserNotifications;
use App\MUser;
use App\PaymentsAPI\PaymentCenter\PaymentCenter;
use App\UserWallet;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;

class PayCenterPayout extends Payout implements AutoPayoutSystem
{

    private PaymentCenter $api;

    public function __construct()
    {
        $this->api = new PaymentCenter();
        $this->api->setMode(PaymentCenter::MODE_PAYOUT);
    }

    public function checkParams(array $params): bool
    {
        return true;
    }

    public function processAutoPayment(ApplicationForPayment $applicationForPayment)
    {
        if ($applicationForPayment->confirmed == ApplicationForPayment::STATUS_AUTO_PAY_PENDING) {

            $applicationForPayment->confirmed = ApplicationForPayment::STATUS_AUTO_PAY_PROCESS;
            $applicationForPayment->comentar = 'обрабатывается автовыплата PaymentCenter';

            if ($applicationForPayment->save()) {
                /** @var MUser $user */
                $user = MUser::findOrFail($applicationForPayment->user_id);

                $method = explode('.', $applicationForPayment->system_pay)[1];
                $requsites = json_decode($applicationForPayment->requisites, true);

                $result = $this->api->payout($applicationForPayment->card_number, 'o'.$applicationForPayment->id,
                    $applicationForPayment->money, $requsites['card_holder'], UserWallet::currencyName($applicationForPayment->currency));

                log::channel('payout')->info('result: ' . var_export($result, true));

                if (empty($result)) {
                    $applicationForPayment->confirmed = ApplicationForPayment::STATUS_AUTO_PAY_ERROR;
                    $applicationForPayment->comentar = 'Ошибка запроса, останов...';
                    $applicationForPayment->save();
                } elseif (empty($result['success'])) {
                    $applicationForPayment->confirmed = ApplicationForPayment::STATUS_AUTO_PAY_ERROR;
                    $applicationForPayment->error = $result['errMessage'] ?? 'unknown error';
                    $applicationForPayment->save();
                } else {
                    $applicationForPayment->piastrix_id = $result['tranId'] ?? null;
                    $applicationForPayment->save();
                }
            }
        } elseif ($applicationForPayment->confirmed == ApplicationForPayment::STATUS_AUTO_PAY_PROCESS) {

            if ($applicationForPayment->piastrix_id > 0) {

                $result = $this->api->status('o'.$applicationForPayment->id);

                log::channel('payout')->info('PaymentCenter info result: ' . var_export($result, true));

                if (empty($result)) {
                    $applicationForPayment->confirmed = ApplicationForPayment::STATUS_AUTO_PAY_ERROR;
                    $applicationForPayment->comentar = 'Ошибка запроса, останов...';
                    $applicationForPayment->save();
                } elseif ($result['success'] !== 'true') {
                    $applicationForPayment->confirmed = ApplicationForPayment::STATUS_AUTO_PAY_ERROR;
                    $applicationForPayment->error = 'payout fail';
                    $applicationForPayment->save();
                } elseif ($result['tranStatus'] === 'CHARGED') {

                    $applicationForPayment->confirmed = ApplicationForPayment::STATUS_AUTO_PAY_SUCCESS;
                    $applicationForPayment->comentar = "Завершено успешно";
                    $applicationForPayment->suma = $result['amount'];
                    $applicationForPayment->save();
                    $fee = 0;

                    DB::table('payout_transactions')
                        ->insert([
                            'user_id' => $applicationForPayment->user_id,
                            'hall_id' => $applicationForPayment->hall_id,
                            'date' => date('Y-m-d H:i:s'),
                            'currency' => UserWallet::currencyName($applicationForPayment->currency),
                            'operation' => 'Автовыплата PaymentCenter',
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

    public function getFormRequisites(string $method, ?PaymentSystems $pay_method): string
    {
        return view('pay.requisites.payout.card-holder', ['method' => $method]);
    }

    public static function payoutCallback(Request $request)
    {
        Log::channel('payout')->info('PayCenter payout callback');
        Log::channel('payout')->info(var_export($request->all()));
        $api = new PaymentCenter();
        $api->setMode(PaymentCenter::MODE_PAYOUT);

        $data = $request->all();

        if (!isset($data['Order_Id'])) {
            Log::channel('pay')->error('Order_Id field not set');
            return response('', 500);
        }

        /** @var ApplicationForPayment $applicationForPayment */
        $applicationForPayment = ApplicationForPayment::where('id', explode('o', $data['Order_Id'])[1])
            ->first();
        if (!preg_match('#^PayCenter\..*#', $applicationForPayment->system_pay)) {
            Log::channel('payout')->info('wrong paysystem '. $applicationForPayment->system_pay);
            return response('ok', 500);
        }
        if ($applicationForPayment->confirmed == ApplicationForPayment::STATUS_AUTO_PAY_PROCESS) {
            $payout_system = new PayCenterPayout();
            $payout_system->processAutoPayment($applicationForPayment);
        } else {
            Log::channel('payout')->info('payment has status'. $applicationForPayment->confirmed);
        }

        return response('ok', 200);
    }

}
