<?php


namespace App\Payout;


use App\ApplicationForPayment;
use App\Models\PaymentSystems;
use App\Models\UserNotifications;
use App\MUser;
use App\PaymentsAPI\Brpay\Brpay;
use App\PayoutTransaction;
use App\UserWallet;
use App\Wallet;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class BrpayAutoPayout extends Payout implements AutoPayoutSystem
{
    private $api;

    public function __construct()
    {
//        $this->api = new Brpay();
    }

    public function checkParams(array $params): bool
    {
        list($pay_system_id, $pay_method) = explode('/', $params['payment-method']);
        /** @var MUser $user */
        $user = MUser::find(Auth::id());
        return true;
    }

    public function processAutoPayment(ApplicationForPayment $applicationForPayment)
    {
        $this->api = new Brpay($applicationForPayment->currency);
        if ($applicationForPayment->confirmed == ApplicationForPayment::STATUS_AUTO_PAY_PENDING) {

            $applicationForPayment->confirmed = ApplicationForPayment::STATUS_AUTO_PAY_PROCESS;
            $applicationForPayment->comentar = 'обрабатывается автовыплата';

            if ($applicationForPayment->save()) {

                $this->api->setKeysForMethod($applicationForPayment->getSystemPay());

                if ($applicationForPayment->system_pay === 'Brpay.PHONE_SBP') {
                    $requsites = json_decode($applicationForPayment->requisites, true);
                    $result = $this->api->transferPhone(
                        amount: $applicationForPayment->money,
                        phone: $requsites['phone'],
                        bank: $requsites['bank'],
                        transaction_id: $applicationForPayment->id);
                } elseif($applicationForPayment->currency !== Wallet::CURRENCY_RUB) {
                    $result = $this->api->transferCard((float)$applicationForPayment->money, $applicationForPayment->card_number,
                        $applicationForPayment->id, Wallet::currencyName($applicationForPayment->currency));
                } else {
                    $result = $this->api->transfer((float)$applicationForPayment->money, $applicationForPayment->card_number, $applicationForPayment->id);
                }


                log::channel('payout')->info('result: ' . var_export($result, true));

                if (empty($result) || $result === false) {
                    $applicationForPayment->confirmed = ApplicationForPayment::STATUS_AUTO_PAY_ERROR;
                    $applicationForPayment->comentar = 'Ошибка запроса, останов...';
                    $applicationForPayment->save();
                    return;
                }

                if (isset($result['sp_error_code]'])) {
                    $applicationForPayment->confirmed = ApplicationForPayment::STATUS_AUTO_PAY_ERROR;
                    $applicationForPayment->error = "" . $result['sp_error_code]'] . " " . $result['sp_error_message]'];
                    $applicationForPayment->save();
                } else {
                    $applicationForPayment->piastrix_id = $result['sp_transaction_id'];
                    $applicationForPayment->save();
                }
            }
        } elseif ($applicationForPayment->confirmed == ApplicationForPayment::STATUS_AUTO_PAY_PROCESS) {

            if ($applicationForPayment->piastrix_id > 0) {
                $this->api->setKeysForMethod($applicationForPayment->getSystemPay());

                $result = $this->api->payoutStatus((int)$applicationForPayment->piastrix_id);

                log::channel('payout')->info('result: ' . var_export($result, true));

                if (isset($result['sp_error_code'])) {
                    $applicationForPayment->confirmed = ApplicationForPayment::STATUS_AUTO_PAY_ERROR;
                    $applicationForPayment->error = "" . $result['sp_error_code]'] . " " . $result['sp_error_message]'];
                    $applicationForPayment->save();
                } elseif ($result['sp_payout_status'] == 'REJECT') {
                    $applicationForPayment->confirmed = ApplicationForPayment::STATUS_AUTO_PAY_ERROR;
                    $applicationForPayment->error = "REJECT";
                    $applicationForPayment->save();
                } elseif ($result['sp_payout_status'] == 'EXECUTED') {
                    $applicationForPayment->confirmed = ApplicationForPayment::STATUS_AUTO_PAY_SUCCESS;
                    $applicationForPayment->comentar = "Завершено успешно";
                    $applicationForPayment->suma = $result['sp_amount'];
                    $applicationForPayment->save();
                    $fee = $result['sp_full_amount'] - $result['sp_amount'];
                    // Записуємо транзакцію
                    DB::table('payout_transactions')
                        ->insert([
                            'user_id' => $applicationForPayment->user_id,
                            'hall_id' => $applicationForPayment->hall_id,
                            'date' => date('Y-m-d H:i:s'),
                            'currency' => UserWallet::currencyName($applicationForPayment->currency),
                            'operation' => 'Автовыплата Brpay',
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
    }

    public function getFormRequisites(string $method, ?PaymentSystems $pay_method): string
    {
        if ($method === 'Brpay.PHONE_SBP') {
            return view('pay.requisites.payout.br-phone', ['method' => $method]);
        }
        return parent::getFormRequisites($method, $pay_method);
    }
}
