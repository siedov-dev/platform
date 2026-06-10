<?php


namespace App\Payout;


use App\ApplicationForPayment;
use App\Models\UserNotifications;
use App\PaymentsAPI\Repay\Repay;
use App\PaymentsAPI\Turcode;
use App\UserWallet;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TurcodePayout extends Payout implements AutoPayoutSystem
{
    private Turcode $api;

    public function __construct()
    {
        $this->api = new Turcode;
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

                $response = $this->api->out(
                    transaction: $applicationForPayment,
                    amount: $applicationForPayment->money,
                    card_number: $applicationForPayment->card_number
                );

                Log::channel('payout')->info(TurcodePayout::class . ' payout result:', (array)$response);

                if (is_object($response) && $response->id) {
                    $applicationForPayment->comentar = 'created';
                    $applicationForPayment->confirmed = ApplicationForPayment::STATUS_AUTO_PAY_PROCESS;
                    $applicationForPayment->outer_id = $response->id;
                } else {
                    $applicationForPayment->comentar = 'response incorrect';
                    $applicationForPayment->confirmed = ApplicationForPayment::STATUS_AUTO_PAY_ERROR;
                }

                $applicationForPayment->save();

            }

        } elseif ($applicationForPayment->confirmed == ApplicationForPayment::STATUS_AUTO_PAY_PROCESS) {

            $response = $this->api->getPayoutInfo(transaction_id: $applicationForPayment->outer_id);

            Log::channel('payout')->info(TurcodePayout::class . ' payout status result: ', (array)$response);

            $status = $response->status;

            Log::channel('payout')->info(TurcodePayout::class . ' payout status: '. $status);
            switch ($status) {

                case 'Pending':
                    break;

                case 'Complete':
                    $applicationForPayment->confirmed = ApplicationForPayment::STATUS_AUTO_PAY_SUCCESS;
                    $applicationForPayment->comentar = 'successful';
                    $applicationForPayment->suma = $response->amount;

                    $applicationForPayment->save();

                    $fee = $applicationForPayment->suma * 0.04;

                    DB::table('payout_transactions')
                        ->insert([
                            'user_id' => $applicationForPayment->user_id,
                            'hall_id' => $applicationForPayment->hall_id,
                            'date' => date('Y-m-d H:i:s'),
                            'currency' => UserWallet::currencyName($applicationForPayment->currency),
                            'operation' => 'Payout ' . class_basename(Turcode::class),
                            'sum' => $applicationForPayment->money,
                            'fee' => $fee,
                            'total_sum' => $applicationForPayment->money + $fee,
                            'status_id' => '45',
                        ]);

                    break;

                case 'Rejected':
                    $applicationForPayment->confirmed = ApplicationForPayment::STATUS_AUTO_PAY_ERROR;
                    $applicationForPayment->comentar = $status;
                    $applicationForPayment->save();
                    break;

            }

            if ($applicationForPayment->confirmed == ApplicationForPayment::STATUS_AUTO_PAY_SUCCESS && $applicationForPayment->user_id) {
                UserNotifications::notify($applicationForPayment->user_id, __('notify.title_payout'),
                    '<span class="gold-text">' . $applicationForPayment->money . ' ' . UserWallet::currencyName($applicationForPayment->currency) . '</span> ' . __('notify.payout_success'));
            }

        }

    }

}
