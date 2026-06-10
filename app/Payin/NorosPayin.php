<?php

namespace App\Payin;

use App\MUser;
use App\PaymentsAPI\NirvanaPay\NirvanaPay;
use App\PayoutTransaction;
use App\Services\KPay\KPay;
use App\Services\KPay\PayInData;
use App\Services\KPay\UserStatsData;
use App\Wallet;

class NorosPayin extends Payin
{

    public function __construct(MUser $user)
    {
        parent::__construct($user);
        $this->name = 'Noros';
        $this->type = self::TYPE_NOROS;
    }

    public function process($amount, $method, array $data, ?PayoutTransaction $transaction, int $method_id): bool|array
    {
        $dep_data = new PayInData();
        $dep_data->amount = $amount;
        $dep_data->currency = Wallet::currencyName($this->user->currency);
        $dep_data->user_id = $this->user->id;
        $dep_data->method_id = $method_id;
        $dep_data->bonus_id = $transaction->bonus_id?? 0; //todo: переделать
        $dep_data->extra_data = $data;
        $dep_data->userStats = UserStatsData::getUserData($this->user->getId());
        $result = KPay::getInstance()->processDeposit($dep_data);
        if (!$result->isSuccess()) {
            $this->_error = $result->getError();
            return false;
        }
        return $result->getResult();
    }

    public function getStatusCodes(): array
    {
        return [
            PayoutTransaction::STATUS_NOROS_PENDING,
            PayoutTransaction::STATUS_NOROS_SUCCESS,
            PayoutTransaction::STATUS_NOROS_FAIL,
        ];
    }

}
