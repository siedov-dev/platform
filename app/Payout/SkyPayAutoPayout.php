<?php

namespace App\Payout;

use App\ApplicationForPayment;
use App\Models\UserNotifications;
use App\PaymentsAPI\SkyPay;
use App\UserWallet;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SkyPayAutoPayout extends Payout implements AutoPayoutSystem
{

    protected SkyPay $api;

    public function __construct()
    {
        $this->api = new SkyPay(mode: 'production');
    }

    public function checkParams(array $params): bool
    {
        return true;
    }

    public function processAutoPayment(ApplicationForPayment $applicationForPayment)
    {

        Log::channel('payout')->info(SkyPay::class . ' start');

        if ($applicationForPayment->confirmed == ApplicationForPayment::STATUS_AUTO_PAY_PENDING) {

            $applicationForPayment->comentar = 'init';

            if ($applicationForPayment->save()) {

                $currency = UserWallet::currencyName($applicationForPayment->currency);

                $brokers = collect($this->api->brokers($currency));

                $broker = $brokers->where('name', 'Любой банк РФ')->where('is_card', true)->first();

                if(!$broker?->id){
                    $this->_error = "Not find correct broker";
                    return false;
                }

                $response = $this->api->out(
                    order_id: $applicationForPayment->id,
                    broker_id: $broker?->id,
                    amount: $applicationForPayment->money,
                    currency: $currency,
                    client_order_id: $applicationForPayment->user_id,
                    card_number: $applicationForPayment->card_number,
                );

                Log::channel('payout')->info(SkyPay::class . ' payout result: ' . var_export($response, true));

                if(isset($response->id)){
                    $applicationForPayment->outer_id = $response->id;
                    $applicationForPayment->confirmed = ApplicationForPayment::STATUS_AUTO_PAY_PROCESS;
                }else{
                    $applicationForPayment->error = $response->detail ?? 'unknown error';
                    $applicationForPayment->confirmed = ApplicationForPayment::STATUS_AUTO_PAY_ERROR;
                }

                $applicationForPayment->save();
            }

        }elseif($applicationForPayment->confirmed == ApplicationForPayment::STATUS_AUTO_PAY_PROCESS){

            if(!empty($applicationForPayment->outer_id)){

                $response = $this->api->out_get(id: $applicationForPayment->outer_id);

                if(isset($response->id) && isset($response->status)){

                    switch ($response->status){
                        case 2:
                            $applicationForPayment->confirmed = ApplicationForPayment::STATUS_AUTO_PAY_SUCCESS;
                            $applicationForPayment->suma = $response->amount;

                            DB::table('payout_transactions')
                                ->insert([
                                    'user_id' => $applicationForPayment->user_id,
                                    'hall_id' => $applicationForPayment->hall_id,
                                    'date' => date('Y-m-d H:i:s'),
                                    'currency' => UserWallet::currencyName($applicationForPayment->currency),
                                    'operation' => 'Автовыплата ' . SkyPay::class,
                                    'sum' => $applicationForPayment->money,
                                    'total_sum' => $applicationForPayment->money,
                                    'status_id' => '45',
                                ]);

                            break;
                        case 3:
                            $applicationForPayment->error = $response->detail ?? 'unknown error';
                            $applicationForPayment->confirmed = ApplicationForPayment::STATUS_AUTO_PAY_ERROR;
                            break;
                    }

                }else{
                    $applicationForPayment->error = $response->detail ?? 'unknown error';
                    $applicationForPayment->confirmed = ApplicationForPayment::STATUS_AUTO_PAY_ERROR;
                }

                $applicationForPayment->save();

                if ($applicationForPayment->user_id) {
                    UserNotifications::notify($applicationForPayment->user_id, __('notify.title_payout'),
                        '<span class="gold-text">' . $applicationForPayment->money . ' ' . UserWallet::currencyName($applicationForPayment->currency) . '</span> ' . __('notify.payout_success'));
                }

            }else{
                $applicationForPayment->confirmed = ApplicationForPayment::STATUS_AUTO_PAY_ERROR;
                $applicationForPayment->error = 'outer_id not set';
            }

            $applicationForPayment->save();

        }

    }
}
