<?php


namespace App\Payout;


use App\ApplicationForPayment;
use App\Models\PaymentSystems;
use App\Models\UserNotifications;
use App\MUser;
use App\PaymentsAPI\Payme\Payme;
use App\UserWallet;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PaymePayout extends Payout implements AutoPayoutSystem
{
    private Payme $api;

    public function __construct()
    {
        $this->api = new Payme();
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
                $requisites = json_decode($applicationForPayment->requisites, true);
                $sub_method = explode('_', $method)[1];
                if ($method === 'RUB_phone' || $method === 'RUB_fiat:by_mobile') {
                    $acc = '+'.$requisites['phone'];
                } else {
                    $acc = $applicationForPayment->card_number;
                }
                $filter = "";
                $fiat = 0;
                if ($sub_method === 'phone') $filter = 'by_mobile';
                if ($sub_method === 'card') $filter = 'card_number';
                if (str_contains($method, ":")) {
                    $filter = explode(':', $method)[1];
                    $fiat = 1;
                }

                $response = $this->api->withdrawRequest(
                    amount: $applicationForPayment->money,
                    currency: UserWallet::currencyName($applicationForPayment->currency),
                    system_type: $filter,
                    fiat: $fiat,
                );

                Log::channel('payout')->info(__CLASS__ . ' withdrawal lookup result: ' . var_export($response, true));

                if (empty($response['status']) || $response['status'] !== 1) {
                    $applicationForPayment->confirmed = ApplicationForPayment::STATUS_AUTO_PAY_ERROR;
                    $applicationForPayment->comentar = 'pay lookup error';
                    $applicationForPayment->save();
                } elseif (empty($response['data']['ads'])) {
                    $applicationForPayment->confirmed = ApplicationForPayment::STATUS_AUTO_PAY_ERROR;
                    $applicationForPayment->comentar = 'no ads';
                    $applicationForPayment->save();
                } else {
                    $ad_id = $response['data']['ads'][0]['ad_id'];
                    $response = $this->api->withdrawCreate($ad_id, $applicationForPayment->id, $acc, $applicationForPayment->money, UserWallet::currencyName($applicationForPayment->currency));
                    Log::channel('payout')->info(__CLASS__ . ' withdrawal create result: ' . var_export($response, true));

                    if (empty($response['status']) || $response['status'] !== 1) {
                        $applicationForPayment->confirmed = ApplicationForPayment::STATUS_AUTO_PAY_ERROR;
                        $applicationForPayment->comentar = 'pay create error';
                        $applicationForPayment->save();
                    } elseif (empty($response['data']['invoice_id'])) {
                        $applicationForPayment->confirmed = ApplicationForPayment::STATUS_AUTO_PAY_ERROR;
                        $applicationForPayment->comentar = 'no id';
                        $applicationForPayment->save();
                    } else {
                        $applicationForPayment->outer_id = $response['data']['invoice_id'];
                        $applicationForPayment->save();
                    }
                }
            }

        } elseif ($applicationForPayment->confirmed == ApplicationForPayment::STATUS_AUTO_PAY_PROCESS) {

            if ($applicationForPayment->outer_id > 0) {

                $result = $this->api->payoutStatus($applicationForPayment->outer_id);

                log::channel('payout')->info(__CLASS__ . ' info result: ' . var_export($result, true));

                if (empty($result) || !isset($result['data']['status']['id'])) {
                    $applicationForPayment->confirmed = ApplicationForPayment::STATUS_AUTO_PAY_ERROR;
                    $applicationForPayment->comentar = 'Ошибка запроса, останов...';
                    $applicationForPayment->save();
                } elseif ($result['data']['status']['id'] === -1) {
                    $applicationForPayment->confirmed = ApplicationForPayment::STATUS_AUTO_PAY_ERROR;
                    $applicationForPayment->comentar = 'canceled';
                    $applicationForPayment->save();
                } elseif ($result['data']['status']['id'] === 3) {
                    $applicationForPayment->comentar = 'not confirmed';
                    $applicationForPayment->save();
                } elseif ($result['data']['status']['id'] === 2) {
                    $response = $this->api->confirmPayout($applicationForPayment->outer_id);
                    log::channel('payout')->info(__CLASS__ . ' info confirm: ' . var_export($response, true));
                    $applicationForPayment->comentar = 'confirmed';
                    $applicationForPayment->save();
                } elseif ($result['data']['status']['id'] === 1) {
                    $response = $this->api->confirmPayout($applicationForPayment->outer_id);
                    log::channel('payout')->info(__CLASS__ . ' info confirm: ' . var_export($response, true));
                    $applicationForPayment->confirmed = ApplicationForPayment::STATUS_AUTO_PAY_SUCCESS;
                    $applicationForPayment->comentar = "Завершено успешно";
                    $applicationForPayment->suma = $result['data']['status']['fiat_amount']?? $applicationForPayment->money;
                    $applicationForPayment->save();

                    $fee = ($result['data']['status']['amount']?? $applicationForPayment->suma)  - $applicationForPayment->suma;

                    DB::table('payout_transactions')
                        ->insert([
                            'user_id' => $applicationForPayment->user_id,
                            'hall_id' => $applicationForPayment->hall_id,
                            'date' => date('Y-m-d H:i:s'),
                            'currency' => UserWallet::currencyName($applicationForPayment->currency),
                            'operation' => 'Автовыплата PayMe',
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
        if ($method === 'Payme.RUB_phone') {
            return view('pay.requisites.payout.phone', [
                'method' => $method,
                'pay_method' => $pay_method,
            ]);
        }
        if ($method === 'Payme.RUB_fiat:by_mobile') {
            return view('pay.requisites.payout.phone', [
                'method' => $method,
                'pay_method' => $pay_method,
            ]);
        }


        return parent::getFormRequisites($method, $pay_method);
    }

}
