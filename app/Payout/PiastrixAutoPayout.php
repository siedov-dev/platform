<?php


namespace App\Payout;


use App\ApplicationForPayment;
use App\Models\PaySystemsConfig;
use App\Models\UserNotifications;
use App\MUser;
use App\PaymentsAPI\PiastrixClient;
use App\UserWallet;
use App\Wallet;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PiastrixAutoPayout extends Payout implements AutoPayoutSystem
{
    const CURRENCY_RUB = 643; // - Российский рубль;
    const CURRENCY_USD = 840; // - Доллар США;
    const CURRENCY_EUR = 978; // - Евро;
    const CURRENCY_UAH = 980; // - Украинская гривна.

    private $piastrix_client;
    protected int $shop_id = 2509;
    protected string $key = 'P3wOB1pwqXuUrY8VPC';

    public function __construct()
    {
        $conf = PaySystemsConfig::getSystemConfig(self::TYPE_PIASTRIX);
        $this->shop_id = $conf['shop_id'] ?? 2509;
        $this->key = $conf['key'] ?? 'P3wOB1pwqXuUrY8VPC';

        $this->piastrix_client = new PiastrixClient($this->shop_id, $this->key);
    }

    public function checkParams(array $params): bool
    {

        list($pay_system_id, $pay_method) = explode('/', $params['payment-method']);
        /** @var MUser $user */
        $user = MUser::find(Auth::id());

        $sumInRub = $params['sum'];

        $balance = json_decode($this->piastrix_client->check_balance());


        log::channel('payout')->info('shop balance:' . var_export($balance, true));

        if (!$balance || $balance->error_code) {
            $this->_error = 'Выплата временно не доступна';
            return false;
        }

        if ($pay_method == ApplicationForPayment::TYPE_PIASTRIX_RUB) {
            return true;
        }

        $pre_calc = json_decode($this->piastrix_client->withdraw_try($sumInRub,
            /*$params['system'] == ApplicationForPayment::TYPE_VISA_UAH? "shop_amount" : */ "ps_amount",
            self::paySystemToPiastrixPayWay($pay_method), UserWallet::getIntCodeById($user->currency)));

        log::channel('payout')->info('Precalc:' . var_export($pre_calc, true));

        if (isset($pre_calc->data->account_info_config->account->regex)) {
            if (!preg_match('/' . $pre_calc->data->account_info_config->account->regex . '/', $params['cardNumber'])) {
                $this->_error = __('wallet.invalid_account_no');
                return false;
            }
        }

        $account = json_decode($this->piastrix_client->check_account($params['cardNumber'], $sumInRub, self::paySystemToPiastrixPayWay($pay_method)));

        if ($account->error_code) {
            $this->_error = $account->message;
            return false;
        }

        return true;
    }

    public function processAutoPayment(ApplicationForPayment $applicationForPayment)
    {
        if ($applicationForPayment->confirmed == ApplicationForPayment::STATUS_AUTO_PAY_PENDING) {

            $applicationForPayment->confirmed = ApplicationForPayment::STATUS_AUTO_PAY_PROCESS;
            $applicationForPayment->comentar = 'обрабатывается автовыплата';

            if ($applicationForPayment->save()) {
                //transfer method for piastrix
                if ($applicationForPayment->system_pay == ApplicationForPayment::TYPE_PIASTRIX_RUB) {
                    $result = json_decode($this->piastrix_client->transfer((float)$applicationForPayment->money, 'receive_amount',
                        $applicationForPayment->card_number, self::CURRENCY_RUB, self::CURRENCY_RUB, $applicationForPayment->id));
                } else {
                    $result = json_decode($this->piastrix_client->withdraw($applicationForPayment->card_number, (float)$applicationForPayment->money,
                        /*$applicationForPayment->system_pay == ApplicationForPayment::TYPE_VISA_UAH? "shop_amount" : */ "ps_amount",
                        PiastrixAutoPayout::paySystemToPiastrixPayWay($applicationForPayment->system_pay),
                        UserWallet::getIntCodeById($applicationForPayment->currency), $applicationForPayment->id));
                }

                log::channel('payout')->info('result: ' . var_export($result, true));

                if (empty($result)) {
                    $applicationForPayment->confirmed = ApplicationForPayment::STATUS_AUTO_PAY_ERROR;
                    $applicationForPayment->comentar = 'Ошибка запроса, останов...';
                    $applicationForPayment->save();
                }

                if ($result->error_code) {
                    $applicationForPayment->confirmed = ApplicationForPayment::STATUS_AUTO_PAY_ERROR;
                    $applicationForPayment->error = "" . $result->error_code . " " . $result->message;
                    $applicationForPayment->save();
                } else {
                    $applicationForPayment->piastrix_id = $result->data->id;
                    $applicationForPayment->save();
                }
            }
        } elseif ($applicationForPayment->confirmed == ApplicationForPayment::STATUS_AUTO_PAY_PROCESS) {

            if ($applicationForPayment->piastrix_id > 0) {
                if ($applicationForPayment->system_pay == ApplicationForPayment::TYPE_PIASTRIX_RUB) {
                    $result = json_decode($this->piastrix_client->transfer_status((int)$applicationForPayment->id));

                    log::channel('payout')->info('result: ' . var_export($result, true));

                    if (empty($result)) {
                        $applicationForPayment->error = "response error";
                        $applicationForPayment->save();
                        return;
                    }
                    if ($result->error_code != 0) {
                        $applicationForPayment->confirmed = ApplicationForPayment::STATUS_AUTO_PAY_ERROR;
                        $applicationForPayment->error = "" . $result->error_code . " " . $result->message;
                        $applicationForPayment->save();
                        return;
                    }

                    $applicationForPayment->confirmed = ApplicationForPayment::STATUS_AUTO_PAY_SUCCESS;
                    $applicationForPayment->comentar = "Завершено успешно";
                    $applicationForPayment->suma = $result->data->receive_amount;
                    $applicationForPayment->save();
                    $fee = $result->data->write_off_amount - $result->data->receive_amount;
                    // Записуємо транзакцію
                    DB::table('payout_transactions')
                        ->insert([
                            'user_id' => $applicationForPayment->user_id,
                            'hall_id' => $applicationForPayment->hall_id,
                            'date' => date('Y-m-d H:i:s'),
                            'currency' => UserWallet::currencyName($applicationForPayment->currency),
                            'operation' => 'Автовыплата piastrix',
                            'sum' => $applicationForPayment->money,
                            'fee' => $fee,
                            'total_sum' => $result->data->write_off_amount,
                            'status_id' => '45',
                        ]);

                    if ($applicationForPayment->user_id) {
                        UserNotifications::notify($applicationForPayment->user_id, "Вывод",
                            '<span class="gold-text">' . $applicationForPayment->money . ' ' . UserWallet::currencyName($applicationForPayment->currency) . '</span>' . ' успешно выведены');
                    }

                    return;
                }

                $result = json_decode($this->piastrix_client->withdraw_id((int)$applicationForPayment->piastrix_id));

                log::channel('payout')->info('result: ' . var_export($result, true));

                if ($result->error_code) {
                    $applicationForPayment->confirmed = ApplicationForPayment::STATUS_AUTO_PAY_ERROR;
                    $applicationForPayment->error = "" . $result->error_code . " " . $result->message;
                    $applicationForPayment->save();
                } elseif ($result->data->status == 5) {
                    $applicationForPayment->confirmed = ApplicationForPayment::STATUS_AUTO_PAY_SUCCESS;
                    $applicationForPayment->comentar = "Завершено успешно";
                    $applicationForPayment->suma = $result->data->payee_receive;
                    $applicationForPayment->save();
                    $fee = $result->data->shop_write_off - $result->data->payee_receive;
                    // Записуємо транзакцію
                    DB::table('payout_transactions')
                        ->insert([
                            'user_id' => $applicationForPayment->user_id,
                            'hall_id' => $applicationForPayment->hall_id,
                            'date' => date('Y-m-d H:i:s'),
                            'currency' => UserWallet::currencyName($applicationForPayment->currency),
                            'operation' => 'Автовыплата piastrix',
                            'sum' => $applicationForPayment->money,
                            'fee' => $fee,
                            'total_sum' => $result->data->shop_write_off,
                            'status_id' => '45',
                        ]);

                    if ($applicationForPayment->user_id) {
                        UserNotifications::notify($applicationForPayment->user_id, __('notify.title_payout'),
                            '<span class="gold-text">' . $applicationForPayment->money . ' ' . UserWallet::currencyName($applicationForPayment->currency) . '</span> ' . __('notify.payout_success'));
                    }

                } elseif (in_array($result->data->status, [6, 10, 11])) {
                    $applicationForPayment->confirmed = ApplicationForPayment::STATUS_AUTO_PAY_ERROR;
                    $applicationForPayment->error = 'status ' . $result->data->status;
                    $applicationForPayment->comentar = "Ошибка, см. код статуса";
                    $applicationForPayment->save();

                }
            }
        }
    }

    public static function paySystemToPiastrixPayWay(string $pay_system): string
    {
        switch ($pay_system) {
            case ApplicationForPayment::TYPE_VISA_RUB:
                return 'card_rub';
            case ApplicationForPayment::TYPE_VISA_UAH:
                return 'card_uah';
            case ApplicationForPayment::TYPE_QIWI:
                return 'qiwi_rub';
            case ApplicationForPayment::TYPE_YANDEX:
                return 'yamoney_rub';
            case ApplicationForPayment::TYPE_PIASTRIX_RUB:
                return 'piastrix_rub';
            default:
                return 'unknown_system';
        }
    }

    public function canProceed(float $amount, string $method, Wallet $wallet): bool
    {
        if ($method == ApplicationForPayment::TYPE_VISA_RUB) {
            if ($amount < 700) {
//                $this->_error = 'Минимальная сумма вывода VISA 700 руб.';
                $this->_error = __('wallet.minimum_payout_amount', ['amount' => 700, 'curr' => 'RUB']);
                return false;
            }
        }
        return parent::canProceed($amount, $method, $wallet);
    }

}
