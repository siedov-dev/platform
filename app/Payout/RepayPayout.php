<?php


namespace App\Payout;


use App\ApplicationForPayment;
use App\Models\PaymentSystems;
use App\Models\UserNotifications;
use App\MUser;
use App\Payin\PayCraftPayin;
use App\PaymentsAPI\NashObmen\NashObmen;
use App\PaymentsAPI\PayCraft;
use App\PaymentsAPI\Repay\Repay;
use App\PayoutTransaction;
use App\UserWallet;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class RepayPayout extends Payout implements AutoPayoutSystem
{
    private Repay $api;

    public function __construct()
    {
        $this->api = new Repay;
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

                /** @var PaymentSystems $psystem */
                $psystem = PaymentSystems::where('method_alias', $applicationForPayment->system_pay)
                    ->where('type', PaymentSystems::TYPE_PAYOUT)
                    ->first();

                $req_type = 'card_number';
                $requisites = json_decode($applicationForPayment->requisites, true);
                $acc = $applicationForPayment->card_number;
                $sbp_bank = "";
                if ($applicationForPayment->system_pay === 'Repay.SBP_RUB') {
                    $req_type = 'phone_number';
                    $acc = $requisites['phone'];
                    $sbp_bank = $requisites['option_id'];
                }

                $response = $this->api->out(
                    transaction: $applicationForPayment,
                    amount: $applicationForPayment->money,
                    bank_id: $psystem->bank_alias,
                    card_number: $acc,
                    type: $req_type,
                    option_id: $sbp_bank,
                );

                Log::channel('payout')->info(RepayPayout::class . ' payout result: ', (array)$response);

                $payload = $response?->payload ?? null;

                if (is_object($payload) && $payload?->state === 'created') {
                    $applicationForPayment->comentar = 'created';
                    $applicationForPayment->confirmed = ApplicationForPayment::STATUS_AUTO_PAY_PROCESS;
                } else {
                    $applicationForPayment->comentar = $response->detail->msg ?? ((!empty($response->detail ?? false) && is_string($response->detail)) ? $response->detail : 'response incorrect');
                    $applicationForPayment->confirmed = ApplicationForPayment::STATUS_AUTO_PAY_ERROR;
                }

                $applicationForPayment->save();

            }

        } elseif ($applicationForPayment->confirmed == ApplicationForPayment::STATUS_AUTO_PAY_PROCESS) {

            $response = $this->api->get(transaction_id: $applicationForPayment->id, out: true);

            Log::channel('payout')->info(RepayPayout::class . ' payout status result: ', (array)$response);

            if (!isset($response->payload)) return response(['status' => false, 'message' => 'return payload failed']);

            $payload = $response?->payload;

            switch ($payload->state) {

                case 'created':
                case 'proceedings':
                    break;

                case 'completed':
                    $applicationForPayment->confirmed = ApplicationForPayment::STATUS_AUTO_PAY_SUCCESS;
                    $applicationForPayment->comentar = 'successful';
                    $applicationForPayment->suma = $payload->amount;

                    $applicationForPayment->save();

                    $fee = $applicationForPayment->suma * 0.025;

                    DB::table('payout_transactions')
                        ->insert([
                            'user_id' => $applicationForPayment->user_id,
                            'hall_id' => $applicationForPayment->hall_id,
                            'date' => date('Y-m-d H:i:s'),
                            'currency' => UserWallet::currencyName($applicationForPayment->currency),
                            'operation' => 'Payout ' . class_basename(Repay::class),
                            'sum' => $applicationForPayment->money,
                            'fee' => $fee,
                            'total_sum' => $applicationForPayment->money + $fee,
                            'status_id' => '45',
                        ]);

                    break;

                case 'rejected_timeout':
                case 'reject_by_office':
                case 'reject_manual_by_client':
                case 'rejected_by_system':
                case 'rejected_by_new_deal':
                    $applicationForPayment->confirmed = ApplicationForPayment::STATUS_AUTO_PAY_ERROR;
                    $applicationForPayment->comentar = var_export($response->error, true) ?? 'failed';;
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
        if ($method === 'Repay.SBP_RUB') {
            $api = new Repay();
            $banks = $api->sbpBanks();
            return view('pay.requisites.payout.repay-sbp', [
                'method' => $method,
                'banks' => $banks,
            ]);
        }
        return parent::getFormRequisites($method, $pay_method);
    }
}
