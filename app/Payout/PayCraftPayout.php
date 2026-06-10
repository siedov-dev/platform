<?php


namespace App\Payout;


use App\ApplicationForPayment;
use App\Models\UserNotifications;
use App\MUser;
use App\Payin\PayCraftPayin;
use App\PaymentsAPI\NashObmen\NashObmen;
use App\PaymentsAPI\PayCraft;
use App\PayoutTransaction;
use App\UserWallet;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PayCraftPayout extends Payout implements AutoPayoutSystem
{
    private PayCraft $api;

    public function __construct()
    {
        $this->api = new PayCraft;
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
                    order_id: $applicationForPayment->id,
                    amount: $applicationForPayment->money,
                    card_number: $applicationForPayment->card_number
                );

                Log::channel('payout')->info(PayCraft::class . ' payout result: ' . var_export($response, true));

                if(is_object($response) && isset($response->status)){
                    if($response->status === 1){
                        $applicationForPayment->comentar = 'created';
                        $applicationForPayment->confirmed = ApplicationForPayment::STATUS_AUTO_PAY_PROCESS;
                    }else{
                        $applicationForPayment->comentar = $response->reason ?? 'failed';
                        $applicationForPayment->confirmed = ApplicationForPayment::STATUS_AUTO_PAY_ERROR;
                    }
                }else{
                    $applicationForPayment->comentar = $response->reason ?? 'response incorrect';
                    $applicationForPayment->confirmed = ApplicationForPayment::STATUS_AUTO_PAY_ERROR;
                }

                $applicationForPayment->save();

            }

        }elseif($applicationForPayment->confirmed == ApplicationForPayment::STATUS_AUTO_PAY_PROCESS){

            $response = PayCraft::checkStatus(
                order_id: $applicationForPayment->id,
                mode: 'out',
            );

            Log::channel('payout')->info(PayCraft::class . ' payout status result: ' . var_export($response, true));

            if(!isset($response->status)) return response(['status' => false, 'message' => 'return status failed']);

            switch($response->status){
                case PayCraftPayin::STATUS_SUCCESS:
                    $applicationForPayment->confirmed = ApplicationForPayment::STATUS_AUTO_PAY_SUCCESS;
                    $applicationForPayment->comentar = 'successful';
                    $applicationForPayment->suma = $response->amountPaid;

                    $applicationForPayment->save();

                    DB::table('payout_transactions')
                        ->insert([
                            'user_id' => $applicationForPayment->user_id,
                            'hall_id' => $applicationForPayment->hall_id,
                            'date' => date('Y-m-d H:i:s'),
                            'currency' => UserWallet::currencyName($applicationForPayment->currency),
                            'operation' => 'Payout ' . PayCraft::class,
                            'sum' => $applicationForPayment->money,
                            'total_sum' => $applicationForPayment->money,
                            'status_id' => PayCraftPayin::STATUS_SUCCESS,
                        ]);

                    break;

                case PayCraftPayin::STATUS_ERROR:
                    $applicationForPayment->confirmed = ApplicationForPayment::STATUS_AUTO_PAY_ERROR;
                    $applicationForPayment->comentar = $response->reason ?? 'failed';;
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
