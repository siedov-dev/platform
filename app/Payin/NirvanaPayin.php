<?php

namespace App\Payin;

use App\MUser;
use App\PaymentsAPI\NirvanaPay\NirvanaPay;
use App\PayoutTransaction;
use App\Services\KPay\KPay;
use App\Services\KPay\PayInData;
use App\Services\KPay\UserStatsData;
use App\Wallet;

class NirvanaPayin extends Payin
{

    protected NirvanaPay $api;

    public function __construct(MUser $user)
    {
        parent::__construct($user);
        $this->name = 'NirvanaPay';
        $this->type = self::TYPE_NIRVANA_PAY;
        $this->api = new NirvanaPay();
    }

    public function process($amount, $method, array $data, ?PayoutTransaction $transaction, int $method_id): bool|array
    {
        $dep_data = new PayInData();
        $dep_data->amount = $amount;
        $dep_data->currency = Wallet::currencyName($this->user->currency);
        $dep_data->user_id = $this->user->id;
        $dep_data->method_id = $method_id;
        $dep_data->bonus_id = $transaction->bonus_id?? 0; //todo: переделать
        $dep_data->extra_data = ['email' => $this->user->email];
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
//        $transaction->status_id = PayoutTransaction::STATUS_NIRVANA_PENDING;
//        $transaction->method = $method;
//        $transaction->save();
//        $token = explode('_',explode('.', $method)[1])[0];
//
//        $response = $this->api->deposit(
//            amount: $amount,
//            tr_id: $transaction->getId(),
//            user: $user,
//            token: $token,
//            currency: $user->getCurrencyName(),
//        );
//
//        if (!empty($response['data']['redirectURL'])) {
//
//            return ['url' => $response['data']['redirectURL']];
//        } else {
//            $this->_error = 'System error. Please try again later.';
//
//            $transaction->status_id = PayoutTransaction::STATUS_NIRVANA_FAIL;
//            $transaction->coment = "can't get url";
//            $transaction->save();
//
//            return false;
//        }

    }

    public function getStatusCodes(): array
    {
        return [
            PayoutTransaction::STATUS_NIRVANA_PENDING,
            PayoutTransaction::STATUS_NIRVANA_SUCCESS,
            PayoutTransaction::STATUS_NIRVANA_FAIL,
        ];
    }

}
