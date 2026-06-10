<?php

namespace App\Payin;

use App\Models\PaymentSystemGroup;
use App\Models\PaymentSystems;
use App\MUser;
use App\PaymentsAPI\PayOnHost;
use App\PaymentsAPI\Repay\Repay;
use App\PayoutTransaction;
use App\UserWallet;

class RepayPayin extends Payin
{

    protected Repay $api;

    public function __construct(MUser $user)
    {
        parent::__construct($user);
        $this->name = class_basename(Repay::class);
        $this->type = self::TYPE_REPAY;
        $this->api = new Repay;
    }

    public function isHidden(int $currency_id, int $deposits_count, float $deposits_sum, ?PaymentSystems $system = null): bool
    {
        if (in_array($system->method_alias, ['Repay.SBP_STD'])) {
            if ($deposits_count < 5) return true;
        }
        if (in_array($system->method_alias, ['Repay.SBP_FTD'])) {
            if ($deposits_count >= 5) return true;
        }
        if (in_array($system->method_alias, ['Repay.SBERPAY_STD'])) {
            if ($deposits_count < 3) return true;
        }
        if (in_array($system->method_alias, ['Repay.SBERPAY_FTD'])) {
            if ($deposits_count >= 3) return true;
        }
//        if (in_array($system->method_alias, ['Repay.TPAY'])) {
//            if ($deposits_count < 5) return true;
//        }

        return parent::isHidden($currency_id, $deposits_count, $deposits_sum, $system);
    }

    public function process($amount, $method, array $data, ?PayoutTransaction $transaction, int $method_id): bool|array
    {
        if ($transaction === null) $transaction = new PayoutTransaction;

        $user = auth()->user();

        $ps = PaymentSystems::where('type', 0)->where('method_alias', $method)->firstOrFail();

        $transaction->user_id = $user->id;
        $transaction->hall_id = $user->hall_id;
        $transaction->date = date(format: 'Y-m-d H:i:s');
        $transaction->sum = $amount;
        $transaction->currency = $user->getWallet($user->currency)->getCurrencyName();
        $transaction->operation = class_basename(Payin::class) . ' ' . $this->name;
        $transaction->status_id = PayoutTransaction::STATUS_REPAY_PENDING;
        $transaction->method = $ps->method_alias;
        $transaction->save();

        $response = $this->api->in(
            transaction: $transaction,
        );

        if (!empty($response->payload->payment_url)) {

            $transaction->o_tr_id = $response->payload->id;
            $transaction->save();

            return ['url' => $response->payload->payment_url];
        } else {
            $this->_error = 'System error. Please try again later.';

            $transaction->status_id = PayoutTransaction::STATUS_REPAY_FAIL;
            $transaction->coment = (is_string($response?->error) ? $response?->error : null) ?? $this->_error;
            $transaction->save();

            return false;
        }

    }

    public function getStatusCodes(): array
    {
        return [
            PayoutTransaction::STATUS_REPAY_PENDING,
            PayoutTransaction::STATUS_REPAY_SUCCESS,
            PayoutTransaction::STATUS_REPAY_FAIL,
        ];
    }

}
