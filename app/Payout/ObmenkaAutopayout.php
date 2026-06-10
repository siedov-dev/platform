<?php


namespace App\Payout;


use App\ApplicationForPayment;
use App\Models\UserNotifications;
use App\MUser;
use App\PaymentsAPI\ObmenkaUa;
use App\UserWallet;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ObmenkaAutopayout extends Payout implements AutoPayoutSystem
{
    private $api;
    public function __construct()
    {
        $this->api = new ObmenkaUa();
    }

    public function checkParams(array $params): bool
    {
        list($pay_system_id, $pay_method) = explode('/', $params['payment-method']);
        /** @var MUser $user */
        $user = MUser::find(Auth::id());

        $sumInRub = $params['sum'];

        $o_currency = $this->api::getObmenkaCurrency($pay_method);

        $balance = $this->api->getCurrBalance($o_currency);

        log::channel('payout')->info('Obmenka balance:'.var_export($balance, true));

        if($this->api->isError){
            $this->_error = __('wallet.payout_not_available');
            log::channel('payout')->error('Obmenka error:'.$this->api->errDescr);
//            return false;
        }

        return true;
    }

    public function processAutoPayment(ApplicationForPayment $applicationForPayment)
    {
        if($applicationForPayment->confirmed == ApplicationForPayment::STATUS_AUTO_PAY_PENDING){

            $applicationForPayment->confirmed = ApplicationForPayment::STATUS_AUTO_PAY_PROCESS;
            $applicationForPayment->comentar = 'обрабатывается автовыплата';

            if($applicationForPayment->save()){

                $opayout = $this->api->createPayout($applicationForPayment->id, $applicationForPayment->card_number,
                    $this->api::getObmenkaCurrency($applicationForPayment->system_pay), (float)$applicationForPayment->money);

                log::channel('payout')->info('Obmenka create payout: '.var_export($opayout,true));

                if($this->api->isError) {
                    $applicationForPayment->confirmed = ApplicationForPayment::STATUS_AUTO_PAY_ERROR;
                    $applicationForPayment->comentar = 'Ошибка выплаты, останов...';
                    $applicationForPayment->error = $this->api->errCode.' '.$this->api->errDescr;
                    $applicationForPayment->save();
                    return;
                }

                $this->api->validatePayout($applicationForPayment->id);

                if(!$this->api->validatePayout($applicationForPayment->id)) {
                    $applicationForPayment->confirmed = ApplicationForPayment::STATUS_AUTO_PAY_ERROR;
                    $applicationForPayment->comentar = 'Ошибка выплаты, останов...';
                    $applicationForPayment->error = $this->api->errCode.' '.$this->api->errDescr;
                    $applicationForPayment->save();
                    return;
                }

                $this->api->processPayout($applicationForPayment->id);

                if($this->api->isError) {
                    $applicationForPayment->confirmed = ApplicationForPayment::STATUS_AUTO_PAY_ERROR;
                    $applicationForPayment->comentar = 'Ошибка выплаты, останов...';
                    $applicationForPayment->error = $this->api->errCode.' '.$this->api->errDescr;
                    $applicationForPayment->save();
                }
            }
        } elseif($applicationForPayment->confirmed == ApplicationForPayment::STATUS_AUTO_PAY_PROCESS){

            $result = $this->api->payoutStatus($applicationForPayment->id);

            Log::channel('payout')->info("Obmenka payout id $applicationForPayment->id status: ".var_export($result,true));

            if($this->api->isError) {
                $applicationForPayment->confirmed = ApplicationForPayment::STATUS_AUTO_PAY_ERROR;
                $applicationForPayment->comentar = $this->api->errDescr;
                $applicationForPayment->error = $this->api->errCode.' '.$this->api->errDescr;
                $applicationForPayment->save();
            }

            if(isset($result['status'])){
                //not started for some reason
                if(in_array($result['status'], ['CREATED'])){
                    $this->api->processPayout($applicationForPayment->id);

                    if($this->api->isError) {
                        $applicationForPayment->confirmed = ApplicationForPayment::STATUS_AUTO_PAY_ERROR;
                        $applicationForPayment->comentar = 'Ошибка выплаты, останов...';
                        $applicationForPayment->error = $this->api->errCode.' '.$this->api->errDescr;
                        $applicationForPayment->save();
                    }
                }
                if(in_array($result['status'], ['PAYED'])){
                    $applicationForPayment->confirmed = ApplicationForPayment::STATUS_AUTO_PAY_SUCCESS;
                    $applicationForPayment->comentar = "Завершено успешно";
                    $applicationForPayment->suma = $result['amount'];
                    $applicationForPayment->save();
                    $fee = $result['fee'];
                    // Записуємо транзакцію
                    DB::table('payout_transactions')
                        ->insert([
                            'user_id'   => $applicationForPayment->user_id,
                            'hall_id'   => $applicationForPayment->hall_id,
                            'date'      => date('Y-m-d H:i:s'),
                            'currency' => UserWallet::currencyName($applicationForPayment->currency),
                            'operation' => 'Автовыплата ObmenkaUa',
                            'sum'       => $result['amount'],
                            'fee'       => $fee,
                            'total_sum' => $result['full_amount'],
                            'status_id' => '45',
                        ]);

                    if($applicationForPayment->user_id) {
                        UserNotifications::notify($applicationForPayment->user_id, __('notify.title_payout'),
                            '<span class="gold-text">' . $applicationForPayment->money . ' ' . UserWallet::currencyName($applicationForPayment->currency) . '</span> ' . __('notify.payout_success'));
                    }
                }

                if(in_array($result['status'], ['CANCELED', 'REVERSED', 'FAILED', 'REFILL'])) {
                    $applicationForPayment->confirmed = ApplicationForPayment::STATUS_AUTO_PAY_ERROR;
                    $applicationForPayment->error = 'status '.$result['status'];
                    $applicationForPayment->comentar = 'Ошибка status '.$result['status'];
                    $applicationForPayment->save();
                }

                $applicationForPayment->comentar = 'status '.$result['status'];
                $applicationForPayment->save();
            }
        }
    }
}
