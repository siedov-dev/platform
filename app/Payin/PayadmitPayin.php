<?php

namespace App\Payin;

use App\Models\PaymentSystems;
use App\Models\UserMoneyStat;
use App\MUser;
use App\PaymentsAPI\Payadmit\Payadmit;
use App\PayoutTransaction;
use App\Wallet;
use Illuminate\Support\Facades\Auth;

class PayadmitPayin extends Payin
{
    public function __construct(MUser $user)
    {
        parent::__construct($user);
        $this->name = "Payadmit";
        $this->type = self::TYPE_PAYADMIT;
    }

    public function process($amount, $method, array $data, ?PayoutTransaction $transaction, int $method_id)
    {
        if ($transaction === null) $transaction = new PayoutTransaction();
        $user_id = Auth::id();
        /** @var MUser $user */
        $user = MUser::find($user_id);

        $currency = $this->user->currency;
        $curr_name = Wallet::currencyName($currency);

        $transaction->user_id = $user->id;
        $transaction->hall_id = $user->hall_id;
        $transaction->date = date("Y-m-d H:i:s");
        $transaction->sum = $amount;
        $transaction->currency = $user->getWallet($currency)->getCurrencyName();
        $transaction->operation = 'Пополнение счета через Payadmit';
        $transaction->status_id = PayoutTransaction::STATUS_PAYADMIT_PENDING;
        $transaction->save();
        $country_code = "";

        $api = new Payadmit($method_id);

        $full_method_name = explode('.', $method)[1];
        $method_prefix = explode(':', $full_method_name)[0]?? '';
        $pay_admit_method = explode(':', $full_method_name)[1]?? "BASIC_CARD";
        if ($transaction->currency === "KZT") $country_code = "KZ";

//        if (str_starts_with($method_prefix, "mobile")) {
//            $resp = $api->depositMobile($transaction->id, $amount, $data, $curr_name, $pay_admit_method);
//        } else {
        $resp = $api->deposit($transaction->id, $amount, $data, $curr_name, $pay_admit_method, $country_code);
//        }

        if (!empty($resp['error'])) {
            $this->_error = $resp['message'];
            return false;
        }
        return ['url' => $resp['result']['redirectUrl']?? ''];
    }

    public function getRequestRequisites(string $method, array $data, int $method_id): array
    {
        if (isset($data['phone'])) {
            //clear all non-numeric characters
            $data['phone'] = preg_replace('#\D#', '', $data['phone']);
            $data['phone'] = substr($data['phone'], 0, 3).' '.substr($data['phone'], 3);
        }
        return $data;
    }

    public function getFormRequisites($method, ?PaymentSystems $pay_method): string
    {
        $email = $this->getLastRequisite('email')?? $this->user->email;
        $phone = $this->getLastRequisite('phone')?? $this->user->phone;
//        $first_name = $this->getLastRequisite('first_name')?? $this->user->phone;
//        $last_name = $this->getLastRequisite('last_name')?? $this->user->phone;

        return view('pay.requisites.payin.phone-mail', [
            'method' => $method,
            'email' => $email,
            'phone' => $phone,
        ])->render();
    }

    public function isHidden(int $currency_id, int $deposits_count, float $deposits_sum, ?PaymentSystems $system = null): bool
    {
        $full_method_name = explode('.', $system->method_alias)[1];
        $method_prefix = explode(':', $full_method_name)[0]?? '';
        if (str_starts_with($method_prefix, "mobile")) {
            $user = MUser::getAuth();
            $stat = UserMoneyStat::getStat($user, $currency_id, UserMoneyStat::TYPE_PAYADMIT_MOBILE);
            if (str_ends_with($method_prefix, "trd") && ($stat?->getDepositCount()?? 0) < 3) {
                return true;
            } elseif (str_ends_with($method_prefix, "ftd") && ($stat?->getDepositCount()?? 0) >= 3) {
                return true;
            }
        } else {
            $user = MUser::getAuth();
            $stat = UserMoneyStat::getStat($user, $currency_id, UserMoneyStat::TYPE_PAYADMIT);
            if (str_ends_with($method_prefix, "trd") && ($stat?->getDepositCount()?? 0) < 3) {
                return true;
            } elseif ((str_ends_with($method_prefix, "ftd") || str_ends_with($method_prefix, "kzt")) && ($stat?->getDepositCount()?? 0) >= 3) {
                return true;
            }
        }
        return parent::isHidden($currency_id, $deposits_count, $deposits_sum, $system);
    }

    public function getStatusCodes(): array
    {
        return [
            PayoutTransaction::STATUS_PAYADMIT_PENDING,
            PayoutTransaction::STATUS_PAYADMIT_SUCCESS,
            PayoutTransaction::STATUS_PAYADMIT_FAIL,
        ];
    }
}
