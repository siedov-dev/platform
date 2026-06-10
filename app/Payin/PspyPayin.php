<?php

namespace App\Payin;

use App\Models\PaymentSystems;
use App\Models\UserMoneyStat;
use App\MUser;
use App\PaymentsAPI\Pspy\PspyApi;
use App\PayoutTransaction;
use App\Services\KPay\KPay;
use App\Services\KPay\PayInData;
use App\Services\KPay\UserStatsData;
use App\Wallet;
use Illuminate\Support\Facades\Auth;

class PspyPayin extends Payin
{
    public function __construct(MUser $user)
    {
        parent::__construct($user);
        $this->name = "Pspy";
        $this->type = self::TYPE_PSPY;
    }

    public function process($amount, $method, array $data, ?PayoutTransaction $transaction, int $method_id)
    {
        $dep_data = new PayInData();
        $dep_data->amount = $amount;
        $dep_data->currency = Wallet::currencyName($this->user->currency);
        $dep_data->user_id = $this->user->id;
        $dep_data->method_id = $method_id;
        $dep_data->extra_data = $data;
        $dep_data->userStats = UserStatsData::getUserData($this->user->getId());
        $dep_data->bonus_id = $transaction->bonus_id?? 0; //todo: переделать
        $result = KPay::getInstance()->processDeposit($dep_data);
        if (!$result->isSuccess()) {
            $this->_error = $result->getError();
            return false;
        }
        return $result->getResult();
        if ($transaction === null) $transaction = new PayoutTransaction();

        $user_id = Auth::id();
        /** @var MUser $user */
        $user = MUser::find($user_id);

        $currency = $this->user->currency;

        $transaction->user_id = $user->id;
        $transaction->hall_id = $user->hall_id;
        $transaction->date = date("Y-m-d H:i:s");
        $transaction->sum = $amount;
        $transaction->currency = $user->getWallet($currency)->getCurrencyName();
        $transaction->operation = 'Пополнение счета через Pspy';
        $transaction->status_id = PayoutTransaction::STATUS_PSPY_PENDING;
        $transaction->save();

        $api = new PspyApi();
        $method = explode('.', $method)[1];

        $resp = $api->deposit($transaction, $amount);
        if (($resp['status']??0) !== 200 || empty($resp['result']['redirectUrl'])) {
            $this->_error = $resp['error']?? 'system error';
            return false;
        }

        $transaction->o_tr_id = $resp['result']['id']??'';
        $transaction->save();
        return ['url' => $resp['result']['redirectUrl']];
    }

    public function getStatusCodes(): array
    {
        return [
            PayoutTransaction::STATUS_PSPY_PENDING,
            PayoutTransaction::STATUS_PSPY_SUCCESS,
            PayoutTransaction::STATUS_PSPY_FAIL,
        ];
    }

    public function isHidden(int $currency_id, int $deposits_count, float $deposits_sum, ?PaymentSystems $system = null): bool
    {
        $stats = UserMoneyStat::getStat(MUser::getAuth(), $currency_id, UserMoneyStat::TYPE_TOTAL);
        return $stats?->getDepositCount() < 3 || $stats?->getPayoutCount() < 1;
    }
}
