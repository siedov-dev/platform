<?php

namespace App\Payin;

use App\Models\PaymentMethodData;
use App\Models\PaymentSystems;
use App\Models\UserMoneyStat;
use App\MUser;
use App\PaymentsAPI\HighHelp\HighHelp;
use App\PayoutTransaction;
use App\Services\KPay\KPay;
use App\Services\KPay\PayInData;
use App\Services\KPay\UserStatsData;
use App\Wallet;

class HighHelpPayin extends Payin
{

    public function __construct(MUser $user)
    {
        parent::__construct($user);
        $this->name = 'HighHelp';
        $this->type = self::TYPE_HIGHHELP;
    }

    public function process($amount, $method, array $data, ?PayoutTransaction $transaction, int $method_id): bool|array
    {
        $dep_data = new PayInData();
        $dep_data->amount = $amount;
        $dep_data->currency = Wallet::currencyName($this->user->currency);
        $dep_data->user_id = $this->user->id;
        $dep_data->method_id = $method_id;
        $dep_data->bonus_id = $transaction->bonus_id?? 0; //todo: переделать
        $dep_data->extra_data = [...$data, ...['email' => $this->user->email]];
        $dep_data->userStats = UserStatsData::getUserData($this->user->getId());
        $result = KPay::getInstance()->processDeposit($dep_data);
        if (!$result->isSuccess()) {
            $this->_error = $result->getError();
            return false;
        }
        return $result->getResult();
//        if ($transaction === null) $transaction = new PayoutTransaction;
//
//        $user = MUser::getAuth();
//
//        $transaction->user_id = $user->id;
//        $transaction->hall_id = $user->hall_id;
//        $transaction->date = date(format: 'Y-m-d H:i:s');
//        $transaction->sum = $amount;
//        $transaction->currency = $user->getCurrencyName();
//        $transaction->operation = class_basename(Payin::class) . ' ' . $this->name;
//        $transaction->status_id = PayoutTransaction::STATUS_HIGHHELP_PENDING;
//        $transaction->method = $method;
//        $transaction->save();
//        $pmethod = explode('.', $method)[1];
//        $pay_type = explode('-', $pmethod)[1]?? $pmethod;
//        $widget_method = explode(':', $pmethod)[0]?? $pmethod;
//        /** @var PaymentSystems $psystem */
//        $psystem = PaymentSystems::find($method_id);
//        $bank_alias = $psystem->bank_alias?? '';
//
//        $project_id = PaymentMethodData::getMethodValue($method_id, 'project_id');
//        $api = new HighHelp($project_id);
//
//        $traffic = "ftd";
//        $po = UserMoneyStat::getStat($user, $user->getCurrency(), UserMoneyStat::TYPE_TOTAL);
//        if ($po?->getDepositCount() >= 3) $traffic = "trust";
//
//        if ($pay_type === 'ecom') {
//            $response = $api->depositEcom(
//                amount: $amount,
//                tr_id: $transaction->id,
//                method: $widget_method,
//                user: $user,
//                currency: $user->getCurrencyName(),
//            );
//        } else {
//            $response = $api->depositP2p(
//                amount: $amount,
//                tr_id: $transaction->id,
//                method: $widget_method,
//                user: $user,
//                currency: $user->getCurrencyName(),
//                traffic: $traffic,
//                bank: $bank_alias,
//            );
//        }
//
//        if (empty($response['status']) || $response['status'] !== 'processing') {
//            $this->_error = $response['error_msg']?? 'System error. Please try again later.';
//            $transaction->status_id = PayoutTransaction::STATUS_HIGHHELP_FAIL;
//            $transaction->coment = $response['error_msg']?? '';
//            $transaction->save();
//        }
//
//        if (!empty($response['integration']['form_url'])) {
//            $transaction->o_tr_id = $response['request_id']?? "";
//            $transaction->save();
//            return ['url' => $response['integration']['form_url']];
//        } else {
//            $this->_error = $response['error_msg']?? 'System error. Please try again later.';
//
//            $transaction->status_id = PayoutTransaction::STATUS_HIGHHELP_FAIL;
//            $transaction->coment = $response['error_msg']?? "can't get url";
//            $transaction->save();
//
//            return false;
//        }

    }

    public function isHidden(int $currency_id, int $deposits_count, float $deposits_sum, ?PaymentSystems $system = null): bool
    {
        if ($system->method_alias === "HighHelp.payin-p2p-mobile" && $deposits_count < 3) return true;
        if ($system->method_alias === "HighHelp.payin-p2p-mobile:1-click" && $deposits_count >= 3) return true;
        return parent::isHidden($currency_id, $deposits_count, $deposits_sum, $system);
    }

    public function getStatusCodes(): array
    {
        return [
            PayoutTransaction::STATUS_HIGHHELP_PENDING,
            PayoutTransaction::STATUS_HIGHHELP_SUCCESS,
            PayoutTransaction::STATUS_HIGHHELP_FAIL,
        ];
    }

}
