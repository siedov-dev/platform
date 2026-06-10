<?php

namespace App\Payin;

use App\Models\PaymentSystems;
use App\MUser;
use App\PaymentsAPI\Paycos\Paycos;
use App\PaymentsAPI\Paze\PazeApi;
use App\PayoutTransaction;
use App\UserWallet;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class PaycosPayin extends Payin
{
    protected Paycos $api;

    public function __construct(MUser $user)
    {
        parent::__construct($user);
        $this->name = "Paycos";
        $this->type = self::TYPE_PAYCOS;
        $this->api  = new Paycos();
    }

    public function canProceed(float $amount, string $method, array $data, int $method_id): bool
    {
        $currency = $this->user->currency;
        if ($currency == UserWallet::CURRENCY_INR && $method !== 'Paycos.inr_95') {
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
        $this->api  = new Paycos(UserWallet::currencyName($currency), $method);
        Log::channel('pay')->info(__CLASS__."@".__METHOD__.' method'. $method);
        $user_id = Auth::id();
        /** @var MUser $user */
        $user = MUser::find($user_id);

//        $data['phone'] = '91 '.$data['phone'];

        $transaction->user_id = $user->id;
        $transaction->hall_id = $user->hall_id;
        $transaction->date = date("Y-m-d H:i:s");
        $transaction->sum = $amount;
        $transaction->currency = $user->getWallet($currency)->getCurrencyName();
        $transaction->operation = 'Пополнение счета через Paycos';
        $transaction->status_id = PayoutTransaction::STATUS_PAYCOS_PENDING;
        $transaction->save();

        $resp = $this->api->initPay($transaction->id, $cents, $data, $transaction->currency, $method);

        if (preg_match('#^wallet_(.*)$#', $method)) $method = 'wallets';

        if (!empty($resp) && $resp['success'] === true && !empty($resp['payment_page_url']['total'])) {
            if ($method === 'ALL') {
                return ['url' => $resp['payment_page_url']['total']];
            } elseif ($method === 'inr_95') {
                return ['url' => $resp['payment_page_url']['methods']['o_pay']];
            } elseif($method === 'card') {
                return ['url' => $resp['payment_page_url']['methods']['q_'.$method]];
            } elseif($currency == UserWallet::CURRENCY_UAH) {
                return ['url' => $resp['payment_page_url']['methods']['ob_card'], 'in_frame' => 'true'];
            } elseif($currency == UserWallet::CURRENCY_RUB) {
                if ($method === 'ob_card2') $method = 'ob_card';
                if ($method === 'ob_card3' || $method === 'qr_tinkoff' || $method === 'qr_sber') {
                    return ['url' => $resp['payment_page_url']['methods']['ob_card']];
                }$h2h = $this->api->h2hConfirm($resp['host_to_host_url'][$method]);
                return ['url' => $h2h['processingUrl']];
            } elseif($currency == UserWallet::CURRENCY_KZT) {
                $h2h = $this->api->h2hConfirm($resp['host_to_host_url']['kzt']);

                if (isset($h2h['success']) && $h2h['success'] == true) {
                    return ['modal_data' => view('pay.requisites.payin.otp-form', ['url' => $resp['host_to_host_url']['kzt']])->render()];
                }
                $this->_error = 'system error';
                return false;

//                return ['url' => $h2h['processingUrl']];
            } else {
                $h2h = $this->api->h2hConfirm($resp['host_to_host_url']['q_'.$method]);
                return ['url' => $h2h['processingUrl']];
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

        if ($method === 'Paycos.netbanking') {
            return view('pay.requisites.payin.Paycos-Bank', [
                'email' => $email,
                'mobile' => $phone,
                'address' =>  $this->getLastRequisite('address'),
                'method' => $method,
                ]);
        }
        if ($method === 'Paycos.upi') {
            return view('pay.requisites.payin.Paycos-upi', [
                'email' => $email,
                'mobile' => $phone,
                'address' =>  $this->getLastRequisite('address'),
                'payer_address' =>  $this->getLastRequisite('payer_address'),
                'method' => $method,
            ]);
        }
        if ($method === 'Paycos.wallets') {
            return view('pay.requisites.payin.Paycos-Wallets', [
                'email' => $email,
                'mobile' => $phone,
                'address' =>  $this->getLastRequisite('address'),
                'method' => $method,
            ]);
        }
        if (preg_match('#^Paycos.wallet_(.*)$#', $method, $mop)) {
            return view('pay.requisites.payin.Paycos-Wall', [
                'email' => $email,
                'mobile' => $phone,
                'address' =>  $this->getLastRequisite('address'),
                'mop' => $mop[1],
                'method' => $method,
            ]);
        }
        if ($method === 'Paycos.kz_mobile') {
            return view('pay.requisites.payin.with-phone', [
                'mobile' => $phone,
                'method' => $method,
            ]);
        }
        return parent::getFormRequisites($method, $pay_method);
    }

    public function getStatusCodes(): array
    {
        return [
            PayoutTransaction::STATUS_PAYCOS_PENDING,
            PayoutTransaction::STATUS_PAYCOS_SUCCESS,
            PayoutTransaction::STATUS_PAYCOS_FAIL,
        ];
    }
}
