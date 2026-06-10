<?php


namespace App\Payout;


use App\ApplicationForPayment;
use App\Models\PaymentSystems;
use App\Models\UserNotifications;
use App\MUser;
use App\PaymentsAPI\InterKassa\IkKzt;
use App\UserWallet;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class IkKztAutoPayout extends Payout implements AutoPayoutSystem
{
    private $api;

    public function __construct()
    {
        $this->api = new IkKzt();
    }

    public function checkParams(array $params): bool
    {
        list($pay_system_id, $pay_method) = explode('/', $params['payment-method']);

        if ($pay_method === 'IkKzt.card') {
            if (empty($params['cardNumber'])) {
                $this->_error = __('wallet.not_enough_data');
                return false;
            }
        }
        if ($pay_method === 'IkKzt.apm') {
            if (empty($params['phone'])) {
                $this->_error = __('wallet.not_enough_data');
                return false;
            }
        }
        return true;
    }

    public function processAutoPayment(ApplicationForPayment $applicationForPayment)
    {

        if ($applicationForPayment->confirmed == ApplicationForPayment::STATUS_AUTO_PAY_PENDING) {

            $applicationForPayment->confirmed = ApplicationForPayment::STATUS_AUTO_PAY_PROCESS;
            $applicationForPayment->comentar = 'обрабатывается автовыплата';

            if ($applicationForPayment->save()) {

                $user = MUser::findOrFail($applicationForPayment->user_id);
                $this->api = new IkKzt($applicationForPayment->system_pay, $user);
                if ($applicationForPayment->system_pay === 'IkKzt.card') $this->api->setCheckout('2');
                if ($applicationForPayment->system_pay === 'IkKzt.apm') $this->api->setCheckout('amp');
                if (in_array($applicationForPayment->system_pay, ['IkKzt.visa', 'IkKzt.mastercard'])) {
                    $this->api->setCheckout('usdt');
                }
                try {
                    Log::channel('payout')->info('IkKzt payout started');
                    $requisites = json_decode($applicationForPayment->requisites, true);
                    Log::channel('payout')->info('requisites ' . var_export($requisites, true));
                    if (!empty($requisites['customer_address'])) {
                        $requisites['customer_address'] = str_replace(',', ' ', ($requisites['customer_address']));
                    }
                    Log::channel('payout')->info('payout requisites:' . var_export($requisites, true));

                    $result = $this->api->createWithdraw((float)$applicationForPayment->money, $applicationForPayment->id, $requisites, $applicationForPayment->system_pay);

                    log::channel('payout')->info('InterKassa kzt create payout result: ' . var_export($result, true));

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

            $user = MUser::findOrFail($applicationForPayment->user_id);
            $this->api = new IkKzt($applicationForPayment->system_pay, $user);
            if ($applicationForPayment->system_pay === 'IkKzt.card') $this->api->setCheckout('2');
            if ($applicationForPayment->system_pay === 'IkKzt.apm') $this->api->setCheckout('amp');
            if (in_array($applicationForPayment->system_pay, ['IkKzt.visa', 'IkKzt.mastercard'])) {
                $this->api->setCheckout('usdt');
            }

            if ($applicationForPayment->piastrix_id > 0) {
                $result = $this->api->payoutStatus($applicationForPayment->piastrix_id);

                log::channel('payout')->info('InterKassa kzt payout status result: ' . var_export($result, true));

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
                    $applicationForPayment->suma = str_replace(',', '', $result['data']['psAmount']);
                    $applicationForPayment->save();
                    $fee = str_replace(',', '', $result['data']['ikFee']);
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
        if ($method === 'IkKzt.PayTm') {
            $cached = ApplicationForPayment::where('system_pay', $method)
                ->where('user_id', Auth::id())->get()->last();
            $requisites = json_decode($cached->requisites ?? '{}', true) ?? [];

            if (empty($requisites['first_name'])) $requisites['first_name'] = Auth::user()->name ?? '';
            if (empty($requisites['last_name'])) $requisites['last_name'] = Auth::user()->surname ?? '';

            return view('pay.requisites.payout.IkInr-PayTm', ['requisites' => $requisites, 'method' => $method,])->render();
        }

        if ($method === 'IkKzt.apm') {
            $cached = ApplicationForPayment::where('system_pay', $method)
                ->where('user_id', Auth::id())->get()->last();
            $requisites = json_decode($cached->requisites ?? '{}', true) ?? [];
            return view('pay.requisites.payout.IkKzt-Apm', ['requisites' => $requisites, 'method' => $method,])->render();
        }

        return parent::getFormRequisites($method, $pay_method);
    }

    public function getRequestRequisites(string $method, array $data): array
    {
        if ($method === 'IkInr.PayTm') {
            $requisites['wallet'] = $data['wallet'];
            $requisites['first_name'] = $data['first_name'];
            $requisites['last_name'] = $data['last_name'];

            return $requisites;
        }

        if ($method === 'IkInr.imps') {
            $requisites['ifsc'] = $data['ifsc'];
            $requisites['wallet'] = $data['wallet'];
            $requisites['first_name'] = $data['first_name'];
            $requisites['last_name'] = $data['last_name'];

            return $requisites;
        }

        if ($method === 'IkKzt.apm') {

            if (isset($data['phone'])) {
                //clear all non-numeric characters
                $data['phone'] = preg_replace('#[^\d]#', '', $data['phone']);
            }
            return $data;
        }

        return ['card' => $data['cardNumber']];
    }
}
