<?php


namespace App\Payout;


use App\ApplicationForPayment;
use App\Helpers\Currency;
use App\Models\UserNotifications;
use App\PaymentsAPI\BillLine;
use App\UserWallet;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class BillLineAutoPayout extends Payout implements AutoPayoutSystem
{
    private $api;

    public function __construct()
    {
        $this->api = new BillLine();
    }

    public function checkParams(array $params): bool
    {
        if (!preg_match('#^[0-9]{16}$#', $params['cardNumber'])) {
            $this->_error = __('wallet.invalid_account_no');
            return false;
        }

//        if ($params['sum'] < 500) {
//            $this->_error = __('wallet.minimum_payout_amount', ['amount' => 500]);
//            return false;
//        }

        $balance = $this->api->getBalance($params['currency']);

        if (!$balance || ($balance < $params['sum'])) {
            log::channel('payout')->info('BillLine low balance 1' . var_export($balance, true) . ' sum:' . $params['sum']);
            $this->api = new BillLine(2);
            if (!$balance || ($balance < $params['sum'])) {
                log::channel('payout')->info('BillLine low balance 2' . var_export($balance, true) . ' sum:' . $params['sum']);
            }
        }

        log::channel('payout')->info('BillLine balance ' . var_export($balance, true));

        return true;
    }

    public function processAutoPayment(ApplicationForPayment $applicationForPayment)
    {
        if ($applicationForPayment->confirmed == ApplicationForPayment::STATUS_AUTO_PAY_PENDING) {

            $applicationForPayment->confirmed = ApplicationForPayment::STATUS_AUTO_PAY_PROCESS;
            $applicationForPayment->comentar = 'обрабатывается автовыплата';

            if ($applicationForPayment->save()) {

                $amount = (float)$applicationForPayment->money;
//                $amount = $applicationForPayment->currency == UserWallet::CURRENCY_UAH? (float)$applicationForPayment->money :
//                    round(Currency::convert((float)$applicationForPayment->money, UserWallet::currencyName($applicationForPayment->currency), 'UAH'),2);

                log::channel('payout')->info('amount: ' . $amount . ' currency:' . UserWallet::currencyName($applicationForPayment->currency));

                $thread = 1;
                $this->api = new BillLine($thread);
                $balance = $this->api->getBalance(UserWallet::currencyName($applicationForPayment->currency));

                if (!$balance || ($balance < $amount)) {
                    log::channel('payout')->info('BillLine low balance thread 1 ' . var_export($balance, true) . ' sum:' . $amount);
//                    $thread = 2;
//                    $this->api = new BillLine($thread);
//                    $balance = $this->api->getBalance(UserWallet::currencyName($applicationForPayment->currency));
                    if (!$balance || ($balance < $amount)) {
                        log::channel('payout')->info('BillLine low balance thread 2 ' . var_export($balance, true) . ' sum:' . $amount);
                    }
                }

                $result = $this->api->payout($applicationForPayment->card_number, $amount,
                    $applicationForPayment->id, $applicationForPayment->currency);

                log::channel('payout')->info('result: ' . var_export($result, true));

                if (empty($result)) {
                    $applicationForPayment->confirmed = ApplicationForPayment::STATUS_AUTO_PAY_PENDING;
                    $applicationForPayment->comentar = 'Ошибка запроса, перезапуск...';
                    $applicationForPayment->save();
                } elseif ($result['status'] == BillLine::STATUS_ERROR) {
                    $applicationForPayment->confirmed = ApplicationForPayment::STATUS_AUTO_PAY_ERROR;
                    $applicationForPayment->error = "" . $result['code'] . " " . $result['description'];
                    $applicationForPayment->save();
                } else {
                    $applicationForPayment->piastrix_id = $result['payout_id'];
                    $applicationForPayment->token = $thread;
                    $applicationForPayment->save();
                }
            }
        } elseif ($applicationForPayment->confirmed == ApplicationForPayment::STATUS_AUTO_PAY_PROCESS) {

            if ($applicationForPayment->piastrix_id > 0) {
//                $result = $this->api->getPayoutStatus((int)$applicationForPayment->piastrix_id);
                $this->api = new BillLine((int)$applicationForPayment->token);
                $result = $this->api->getPayoutStatus((int)$applicationForPayment->id);

                log::channel('payout')->info('result: ' . var_export($result, true));

                if ($result['status'] == BillLine::STATUS_BLOCKED) {
                    $applicationForPayment->confirmed = ApplicationForPayment::STATUS_AUTO_PAY_ERROR;
                    $applicationForPayment->error = "" . $result['code'] . " " . $result['description'];
                    $applicationForPayment->save();
                } elseif ($result['status'] == BillLine::STATUS_SUCCESS) {
                    $applicationForPayment->confirmed = ApplicationForPayment::STATUS_AUTO_PAY_SUCCESS;
                    $applicationForPayment->comentar = "Завершено успешно";
//                    $applicationForPayment->suma = $result->data->payee_receive;
                    $applicationForPayment->save();
                    $fee = round($applicationForPayment->money * 0.015, 2);
                    // Записуємо транзакцію
                    DB::table('payout_transactions')
                        ->insert([
                            'user_id' => $applicationForPayment->user_id,
                            'hall_id' => $applicationForPayment->hall_id,
                            'date' => date('Y-m-d H:i:s'),
                            'currency' => UserWallet::currencyName($applicationForPayment->currency),
                            'operation' => 'Автовыплата BillLine',
                            'sum' => $applicationForPayment->money,
                            'fee' => $fee,
                            'total_sum' => $applicationForPayment->money,
                            'status_id' => '45',
                        ]);

                    UserNotifications::notify($applicationForPayment->user_id, __('notify.title_payout'),
                        '<span class="gold-text">' . $applicationForPayment->money . ' ' . UserWallet::currencyName($applicationForPayment->currency) . '</span> ' . __('notify.payout_success'));

                } elseif ($result['status'] == BillLine::STATUS_ERROR) {
                    $applicationForPayment->error = "" . $result['code'] . " " . $result['description'];
                    $applicationForPayment->comentar = "Не финальная ошибка, см. код статуса";
                    $applicationForPayment->save();
                } elseif ($result['status'] == BillLine::STATUS_PENDING) {
                    $applicationForPayment->comentar = "status pending";
                    $applicationForPayment->save();
                }

            }

        }
    }
}
