<?php


namespace App\Payout;


use App\ApplicationForPayment;
use App\Models\PaymentMethodData;
use App\Models\PaymentSystems;
use App\Models\UserNotifications;
use App\MUser;
use App\PaymentsAPI\HighHelp\HighHelp;
use App\Wallet;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class HighHelpPayout extends Payout implements AutoPayoutSystem
{
    private HighHelp $api;

    public function __construct()
    {
        $this->api = new HighHelp();
    }

    public function checkParams(array $params): bool
    {
        return true;
    }

    public function processAutoPayment(ApplicationForPayment $applicationForPayment)
    {
        if ($applicationForPayment->confirmed == ApplicationForPayment::STATUS_AUTO_PAY_PENDING) {
            /** @var PaymentSystems $psystem */
            $psystem = PaymentSystems::getPayoutSystemForMethod($applicationForPayment->system_pay, $applicationForPayment->currency);
//            $psystem = PaymentSystems::where('method_alias', $applicationForPayment->system_pay)
//                ->where('type', PaymentSystems::TYPE_PAYOUT)
//                ->first();
            $project_id = PaymentMethodData::getMethodValue($psystem->getId(), 'project_id');
            $this->api = new HighHelp($project_id);

            $applicationForPayment->confirmed = ApplicationForPayment::STATUS_AUTO_PAY_PROCESS;
            $applicationForPayment->comentar = 'init';

            if ($applicationForPayment->save()) {
                $requisites = json_decode($applicationForPayment->getRequisites(), true);
                $method = explode('.', $applicationForPayment->system_pay)[1];
                if ($method === 'card-sbp') {
                    $method = 'sbp-p2p';
                }
                if (!empty($requisites['receiver']['phone'])) {
                    $requisites['receiver']['phone'] = '+'.$requisites['receiver']['phone'];
                }

                $user = MUser::find($applicationForPayment->user_id);

                if ($applicationForPayment->system_pay === 'HighHelp.card-ecom') {
                    $response = $this->api->payoutEcom(
                        amount: $applicationForPayment->money,
                        tr_id: 'o'.$applicationForPayment->getId(),
                        method: $method,
                        user: $user,
                        requisites: $requisites,
                        currency: Wallet::currencyName($applicationForPayment->currency),
                    );
                } else {
                    $response = $this->api->payoutP2p(
                        amount: $applicationForPayment->money,
                        tr_id: 'o'.$applicationForPayment->getId(),
                        method: $method,
                        user: $user,
                        requisites: $requisites,
                        currency: Wallet::currencyName($applicationForPayment->currency),
                    );
                }


                Log::channel('payout')->info(__CLASS__ . ' payout result: ' . var_export($response, true));

                if (!empty($response['status'])) {
                    $applicationForPayment->comentar = $response['status'];
                    $applicationForPayment->confirmed = ApplicationForPayment::STATUS_AUTO_PAY_PROCESS;
                    $applicationForPayment->outer_id = $response['request_id'] ?? '';
                } else {
                    $applicationForPayment->comentar = $response['message']?? 'response incorrect';
                    $applicationForPayment->confirmed = ApplicationForPayment::STATUS_AUTO_PAY_ERROR;
                }

                $applicationForPayment->save();

            }

        } elseif ($applicationForPayment->confirmed == ApplicationForPayment::STATUS_AUTO_PAY_PROCESS) {
            /** @var PaymentSystems $psystem */
            $psystem = PaymentSystems::getPayoutSystemForMethod($applicationForPayment->system_pay, $applicationForPayment->currency);
//            $psystem = PaymentSystems::where('method_alias', $applicationForPayment->system_pay)
//                ->where('type', PaymentSystems::TYPE_PAYOUT)
//                ->first();
            $project_id = PaymentMethodData::getMethodValue($psystem->getId(), 'project_id');
            $this->api = new HighHelp($project_id);

            if ($applicationForPayment->system_pay === 'HighHelp.card_ecom') {
                $result = $this->api->payoutInfoEcom('o'.$applicationForPayment->getId());
            } else {
                $result = $this->api->payoutInfoP2p('o'.$applicationForPayment->getId());
            }


            log::channel('payout')->info(__CLASS__ . ' info result: ' . var_export($result, true));

            if (empty($result)) {
                $applicationForPayment->confirmed = ApplicationForPayment::STATUS_AUTO_PAY_ERROR;
                $applicationForPayment->comentar = $result['errors']['general_error'] ?? 'Ошибка запроса, останов...';
                $applicationForPayment->save();
            } elseif (in_array($result['status'], ['decline', 'error'])) {
                $applicationForPayment->confirmed = ApplicationForPayment::STATUS_AUTO_PAY_ERROR;
                $applicationForPayment->comentar = $result['status'];
                $applicationForPayment->save();
            } elseif ($result['status'] === 'success') {

                $applicationForPayment->confirmed = ApplicationForPayment::STATUS_AUTO_PAY_SUCCESS;
                $applicationForPayment->comentar = "Завершено успешно";
                $applicationForPayment->suma = $result['payment_info']['amount'] / 100;
                $applicationForPayment->save();

                $fee = $applicationForPayment->suma * 0.035;
                if ($applicationForPayment->currency == Wallet::CURRENCY_UZS) {
                    $fee = $applicationForPayment->suma * 0.02;
                }

                DB::table('payout_transactions')
                    ->insert([
                        'user_id' => $applicationForPayment->user_id,
                        'hall_id' => $applicationForPayment->hall_id,
                        'date' => date('Y-m-d H:i:s'),
                        'currency' => Wallet::currencyName($applicationForPayment->currency),
                        'operation' => 'Автовыплата HighHelp',
                        'sum' => $applicationForPayment->money,
                        'fee' => $fee,
                        'total_sum' => $applicationForPayment->money + $fee,
                        'status_id' => '45',
                    ]);

                if ($applicationForPayment->user_id) {
                    UserNotifications::notify($applicationForPayment->user_id, __('notify.title_payout'),
                        '<span class="gold-text">' . $applicationForPayment->money . ' ' . Wallet::currencyName($applicationForPayment->currency) . '</span> ' . __('notify.payout_success'));
                }

            }

        }
    }

    public function getFormRequisites(string $method, ?PaymentSystems $pay_method): string
    {
        if ($method === 'HighHelp.card-ecom') {
            return view('pay.requisites.payout.card-dates', [
                'method' => $method,
            ]);
        }
        if ($method === 'HighHelp.card-p2p') {
            return view('pay.requisites.payout.hh-card', [
                'method' => $method,
            ]);
        }
        if ($method === 'HighHelp.card-sbp') {
            $project_id = PaymentMethodData::getMethodValue($pay_method->getId(), 'project_id');
            $this->api = new HighHelp($project_id);
            $banks_resp = $this->api->getBanks();
            $banks = collect($banks_resp)->where('is_sbp_bank', '=', true)->values();

            return view('pay.requisites.payout.hh-sbp', [
                'method' => $method,
                'banks' => $banks,
            ]);
        }
        return '';
    }

}
