<?php

namespace App\Payin;

use App\Models\PaymentSystems;
use App\MUser;
use App\PaymentsAPI\AezaPay\AezaPay;
use App\PayoutTransaction;
use App\UserWallet;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class AezaPayin extends Payin
{
    protected AezaPay $api;

    public function __construct(MUser $user)
    {
        parent::__construct($user);
        $this->name = "AezaPay";
        $this->type = self::TYPE_AEZA;
        $this->api  = new AezaPay();
    }

    public function canProceed(float $amount, string $method, array $data, int $method_id): bool
    {
        $currency = $this->user->currency;
        if ($currency == UserWallet::CURRENCY_INR) {
            if (empty($data['email']) || !preg_match('/^([a-zA-Z0-9_\.-]+)@([a-zA-Z0-9_\.-]+)\.([a-zA-Z\.]{2,6})$/', $data['email'])) {
                $this->_error = __('wallet.email_required');
                return false;
            }

            if (!preg_match('#^\d{10}$#', $data['phone'])) {
                $this->_error = __('wallet.wrong_10_phone');
                return false;
            }

            if (isset($data['address']) && !preg_match('#^.{5,}$#', $data['address'])) {
                $this->_error = "Address should have at least 5 characters";
                return false;
            }
        }

        return parent::canProceed($amount, $method, $data, $method_id);
    }

    public function process($amount, $method, array $data, ?PayoutTransaction $transaction, int $method_id)
    {
        if ($transaction === null) $transaction = new PayoutTransaction();
        $cents = floor($amount * 100);
        $method = explode('.', $method)[1];
        $currency = $this->user->currency;
        $this->api  = new AezaPay();
        Log::channel('pay')->info(__CLASS__."@".__METHOD__.' method'. $method);
        $user_id = Auth::id();
        /** @var MUser $user */
        $user = MUser::find($user_id);

        $transaction->user_id = $user->id;
        $transaction->hall_id = $user->hall_id;
        $transaction->date = date("Y-m-d H:i:s");
        $transaction->sum = $amount;
        $transaction->currency = $user->getWallet($currency)->getCurrencyName();
        $transaction->operation = 'Пополнение счета через Aeza';
        $transaction->status_id = PayoutTransaction::STATUS_AEZA_PENDING;
        $transaction->save();

        $resp = $this->api->initPay($transaction->id, $cents, $data, $transaction->currency, $method);

        if (!empty($resp) && $resp['success'] === true && !empty($resp['payment_page_url']['total'])) {
            if ($method === 'all') {
                return ['url' => $resp['payment_page_url']['total']];
            } else {
                return ['url' => $resp['payment_page_url']['methods'][$method]];
//                $h2h = $this->api->h2hConfirm($resp['host_to_host_url'][$method]);
//                return ['url' => $h2h['processingUrl']];
            }
        }

        $this->_error = $resp['error']??'system error';
        return false;
    }

    public function getRequestRequisites(string $method, array $data, int $method_id): array
    {
        if (isset($data['phone'])) {
            //clear all non-numeric characters
            $data['phone'] = preg_replace('#[^+\d]#', '', $data['phone']);

            if (preg_match('#^(\+)?(91)?(\d{10})$#', $data['phone'], $match)) {
                $data['phone'] = $match[3];
            } elseif (preg_match('#^(\+)?(\d*)$#', $data['phone'], $match)) {
                $data['phone'] = $match[2];
            }
        }
        return $data;
    }

    public function getFormRequisites($method, ?PaymentSystems $pay_method): string
    {
        $email = $this->getLastRequisite('email')?? $this->user->email;
        $phone = $this->getLastRequisite('phone')?? $this->user->phone;

        $phone =  preg_replace('#[^+\d]#', '', $phone??'');
        if (preg_match('#^(\+?)(91)?(\d{10})$#', $phone, $match)) {
            $phone = $match[3];
        }
        return parent::getFormRequisites($method, $pay_method);
    }

    public function getStatusCodes(): array
    {
        return [
            PayoutTransaction::STATUS_AEZA_PENDING,
            PayoutTransaction::STATUS_AEZA_SUCCESS,
            PayoutTransaction::STATUS_AEZA_FAIL,
        ];
    }
}
