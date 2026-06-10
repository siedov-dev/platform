<?php


namespace App\Payout;


use App\ApplicationForPayment;
use App\Models\PaymentSystems;
use App\Models\PaySystemsConfig;
use App\Models\UserNotifications;
use App\MUser;
use App\PaymentsAPI\Kvitum\KvitumClient;
use App\PaymentsAPI\PiastrixClient;
use App\UserWallet;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class KvitumAutoPayout extends Payout implements AutoPayoutSystem
{
    const CURRENCY_RUB = 643; // - Российский рубль;
    const CURRENCY_USD = 840; // - Доллар США;
    const CURRENCY_EUR = 978; // - Евро;
    const CURRENCY_UAH = 980; // - Украинская гривна.

    const PIASTRIX_WALLET_METHOD = 'piastrix_uah';

    private $piastrix_client;
    protected int $shop_id = 300;
    protected string $key = '.gT>SoR4f6Hf#7{';

    public function __construct()
    {
        $conf = PaySystemsConfig::getSystemConfig(self::TYPE_KVITUM);
        $this->shop_id = $conf['shop_id'] ?? 300;
        $this->key = $conf['key'] ?? '.gT>SoR4f6Hf#7{';

        $this->piastrix_client = new KvitumClient($this->shop_id, $this->key, 10, 'https://core.feennex.com/');
    }

    public function checkParams(array $params): bool
    {
        return true;

        list($pay_system_id, $pay_method) = explode('/', $params['payment-method']);
        $pay_method = explode('.', $pay_method)[1];
        /** @var MUser $user */
        $user = MUser::find(Auth::id());

        $sumInRub = $params['sum'];

        $balance = json_decode($this->piastrix_client->check_balance());


        log::channel('payout')->info('shop balance:' . var_export($balance, true));

        if (!$balance || $balance->error_code) {
            $this->_error = 'Error: temporarily unavailable';
            return false;
        }

        if ($this->isWalletMethod($pay_method)) {
            return true;
        }

        if ($pay_method === 'bank_uah') {
            return true;
        }

        $pre_calc = json_decode($this->piastrix_client->withdraw_try($sumInRub, "ps_amount", $pay_method, UserWallet::getIntCodeById($user->currency)));

        log::channel('payout')->info('Precalc:' . var_export($pre_calc, true));

        if (isset($pre_calc->data->account_info_config->account->regex)) {
            if (!preg_match('/' . $pre_calc->data->account_info_config->account->regex . '/', $params['cardNumber'])) {
                $this->_error = __('wallet.invalid_account_no');
                return false;
            }
        }

        $account = json_decode($this->piastrix_client->check_account($params['cardNumber'], $sumInRub, $pay_method, ['customer_id' => strval(MUser::getAuth()?->getId())]));

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

            $pay_method = explode('.', $applicationForPayment->system_pay)[1];
            $user = MUser::find($applicationForPayment->user_id);

            if ($applicationForPayment->save()) {
                //transfer method for piastrix wallet
                if ($this->isWalletMethod($pay_method)) {
                    $result = json_decode($this->piastrix_client->transfer((float)$applicationForPayment->money, 'receive_amount',
                        $applicationForPayment->card_number, UserWallet::getIntCodeById($applicationForPayment->currency),
                        UserWallet::getIntCodeById($applicationForPayment->currency), $applicationForPayment->id));
                } else {
                    if ($applicationForPayment->getSystemPay() === 'Kvitum.bank_uah') {
                        $requisites = json_decode($applicationForPayment->requisites, true);

                        $result = json_decode($this->piastrix_client->withdraw(
                            account: $requisites['iban'],
                            amount: (float)$applicationForPayment->money,
                            amount_type: "ps_amount",
                            payway: $pay_method,
                            shop_currency: UserWallet::getIntCodeById($applicationForPayment->currency),
                            shop_payment_id: $applicationForPayment->id,
                            account_details: [
                                'customer_id' => strval($applicationForPayment->getUserId()),
//                                'iban' => $requisites['iban'],
                                'itn' => $requisites['itn'],
                                'full_name' => $requisites['full_name'],
                            ],
                            extra_fields: [
                                'email' => $user->email,
                            ],
                        ),
                        );

                    } else {
                        $result = json_decode($this->piastrix_client->withdraw(
                            account: $applicationForPayment->card_number,
                            amount: (float)$applicationForPayment->money,
                            amount_type: "ps_amount",
                            payway: $pay_method,
                            shop_currency: UserWallet::getIntCodeById($applicationForPayment->currency),
                            shop_payment_id: $applicationForPayment->id,
                            account_details: ['customer_id' => strval($applicationForPayment->getUserId())]),
                        );
                    }
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

            $pay_method = explode('.', $applicationForPayment->system_pay)[1];
            if ($applicationForPayment->piastrix_id > 0) {
                if ($this->isWalletMethod($pay_method)) {
                    $result = json_decode($this->piastrix_client->transfer_status((int)$applicationForPayment->id));

                    log::channel('payout')->info('transfer result: ' . var_export($result, true));

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
                            'operation' => 'Автовыплата Kvitum',
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
                            'operation' => 'Автовыплата kvitum',
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
            } else {
                $applicationForPayment->confirmed = ApplicationForPayment::STATUS_AUTO_PAY_ERROR;
                $applicationForPayment->error = "piastrix id not set";
                $applicationForPayment->save();
            }
        }
    }

    /**
     * if payment method processed by kvitum wallet system
     * @param $method
     * @return bool
     */
    private function isWalletMethod($method): bool
    {
        return in_array($method, [
            'kvitum_uah',
            'kvitum_kzt',
        ]);
    }

    public function getFormRequisites(string $method, ?PaymentSystems $pay_method): string
    {
        Log::info($method);
        if ($method === 'Kvitum.bank_uah') {
            $cached = ApplicationForPayment::where('system_pay', $method)
                ->where('user_id', Auth::id())->get()->last();
            $requisites = json_decode($cached->requisites ?? '{}', true) ?? [];
            return view('pay.requisites.payout.Kvitum-Bank', ['requisites' => $requisites, 'method' => $method,])->render();
        }

        return parent::getFormRequisites($method, $pay_method);
    }
}
