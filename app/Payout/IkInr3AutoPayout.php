<?php


namespace App\Payout;


use App\ApplicationForPayment;
use App\Models\PaymentSystems;
use App\Models\UserNotifications;
use App\MUser;
use App\PaymentsAPI\InterKassa\IkInr;
use App\PaymentsAPI\InterKassa\IkInr3;
use App\PaymentsAPI\InterKassa\InterKassa;
use App\PaymentsAPI\SwiftPay\SwiftPay;
use App\UserWallet;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class IkInr3AutoPayout extends Payout implements AutoPayoutSystem
{
    private $api;

    public function __construct()
    {
        $this->api = new IkInr3();
    }

    public function checkParams(array $params): bool
    {
        list($pay_system_id, $pay_method) = explode('/', $params['payment-method']);

        if (empty($params['cardNumber'])) {
            $this->_error = __('wallet.not_enough_data');
            return false;
        }
        if ($pay_method === 'IkInr.Bank' && empty($params['ifsc'])) {
            $this->_error = __('wallet.ifsc_required');
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
                try {
                    Log::channel('payout')->info('IkInr3 payout started');
                    $requisites = json_decode($applicationForPayment->requisites, true);
                    Log::channel('payout')->info('requisites ' . var_export($requisites, true));
//                        $requisites['customer_phone'] = '8928592722';
//                        $requisites['customer_email'] = 'ktsk0419@gmail.com';
//                    if (!empty($requisites['customer_address'])) {
//                        $requisites['customer_address'] = str_replace(',',' ', ($requisites['customer_address']));
//                    }
                    Log::channel('payout')->info('payout requisites:' . var_export($requisites, true));

                    $result = $this->api->createWithdraw((float)$applicationForPayment->money, $applicationForPayment->id, $requisites, $applicationForPayment->system_pay);

                    log::channel('payout')->info('InterKassa inr3 create payout result: ' . var_export($result, true));

                    if (empty($result)) {
                        $applicationForPayment->confirmed = ApplicationForPayment::STATUS_AUTO_PAY_ERROR;
                        $applicationForPayment->comentar = 'Ошибка запроса, останов...';
                        $applicationForPayment->save();
                    } elseif ($result['status'] == 'error') {
                        $applicationForPayment->confirmed = ApplicationForPayment::STATUS_AUTO_PAY_ERROR;
                        $applicationForPayment->error = $result['status'] . "code " . $result['code'] . " " . $result['message'];
                        $applicationForPayment->save();
                    } else {
                        $applicationForPayment->confirmed = ApplicationForPayment::STATUS_AUTO_PAY_PROCESS;
                        $applicationForPayment->piastrix_id = $result['data']['transaction']['opId'];
                        $applicationForPayment->save();
                    }
                } catch (\Exception $e) {
                    Log::channel('payout')->error($e->getMessage());
                    $applicationForPayment->confirmed = ApplicationForPayment::STATUS_AUTO_PAY_ERROR;
                    $applicationForPayment->comentar = $e->getMessage();
                    $applicationForPayment->save();
                }
            }
        } elseif ($applicationForPayment->confirmed == ApplicationForPayment::STATUS_AUTO_PAY_PROCESS) {

            if ($applicationForPayment->piastrix_id > 0) {
                $result = $this->api->payoutStatus($applicationForPayment->piastrix_id);

                log::channel('payout')->info('InterKassa inr3 payout status result: ' . var_export($result, true));

                if (empty($result) || $result['status'] == 'error') {
                    $applicationForPayment->confirmed = ApplicationForPayment::STATUS_AUTO_PAY_ERROR;
                    $applicationForPayment->error = "response error";
                    $applicationForPayment->save();

                } elseif ($result['status'] == 'ok' && in_array($result['data']['state'], [3, 9])) {
                    $applicationForPayment->confirmed = ApplicationForPayment::STATUS_AUTO_PAY_ERROR;
                    $applicationForPayment->error = "status " . $result['data']['state'];
                    $applicationForPayment->save();

                } elseif ($result['status'] == 'ok' && $result['data']['state'] == 8) {
                    $applicationForPayment->confirmed = ApplicationForPayment::STATUS_AUTO_PAY_SUCCESS;
                    $applicationForPayment->comentar = "Завершено успешно";
                    $applicationForPayment->suma = $result['data']['psAmount'];
                    $applicationForPayment->save();
                    $fee = $result['data']['ikFee'];
                    if ($fee == 0) {
                        $fee = $applicationForPayment->money * 0.07;
                    }
                    // Записуємо транзакцію
                    DB::table('payout_transactions')
                        ->insert([
                            'user_id' => $applicationForPayment->user_id,
                            'hall_id' => $applicationForPayment->hall_id,
                            'date' => date('Y-m-d H:i:s'),
                            'currency' => UserWallet::currencyName($applicationForPayment->currency),
                            'operation' => 'Автовыплата InterKassa',
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
                $applicationForPayment->error = "result payment id not set";
                $applicationForPayment->save();
            }
        }
    }

    public function getFormRequisites(string $method, ?PaymentSystems $pay_method): string
    {
        if ($method === 'IkInr3.UPI') {
            $cached = ApplicationForPayment::where('system_pay', $method)
                ->where('user_id', Auth::id())->get()->last();
            $requisites = json_decode($cached->requisites??'{}', true)?? [];

            if (empty($requisites['customer_name'])) $requisites['customer_name'] = (Auth::user()->name??'').' '.(Auth::user()->surname??'');

            return view('pay.requisites.payout.IkInr3-UPI', ['requisites' => $requisites, 'method' => $method,])->render();
        }

        if ($method === 'IkInr3.Bank') {
            $cached = ApplicationForPayment::where('system_pay', $method)
                ->where('user_id', Auth::id())->get()->last();
            $requisites = json_decode($cached->requisites??'{}', true)?? [];
            return view('pay.requisites.payout.IkInr3-Bank', ['requisites' => $requisites, 'method' => $method,])->render();
        }

        return '';
    }

    public function getRequestRequisites(string $method, array $data): array
    {
        if ($method === 'IkInr3.UPI') {

            //clear all ineligible characters
            $data['customer_address'] = preg_replace('#[^a-zA-Z0-9\s]#', ' ', $data['customer_address']);
            //clear spaces
            $data['customer_address'] = preg_replace('#[\s]{2,}#', ' ', $data['customer_address']);

            $requisites['customer_name'] = $data['customer_name'];
            if (preg_match('#^[\d]*$]#', $data['cardNumber'])) {
                $data['cardNumber'] = $data['cardNumber'].'@upi';
            }
            $requisites['vpa'] = $data['cardNumber'];
            $requisites['customer_address'] = $data['customer_address'];

            if (isset($data['phone'])) {
                //clear all non-numeric characters
                $data['phone'] = preg_replace('#[^+\d]#', '', $data['phone']);

                if (preg_match('#^(\+)?(91)?(\d{10})$#', $data['phone'], $match)) {
                    $data['phone'] = $match[3];
                }
                $requisites['customer_phone'] = $data['phone'];
            }

            return $requisites;
        }

        if ($method === 'IkInr3.Bank') {
            $requisites['customer_name'] = $data['customer_name'];
            $requisites['bank_account'] = $data['cardNumber'];
            $requisites['ifsc'] = $data['ifsc'];
            //clear all ineligible characters
            $data['customer_address'] = preg_replace('#[^a-zA-Z0-9\s]#', ' ', $data['customer_address']);
            //clear spaces
            $data['customer_address'] = preg_replace('#[\s]{2,}#', ' ', $data['customer_address']);
            $requisites['customer_address'] = $data['customer_address'];

            if (isset($data['phone'])) {
                //clear all non-numeric characters
                $data['phone'] = preg_replace('#[^+\d]#', '', $data['phone']);

                if (preg_match('#^(\+)?(91)?(\d{10})$#', $data['phone'], $match)) {
                    $data['phone'] = $match[3];
                }
                $requisites['customer_phone'] = $data['phone'];
            }

            return $requisites;
        }

        return parent::getRequestRequisites($method, $data);
    }
}
