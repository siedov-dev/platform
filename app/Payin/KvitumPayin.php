<?php


namespace App\Payin;


use App\MUser;
use App\PayoutTransaction;
use App\Services\KPay\KPay;
use App\Services\KPay\PayInData;
use App\Services\KPay\UserStatsData;
use App\Wallet;

class KvitumPayin extends Payin
{

    public function __construct(MUser $user)
    {
        parent::__construct($user);
        $this->name = "Kvitum";
        $this->type = self::TYPE_KVITUM;
    }

    public function process($amount, $method, array $data, ?PayoutTransaction $transaction, int $method_id)
    {
        $user = $this->user;

        $dep_data = new PayInData();
        $dep_data->amount = $amount;
        $dep_data->currency = Wallet::currencyName($this->user->currency);
        $dep_data->user_id = $this->user->id;
        $dep_data->method_id = $method_id;
        $dep_data->bonus_id = $transaction->bonus_id?? 0; //todo: переделать
        $dep_data->extra_data = [
            'email' => $user->email,
        ];
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
            PayoutTransaction::STATUS_KVITUM_PENDING,
            PayoutTransaction::STATUS_KVITUM_SUCCESS,
            PayoutTransaction::STATUS_KVITUM_FAIL,
        ];
    }
}
