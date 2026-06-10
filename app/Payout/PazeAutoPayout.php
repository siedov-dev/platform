<?php


namespace App\Payout;


use App\ApplicationForPayment;
use App\Models\PaymentSystems;
use App\Models\UserNotifications;
use App\PaymentsAPI\Paze\PazeApi;
use App\UserWallet;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PazeAutoPayout extends Payout implements AutoPayoutSystem
{
    private $api;

    public function __construct()
    {
        $this->api = new PazeApi();
    }

    public function checkParams(array $params): bool
    {
        return true;
    }

    public function processAutoPayment(ApplicationForPayment $applicationForPayment)
    {
        if ($applicationForPayment->confirmed == ApplicationForPayment::STATUS_AUTO_PAY_PENDING) {

            $applicationForPayment->confirmed = ApplicationForPayment::STATUS_AUTO_PAY_PROCESS;
            $applicationForPayment->comentar = 'обрабатывается автовыплата';

            if ($applicationForPayment->save()) {
                $requisites = json_decode($applicationForPayment->requisites, true);

                $result = $this->api->createWithdraw($applicationForPayment->id, (float)$applicationForPayment->money,
                    $requisites, UserWallet::currencyName($applicationForPayment->currency), $applicationForPayment->system_pay);

                log::channel('payout')->info('result: ' . var_export($result, true));

                if (empty($result)) {
                    $applicationForPayment->confirmed = ApplicationForPayment::STATUS_AUTO_PAY_ERROR;
                    $applicationForPayment->comentar = 'Ошибка запроса, останов...';
                    $applicationForPayment->save();
                }

                if ($result['status'] != 200) {
                    $applicationForPayment->confirmed = ApplicationForPayment::STATUS_AUTO_PAY_ERROR;
                    $applicationForPayment->error = "" . $result['error']??'error';
                    $applicationForPayment->save();
                } else {
                    $applicationForPayment->outer_id = $result['result']['id'];
                    $applicationForPayment->save();
                }
            }
        } elseif ($applicationForPayment->confirmed == ApplicationForPayment::STATUS_AUTO_PAY_PROCESS) {

            if (!empty($applicationForPayment->outer_id)) {
                $result = $this->api->getPayment($applicationForPayment->outer_id);

                log::channel('payout')->info('paze payout check result: ' . var_export($result, true));

                if ($result['status'] != 200) {
                    $applicationForPayment->confirmed = ApplicationForPayment::STATUS_AUTO_PAY_ERROR;
                    $applicationForPayment->error = "" . $result['error'];
                    $applicationForPayment->save();
                } elseif ($result['result']['state'] === 'DECLINED') {
                    $applicationForPayment->confirmed = ApplicationForPayment::STATUS_AUTO_PAY_ERROR;
                    $applicationForPayment->error = "DECLINED " . $result['result']['errorCode']??'';
                    $applicationForPayment->comentar = "DECLINED";
                    $applicationForPayment->save();
                } elseif ($result['result']['state'] === 'CANCELLED') {
                    $applicationForPayment->confirmed = ApplicationForPayment::STATUS_AUTO_PAY_ERROR;
                    $applicationForPayment->error = "CANCELLED " . $result['result']['errorCode']??'';
                    $applicationForPayment->comentar = "CANCELLED";
                    $applicationForPayment->save();
                } elseif ($result['result']['state'] === 'COMPLETED') {
                    $applicationForPayment->confirmed = ApplicationForPayment::STATUS_AUTO_PAY_SUCCESS;
                    $applicationForPayment->comentar = "Завершено успешно";
                    $applicationForPayment->suma = $result['result']['amount'];
                    $applicationForPayment->save();
                    $fee = 0;
                    // Записуємо транзакцію
                    DB::table('payout_transactions')
                        ->insert([
                            'user_id' => $applicationForPayment->user_id,
                            'hall_id' => $applicationForPayment->hall_id,
                            'date' => date('Y-m-d H:i:s'),
                            'currency' => UserWallet::currencyName($applicationForPayment->currency),
                            'operation' => 'Автовыплата Paze',
                            'sum' => $applicationForPayment->money,
                            'fee' => $fee,
                            'total_sum' => $applicationForPayment->money,
                            'status_id' => '45',
                        ]);

                    if ($applicationForPayment->user_id) {
                        UserNotifications::notify($applicationForPayment->user_id, __('notify.title_payout'),
                            '<span class="gold-text">' . $applicationForPayment->money . ' ' . UserWallet::currencyName($applicationForPayment->currency) . '</span> ' . __('notify.payout_success'));
                    }

                }
            } else {
                $applicationForPayment->confirmed = ApplicationForPayment::STATUS_AUTO_PAY_ERROR;
                $applicationForPayment->error = "outer id not set";
                $applicationForPayment->save();
            }
        }
    }

    public function getFormRequisites(string $method, ?PaymentSystems $pay_method): string
    {
        if ($method === 'Paze.Bank') {
            $cached = ApplicationForPayment::where('system_pay', $method)
                ->where('user_id', Auth::id())->get()->last();
            $requisites = json_decode($cached->requisites??'{}', true)?? [];
            return view('pay.requisites.payout.Paze-Bank', ['requisites' => $requisites, 'method' => $method,])->render();
        }

        $cached = ApplicationForPayment::where('system_pay', $method)
            ->where('user_id', Auth::id())->get()->last();
        $requisites = json_decode($cached->requisites??'{}', true)?? [];

        $phone = preg_replace('#[^+\d]#', '', Auth::user()->phone??'');
        if (preg_match('#^(\+)?(91)?(\d{10})$#', $phone, $match)) {
            $phone = $match[3];
        }

        if (empty($requisites['phone'])) $requisites['phone'] = $phone;
        if (empty($requisites['email'])) $requisites['email'] = Auth::user()->email??'';
        if (empty($requisites['first_name'])) $requisites['first_name'] = Auth::user()->name??'';
        if (empty($requisites['last_name'])) $requisites['last_name'] = Auth::user()->surname??'';

        return view('pay.requisites.payout.Paze-Bank', ['requisites' => $requisites, 'method' => $method,])->render();
    }

    public function getRequestRequisites(string $method, array $data): array
    {
        $phone = preg_replace('#[^+\d]#', '', $data['phone']??'');
        if (preg_match('#^(\+)?(91)?(\d{10})$#', $phone, $match)) {
            $phone = $match[3];
        }
        $reqs = [];
        $reqs['phone'] = "91 ".$phone;
        $reqs['ifsc'] = $data['ifsc'];
        $reqs['bank'] = $data['bank'];
        $reqs['email'] = $data['email'];
        $reqs['account_number'] = $data['account_number'];
        $reqs['first_name'] = $data['first_name'];
        $reqs['last_name'] = $data['last_name'];
        $reqs['addressLine1'] = $data['addressLine1'];
        return $reqs;
    }
}
