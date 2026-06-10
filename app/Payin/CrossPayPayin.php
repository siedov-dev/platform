<?php

namespace App\Payin;

use App\Models\PaymentSystems;
use App\MUser;
use App\PaymentsAPI\CrossPay\CrossPay;
use App\PaymentsAPI\Empayre\Empayre;
use App\PayoutTransaction;
use App\UserWallet;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

/**
 * @deprecated
 */
class CrossPayPayin extends Payin
{

    public function __construct(MUser $user)
    {
        parent::__construct($user);
        $this->name = "CrossPay";
        $this->type = self::TYPE_CROSSPAY;
    }

    public function process($amount, $method, array $data, ?PayoutTransaction $transaction, int $method_id)
    {
        if ($transaction === null) $transaction = new PayoutTransaction();

        $user = MUser::getAuth();

        $currency = $this->user->currency;

        $transaction->user_id = $user->id;
        $transaction->hall_id = $user->hall_id;
        $transaction->date = date("Y-m-d H:i:s");
        $transaction->sum = $amount;
        $transaction->currency = $user->getWallet($currency)->getCurrencyName();
        $transaction->operation = 'Пополнение счета через CrossPay';
        $transaction->status_id = PayoutTransaction::STATUS_CROSSPAY_PENDING;
        $transaction->save();

        $trusted = $user->getDepositsCount() >= 3;

        $api = new CrossPay();
        $method = explode('.', $method)[1];


        $resp = $api->payin($transaction->id, $amount, $user->getWallet($currency)->getCurrencyName(), $data, $trusted, $method);


        if (empty($resp) || $resp['status'] !== 'success') {
            $this->_error = $resp['message'] ?? 'system error';
            return false;
        }

        $transaction->o_tr_id = $resp['data'][0]['order_uuid'];
        $transaction->save();

        return ['url' => $resp['data'][0]['pay_url']];
    }

    public function isHidden(int $currency_id, int $deposits_count, float $deposits_sum, ?PaymentSystems $system = null): bool
    {
        if ($system->method_alias === 'CrossPay.UAH_trusted' || $system->method_alias === 'CrossPay.KZT_trusted') {
            if ($deposits_count < 3) return true;
        }
        if ($system->method_alias === 'CrossPay.UAH' || $system->method_alias === 'CrossPay.KZT') {
            if ($deposits_count >= 3) return true;
        }
        return parent::isHidden($currency_id, $deposits_count, $deposits_sum, $system);
    }

    public function getStatusCodes(): array
    {
        return [
            PayoutTransaction::STATUS_CROSSPAY_PENDING,
            PayoutTransaction::STATUS_CROSSPAY_SUCCESS,
            PayoutTransaction::STATUS_CROSSPAY_FAIL,
        ];
    }

    public function getFormRequisites($method, ?PaymentSystems $pay_method): string
    {
        $requisites['address1'] = $this->getLastRequisite('address1');
        $requisites['address2'] = $this->getLastRequisite('address2');
        $requisites['postcode'] = $this->getLastRequisite('postcode');
        $requisites['card_holder'] = $this->getLastRequisite('card_holder');

        if ($method == 'CrossPay.EUR') {
            return view('pay.requisites.payin.eur_crosspay', [
                'method' => $method,
                'm_user' => MUser::getAuth(),
                'requisites' => $requisites,
            ])->render();
        } elseif ($method == 'CrossPay.KZT_mobile') {
            return view('pay.requisites.payin.phone-mail', [
                'method' => $method,
                'm_user' => MUser::getAuth(),
            ])->render();

        } elseif ($method == 'CrossPay.UAH' || $method == 'CrossPay.KZT'
            || $method == 'CrossPay.UAH_trusted' || $method == 'CrossPay.KZT_trusted') {
            return view('pay.requisites.payin.crosspay_uah', [
                'method' => $method,
                'm_user' => MUser::getAuth(),
                'requisites' => $requisites,
            ])->render();
        }

        return parent::getFormRequisites($method, $pay_method);
    }
}
