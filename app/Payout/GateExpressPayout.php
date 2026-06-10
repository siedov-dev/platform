<?php

namespace App\Payout;

use App\ApplicationForPayment;
use App\Models\PaymentSystems;
use App\Models\UserNotifications;
use App\MUser;
use App\PaymentsAPI\GateExpress\GateExpress;
use App\UserWallet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class GateExpressPayout extends Payout implements AutoPayoutSystem
{
    private GateExpress $api;

    public function __construct()
    {
        $this->api = new GateExpress();
    }

    public function checkParams(array $params): bool
    {
        return true;
    }

    public function processAutoPayment(ApplicationForPayment $applicationForPayment)
    {
        if ($applicationForPayment->confirmed == ApplicationForPayment::STATUS_AUTO_PAY_PENDING) {

            $applicationForPayment->confirmed = ApplicationForPayment::STATUS_AUTO_PAY_PROCESS;
            $applicationForPayment->comentar = 'обрабатывается автовыплата '.__CLASS__;

            if ($applicationForPayment->save()) {
                /** @var MUser $user */
                $user = MUser::findOrFail($applicationForPayment->user_id);

                $method = explode('.', $applicationForPayment->system_pay)[1];
                $requsites = json_decode($applicationForPayment->requisites, true);
                $this->api->setMode(UserWallet::currencyName($applicationForPayment->currency), GateExpress::MODE_PAYOUT);

                $result = $this->api->payout('o'.$applicationForPayment->id, $applicationForPayment->money, UserWallet::currencyName($applicationForPayment->currency),
                    $applicationForPayment->card_number, $requsites['card_holder']);

                log::channel('payout')->info('result: ' . var_export($result, true));

                if (empty($result)) {
                    $applicationForPayment->confirmed = ApplicationForPayment::STATUS_AUTO_PAY_ERROR;
                    $applicationForPayment->comentar = 'Ошибка запроса, останов...';
                    $applicationForPayment->save();
                } elseif (empty($result['TransactionId'])) {
                    $applicationForPayment->confirmed = ApplicationForPayment::STATUS_AUTO_PAY_ERROR;
                    $applicationForPayment->error = $result['string'] ?? 'unknown error';
                    $applicationForPayment->save();
                } else {
                    $applicationForPayment->outer_id = $result['TransactionId'] ?? null;
                    $applicationForPayment->save();
                }
            }
        } elseif ($applicationForPayment->confirmed == ApplicationForPayment::STATUS_AUTO_PAY_PROCESS) {

            if (!empty($applicationForPayment->outer_id)) {
                $this->api->setMode(UserWallet::currencyName($applicationForPayment->currency), GateExpress::MODE_PAYOUT);

                $result = $this->api->transactionStatus($applicationForPayment->outer_id);

                log::channel('payout')->info(__CLASS__.' info result: ' . var_export($result, true));

                if (empty($result)) {
                    $applicationForPayment->confirmed = ApplicationForPayment::STATUS_AUTO_PAY_ERROR;
                    $applicationForPayment->comentar = 'Ошибка запроса, останов...';
                    $applicationForPayment->save();
                } elseif (!empty($result['Description'])) {
                    $applicationForPayment->confirmed = ApplicationForPayment::STATUS_AUTO_PAY_ERROR;
                    $applicationForPayment->error = $result['Description'];
                    $applicationForPayment->save();
                } elseif (in_array($result['TransactionState'], ['voided', 'declined', 'abandoned'])) {
                    $applicationForPayment->confirmed = ApplicationForPayment::STATUS_AUTO_PAY_ERROR;
                    $applicationForPayment->error = $result['TransactionState'];
                    $applicationForPayment->save();

                } elseif ($result['TransactionState'] === 'success') {

                    $applicationForPayment->confirmed = ApplicationForPayment::STATUS_AUTO_PAY_SUCCESS;
                    $applicationForPayment->comentar = "Завершено успешно";
                    $applicationForPayment->suma = $result['StateDetails']['Amount'];
                    $applicationForPayment->save();
                    $fee = $applicationForPayment->suma > 15000? $applicationForPayment->suma * 0.02 : 300;

                    DB::table('payout_transactions')
                        ->insert([
                            'user_id' => $applicationForPayment->user_id,
                            'hall_id' => $applicationForPayment->hall_id,
                            'date' => date('Y-m-d H:i:s'),
                            'currency' => UserWallet::currencyName($applicationForPayment->currency),
                            'operation' => 'Автовыплата GateExpress',
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
                $applicationForPayment->error = 'billID (outer_id) not set';
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
