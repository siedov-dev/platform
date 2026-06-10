<?php


namespace App\Payout;


use App\ApplicationForPayment;
use App\Models\PaymentSystems;
use App\Models\UserNotifications;
use App\MUser;
use App\PaymentsAPI\BillBlend\BillBlend;
use App\PayoutTransaction;
use App\UserWallet;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class BillBlendPayout extends Payout implements AutoPayoutSystem
{
    private BillBlend $api;

    public function __construct()
    {
        $this->api = new BillBlend();
    }

    public function checkParams(array $params): bool
    {
        return true;
    }

    public function processAutoPayment(ApplicationForPayment $applicationForPayment)
    {
        if ($applicationForPayment->confirmed == ApplicationForPayment::STATUS_AUTO_PAY_PENDING) {

            $applicationForPayment->confirmed = ApplicationForPayment::STATUS_AUTO_PAY_PROCESS;
            $applicationForPayment->comentar = 'auto payout is processed';

            if ($applicationForPayment->save()) {

                try {
                    $requisites = json_decode($applicationForPayment->requisites, true);
                    $result = $this->api->payout($applicationForPayment, $requisites);

                    log::channel('payout')->info('BillBlend payout result: ' . var_export($result, true));

                    if (empty($result)) {
                        $applicationForPayment->confirmed = ApplicationForPayment::STATUS_AUTO_PAY_ERROR;
                        $applicationForPayment->comentar = 'Request error, stop...';
                        $applicationForPayment->save();
                        return;
                    }

                    if (isset($result['error-message'])) {
                        $applicationForPayment->confirmed = ApplicationForPayment::STATUS_AUTO_PAY_ERROR;
                        $applicationForPayment->error = $result['error-message'] ?? 'payout uknown error';
                        $applicationForPayment->save();
                        return;
                    }

                    if (!isset($result['merchant-order-id'])) {
                        $applicationForPayment->confirmed = ApplicationForPayment::STATUS_AUTO_PAY_ERROR;
                        $applicationForPayment->error = "no merchant-order-id";
                        $applicationForPayment->save();
                        return;
                    }


                    if (!isset($result['paynet-order-id'])) {
                        $applicationForPayment->confirmed = ApplicationForPayment::STATUS_AUTO_PAY_ERROR;
                        $applicationForPayment->error = "no paynet-order-id";
                        $applicationForPayment->save();
                        return;
                    }

                    $applicationForPayment->outer_id = $result['paynet-order-id'];
                    $applicationForPayment->save();

                } catch (\Exception $e) {
                    Log::channel('payout')->error($e->getMessage());
                    Log::channel('payout')->error($e->getLine());
                    Log::channel('payout')->error($e->getFile());
                    Log::channel('payout')->error($e->getTraceAsString());
                    $applicationForPayment->confirmed = ApplicationForPayment::STATUS_AUTO_PAY_ERROR;
                    $applicationForPayment->error = "internal error see logs";
                    $applicationForPayment->save();
                }
            }
        }

        if ($applicationForPayment->confirmed == ApplicationForPayment::STATUS_AUTO_PAY_PROCESS) {

            $result = $this->api->transactionStatus($applicationForPayment->getId());

            if (empty($result)) {
                $applicationForPayment->confirmed = ApplicationForPayment::STATUS_AUTO_PAY_ERROR;
                $applicationForPayment->comentar = 'Request error, stop...';
                $applicationForPayment->save();
                return;
            }

            if (isset($result['error-message'])) {
                $applicationForPayment->confirmed = ApplicationForPayment::STATUS_AUTO_PAY_ERROR;
                $applicationForPayment->error = $result['error-message'] ?? 'payout uknown error';
                $applicationForPayment->save();
                return;
            }

            if (in_array(trim($result['status']), ['declined', 'error', 'filtered'])) {
                $applicationForPayment->confirmed = ApplicationForPayment::STATUS_AUTO_PAY_ERROR;
                $applicationForPayment->error = $result['status'];
                $applicationForPayment->save();
                return;
            }

            if (trim($result['status']) === 'approved') {
                $applicationForPayment->confirmed = ApplicationForPayment::STATUS_AUTO_PAY_SUCCESS;
                $applicationForPayment->error = $result['status']??'';
                $applicationForPayment->save();

                $fee = $result['seller-commission']?? 0;
                DB::table('payout_transactions')
                    ->insert([
                        'user_id' => $applicationForPayment->user_id,
                        'hall_id' => $applicationForPayment->hall_id,
                        'date' => date('Y-m-d H:i:s'),
                        'currency' => UserWallet::currencyName($applicationForPayment->currency),
                        'operation' => 'Автовыплата Billblend',
                        'sum' => $applicationForPayment->money,
                        'fee' => $fee,
                        'total_sum' => $applicationForPayment->money,
                        'status_id' => 45
                    ]);

                if ($applicationForPayment->user_id) {
                    UserNotifications::notify($applicationForPayment->user_id, __('notify.title_payout'),
                        '<span class="gold-text">' . $applicationForPayment->money . ' ' . UserWallet::currencyName($applicationForPayment->currency) . '</span> ' . __('notify.payout_success'));
                }
            }

            $applicationForPayment->error = $result['status']?? 'unknown status response';
            $applicationForPayment->save();
        }
    }

    public function getFormRequisites(string $method, ?PaymentSystems $pay_method): string
    {
        $cached = ApplicationForPayment::where('system_pay', $method)
            ->where('user_id', Auth::id())->get()->last();
        $requisites = json_decode($cached->requisites??'{}', true)?? [];
        return view('pay.requisites.payout.BillBlend', ['method' => $method, 'requisites' => $requisites]);
    }

    public function getRequestRequisites(string $method, array $data): array
    {
        if (isset($data['phone'])) {
            //clear all non-numeric characters
            $data['phone'] = preg_replace('#[^\d]#', '', $data['phone']);
        }
        return $data;
    }
}
