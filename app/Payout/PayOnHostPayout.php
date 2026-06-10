<?php


namespace App\Payout;


use App\ApplicationForPayment;
use App\Models\PaymentSystemGroup;
use App\Models\PaymentSystems;
use App\Models\UserNotifications;
use App\MUser;
use App\Payin\PayCraftPayin;
use App\PaymentsAPI\PayOnHost;
use App\PayoutTransaction;
use App\UserWallet;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PayOnHostPayout extends Payout implements AutoPayoutSystem
{
    private PayOnHost $api;

    public function __construct()
    {
        $this->api = new PayOnHost;
    }

    public function checkParams(array $params): bool
    {
        return true;
    }

    public function processAutoPayment(ApplicationForPayment $applicationForPayment)
    {
        if ($applicationForPayment->confirmed == ApplicationForPayment::STATUS_AUTO_PAY_PENDING) {

            $applicationForPayment->comentar = 'init';

            if ($applicationForPayment->save()) {

                $requisites = json_decode($applicationForPayment->requisites, true);
                $this->api->setIds($applicationForPayment->getSystemPay());

                if ($applicationForPayment->system_pay === 'PayOnHost.27533:32004:12236') {
                    $response = $this->api->out($applicationForPayment, 'phone', $requisites['phone']);
                } elseif ($applicationForPayment->system_pay === 'PayOnHost.27533:31235:12367') {
                    $response = $this->api->outCard($applicationForPayment, $requisites);
                } else {
                    $response = $this->api->out($applicationForPayment, 'BINANCE_ID', $applicationForPayment->card_number);
                }

                Log::channel('payout')->info(PayOnHost::class . ' payout result: ' . var_export($response, true));

                if (is_object($response) && isset($response->response->status)) {
                    if ($response->response->status === 0 || $response->response->status === 10) {
                        $applicationForPayment->outer_id = $response->response->id;
                        $applicationForPayment->comentar = 'created';
                        $applicationForPayment->confirmed = ApplicationForPayment::STATUS_AUTO_PAY_PROCESS;
                    } else {
                        $applicationForPayment->confirmed = ApplicationForPayment::STATUS_AUTO_PAY_ERROR;
                    }
                } else {
                    $applicationForPayment->comentar = $response?->error?->title ?? 'failed';
                    $applicationForPayment->confirmed = ApplicationForPayment::STATUS_AUTO_PAY_ERROR;
                }

                $applicationForPayment->save();

            }

        } elseif ($applicationForPayment->confirmed == ApplicationForPayment::STATUS_AUTO_PAY_PROCESS) {

            $this->api->setIds($applicationForPayment->getSystemPay());
            $response = $this->api->get(id: $applicationForPayment->outer_id);

            Log::channel('payout')->info(PayOnHost::class . ' payout status result: ' . var_export($response, true));

            if (!isset($response->response->status)) return response(['status' => false, 'message' => 'return status failed']);

            switch ($response->response->status) {
                case 1:

                    $amount = ($response->response->amount / 100);
                    $account_wallet_amount = abs($response->response->account_wallet_amount / 100);

                    $applicationForPayment->confirmed = ApplicationForPayment::STATUS_AUTO_PAY_SUCCESS;
                    $applicationForPayment->comentar = 'successful';
                    $applicationForPayment->money = $amount;

                    $applicationForPayment->save();

                    DB::table('payout_transactions')
                        ->insert([
                            'user_id' => $applicationForPayment->user_id,
                            'hall_id' => $applicationForPayment->hall_id,
                            'date' => date('Y-m-d H:i:s'),
                            'currency' => UserWallet::currencyName($applicationForPayment->currency),
                            'operation' => 'Payout ' . class_basename(PayOnHost::class),
                            'sum' => $amount,
                            'total_sum' => $account_wallet_amount,
                            'fee' => abs($amount - $account_wallet_amount),
                            'status_id' => 45,
                        ]);

                    break;

                case 2:
                case 3:
                case 4:
                case 5:
                    $applicationForPayment->confirmed = ApplicationForPayment::STATUS_AUTO_PAY_ERROR;
                    $applicationForPayment->comentar = $response?->error?->title ?? 'failed';
                    $applicationForPayment->save();
                    break;

            }

            if ($applicationForPayment->confirmed == ApplicationForPayment::STATUS_AUTO_PAY_SUCCESS && $applicationForPayment->user_id) {
                UserNotifications::notify($applicationForPayment->user_id, __('notify.title_payout'),
                    '<span class="gold-text">' . $applicationForPayment->money . ' ' . UserWallet::currencyName($applicationForPayment->currency) . '</span> ' . __('notify.payout_success'));
            }

        }

    }

    public function getFormRequisites(string $method, ?PaymentSystems $pay_method): string
    {
        $ps = PaymentSystems::where('method_alias', $method)->firstOrFail();

        if ($ps->system_group == PaymentSystemGroup::Crypto) {
            return view('pay.requisites.payout.binance-pay', ['method' => $method]);
        }

        if ($method === 'PayOnHost.27533:32004:12236') {
            return view('pay.requisites.payout.phone', ['method' => $method]);
        }

        return view('pay.requisites.payout.payonhost-card', ['method' => $method]);
    }

}
