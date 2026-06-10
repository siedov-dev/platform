<?php

namespace App\Payin;

use App\Models\PaymentMethodData;
use App\Models\PaymentSystems;
use App\Models\UserMoneyStat;
use App\MUser;
use App\PaymentsAPI\OneVision\OneVision;
use App\PayoutTransaction;
use Illuminate\Support\Facades\Log;

class OneVisionPayin extends Payin
{

    public function __construct(MUser $user)
    {
        parent::__construct($user);
        $this->name = 'OneVision';
        $this->type = self::TYPE_ONEPLAT_PAY;
    }

    public function process($amount, $method, array $data, ?PayoutTransaction $transaction, int $method_id): bool|array
    {
        if ($transaction === null) $transaction = new PayoutTransaction;

        $user = MUser::getAuth();

        $transaction->user_id = $user->id;
        $transaction->hall_id = $user->hall_id;
        $transaction->date = date(format: 'Y-m-d H:i:s');
        $transaction->sum = $amount;
        $transaction->currency = $user->getCurrencyName();
        $transaction->operation = class_basename(Payin::class) . ' ' . $this->name;
        $transaction->status_id = PayoutTransaction::STATUS_ONEVISION_PENDING;
        $transaction->method = $method;
        $transaction->save();
        $pmethod = explode('.', $method)[1];
        $pay_method = explode('_', $pmethod)[1];
        $pay_type = explode('_', $pmethod)[3]?? 'pay';
        if ($pay_type === 'p2p') $pay_type = 'transfer';

        $method_data = PaymentMethodData::getMethodData($method_id);
        Log::channel('pay')->info('method_data', $method_data);
        $api = new OneVision($method_data['API'], $method_data['SECRET'], $method_data['SID'], $method_data['MID']);

        $response = $api->deposit(
            user: $user,
            amount: $amount,
            tr_id: $transaction->getId(),
            method: $pay_method,
            payment_type: $pay_type,
        );
        if (!$response['success']) {
            $this->_error = $response['error_msg']?? 'System error. Please try again later.';
            $transaction->status_id = PayoutTransaction::STATUS_ONEVISION_FAIL;
            $transaction->coment = $response['error_msg'];
            $transaction->save();
        }
        $data = json_decode(base64_decode($response['data']), true);
        Log::channel('pay')->info('data', $data);

        if (!empty($data['payment_page_url'])) {
            $transaction->o_tr_id = $response['payment_id'];
            $transaction->save();
            return ['url' => $data['payment_page_url'], 'in_frame' => true];
        } else {
            $this->_error = $response['error_msg']?? 'System error. Please try again later.';

            $transaction->status_id = PayoutTransaction::STATUS_ONEVISION_FAIL;
            $transaction->coment = $response['error_msg']?? "can't get url";
            $transaction->save();

            return false;
        }

    }

    public function isHidden(int $currency_id, int $deposits_count, float $deposits_sum, ?PaymentSystems $system = null): bool
    {
        $method_type = explode('_', explode('.', $system->method_alias)[1])[2]?? 'ftd';
        if ($method_type === 'std') {
            $payment_stats = UserMoneyStat::getStat($this->user, $currency_id, UserMoneyStat::TYPE_TOTAL);
            if ($payment_stats->getDepositCount() < 3 || $payment_stats->getPayoutCount() < 1) return true;
        }
        return parent::isHidden($currency_id, $deposits_count, $deposits_sum, $system);
    }

    public function getStatusCodes(): array
    {
        return [
            PayoutTransaction::STATUS_ONEVISION_PENDING,
            PayoutTransaction::STATUS_ONEVISION_SUCCESS,
            PayoutTransaction::STATUS_ONEVISION_FAIL,
        ];
    }

}
