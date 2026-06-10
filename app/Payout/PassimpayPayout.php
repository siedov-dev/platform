<?php


namespace App\Payout;


use App\Models\PaymentSystems;
use App\Models\UserNotifications;
use App\MUser;
use App\PaymentsAPI\Passimpay\Passimpay;
use App\PaymentsAPI\SwiftPay\SwiftPay;
use App\UserWallet;
use Illuminate\Support\Facades\Auth;
use App\ApplicationForPayment;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PassimpayPayout extends Payout implements AutoPayoutSystem
{
    private $api;

    public function __construct()
    {
        $this->api = new Passimpay();
    }

    public function checkParams(array $params): bool
    {
        list($pay_system_id, $pay_method) = explode('/', $params['payment-method']);
        return true;
    }

    public function processAutoPayment(ApplicationForPayment $applicationForPayment)
    {
        if ($applicationForPayment->confirmed == ApplicationForPayment::STATUS_AUTO_PAY_PENDING) {

            $applicationForPayment->confirmed = ApplicationForPayment::STATUS_AUTO_PAY_PROCESS;
            $applicationForPayment->comentar = 'обрабатывается автовыплата';

            if ($applicationForPayment->save()) {

                $requisites = json_decode($applicationForPayment->requisites, true);
                $result = $this->api->withdraw($applicationForPayment->money, $applicationForPayment->getCardNumber(), $requisites['currencyOut']);

                log::channel('payout')->info(__CLASS__. 'create payout result: ' . var_export($result, true));

                if (empty($result)) {
                    $applicationForPayment->confirmed = ApplicationForPayment::STATUS_AUTO_PAY_ERROR;
                    $applicationForPayment->comentar = 'Ошибка запроса, останов...';
                    $applicationForPayment->save();
                } elseif (isset($result['result']) && $result['result'] == 1) {

                    $applicationForPayment->confirmed = ApplicationForPayment::STATUS_AUTO_PAY_SUCCESS;
                    $applicationForPayment->comentar = "Завершено успешно";
                    $applicationForPayment->suma = $applicationForPayment->money;
                    $applicationForPayment->outer_id = $result['payment_id'];
                    $applicationForPayment->save();

                    $fee = $applicationForPayment->money * 0.05;

                    DB::table('payout_transactions')
                        ->insert([
                            'user_id' => $applicationForPayment->user_id,
                            'hall_id' => $applicationForPayment->hall_id,
                            'date' => date('Y-m-d H:i:s'),
                            'currency' => UserWallet::currencyName($applicationForPayment->currency),
                            'operation' => 'Автовыплата SwiftPay',
                            'sum' => $applicationForPayment->money,
                            'fee' => $fee,
                            'total_sum' => $applicationForPayment->money + $fee,
                            'status_id' => '45',
                        ]);
                } else {
                    $applicationForPayment->confirmed = ApplicationForPayment::STATUS_AUTO_PAY_ERROR;
                    $applicationForPayment->error = "" . $result['message']?? "unknown error";
                    $applicationForPayment->save();
                }
            }
        }
    }

    public function getFormRequisites(string $method, ?PaymentSystems $pay_method): string
    {
        return view('pay.requisites.payout.passimpay', ['currencies' => $this->api->getCurrencies()]);
    }
}
