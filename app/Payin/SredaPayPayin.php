<?php

namespace App\Payin;

use App\Models\PaymentSystems;
use App\MUser;
use App\PaymentsAPI\SredaPay\SredaPay;
use App\PaymentsAPI\SredaPay\SredaPayVisa;
use App\PayoutTransaction;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Request;

class SredaPayPayin extends Payin
{
    protected SredaPay $api;

    public function __construct(MUser $user)
    {
        parent::__construct($user);
        $this->name = "SredaPay";
        $this->type = self::TYPE_SREDAPAY;
        $this->api  = new SredaPay();
    }

    public function canProceed(float $amount, string $method, array $data, int $method_id): bool
    {
        if (($method !== 'SredaPay.visa')) {
            $phone = preg_replace('#[^\d]#', '', $data['phone']);
            if (!preg_match('#^\d{11}$#', $phone)) {
                $this->_error = __('wallet.wrong_phone_length');
                return false;
            }
        }

        return parent::canProceed($amount, $method, $data, $method_id);
    }

    public function getRequestRequisites(string $method, array $data, int $method_id): array
    {
        if (isset($data['phone'])) {
            //clear all non-numeric characters
            $data['phone'] = preg_replace('#[^\d]#', '', $data['phone']);
        }
        return $data;
    }

    public function getFormRequisites($method, ?PaymentSystems $pay_method): string
    {
        if ($method === 'SredaPay.visa') return parent::getFormRequisites($method, $pay_method);

        $phone = $this->getLastRequisite('phone')?? $this->user->phone;

        $phone =  preg_replace('#[^+\d]#', '', $phone??'');

        return view('pay.requisites.payin.kz-phone', [
            'mobile' => $phone,
            'method' => $method,
        ]);
    }

    public function getStatusCodes(): array
    {
        return [
            PayoutTransaction::STATUS_SREDAPAY_PENDING,
            PayoutTransaction::STATUS_SREDAPAY_SUCCESS,
            PayoutTransaction::STATUS_SREDAPAY_FAIL,
        ];
    }


    public function process($amount, $method, array $data, ?PayoutTransaction $transaction, int $method_id)
    {
        $currency = $this->user->currency;
        Log::channel('pay')->info(__CLASS__."@".__METHOD__.' method'. $method);
        $otp_method = explode('.', $method)[1];
        $user_id = Auth::id();
        /** @var MUser $user */
        $user = MUser::find($user_id);

        if ($transaction === null) $transaction = new PayoutTransaction();
        $transaction->user_id = $user->id;
        $transaction->hall_id = $user->hall_id;
        $transaction->date = date("Y-m-d H:i:s");
        $transaction->sum = $amount;
        $transaction->currency = $user->getWallet($currency)->getCurrencyName();
        $transaction->operation = 'Пополнение счета через SredaPay';
        $transaction->status_id = PayoutTransaction::STATUS_SREDAPAY_PENDING;
        $transaction->save();

        if ($method === 'SredaPay.visa') {
            $api = new SredaPayVisa();
            $resp = $api->pay($amount, $transaction->id);
            if ($resp['success'] !== true) {
                $this->_error = 'request error';
                return false;
            }
            $result = json_decode(base64_decode($resp['data']), true);
            Log::channel('pay')->info('data:'. var_export($result, true));
            return ['url' => $result['url']?? $result['qr_url']];
        }

        $resp = $this->api->deposit($transaction->id, $data['phone'], $user->id, $amount, $otp_method);
        if ($resp['result'] != 0) {
            $this->_error = $resp['descr']?? 'uknown error';
            return false;
        }
        $transaction->coment = $resp['id'];
        $transaction->save();
        return ['modal_data' => view('pay.requisites.payin.sreda-otp-form',
            ['id' => $resp['id'], 'otp_method' => $otp_method])->render()];
    }

}
