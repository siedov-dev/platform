<?php

namespace App\Payin;

use App\Models\PaymentSystems;
use App\Models\UserMoneyStat;
use App\MUser;
use App\PaymentsAPI\Betatransfer\Betatransfer;
use App\PayoutTransaction;
use App\Services\KPay\KPay;
use App\Services\KPay\PayInData;
use App\Services\KPay\UserStatsData;
use App\Wallet;
use Illuminate\Support\Facades\Auth;

class BetatransferPayin extends Payin
{
    public function __construct(MUser $user)
    {
        parent::__construct($user);
        $this->name = "Betatransfer";
        $this->type = self::TYPE_BETATRANSFER;
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

//        if ($transaction === null) $transaction = new PayoutTransaction();
//
//        $user_id = Auth::id();
//        /** @var MUser $user */
//        $user = MUser::find($user_id);
//
//        $currency = $this->user->currency;
//
//        $transaction->user_id = $user->id;
//        $transaction->hall_id = $user->hall_id;
//        $transaction->date = date("Y-m-d H:i:s");
//        $transaction->sum = $amount;
//        $transaction->currency = $user->getWallet($currency)->getCurrencyName();
//        $transaction->operation = 'Пополнение счета через Betatransfer';
//        $transaction->status_id = PayoutTransaction::STATUS_BETATRANSFER_PENDING;
//        $transaction->save();
//
//        $api = new Betatransfer();
//        $method = explode('.', $method)[1];
//        $resp = $api->deposit($amount, $currency, $transaction->id, $method);
//        if (empty($resp['urlPayment'])) {
//            $this->_error = $resp['message']?? 'system error';
//            return false;
//        }
//
//        $transaction->o_tr_id = $resp['orderId']?? $resp['id']?? null;
//        $transaction->save();
//
//        return ['url' => $resp['urlPayment']];
    }

    public function getStatusCodes(): array
    {
        return [
            PayoutTransaction::STATUS_BETATRANSFER_PENDING,
            PayoutTransaction::STATUS_BETATRANSFER_SUCCESS,
            PayoutTransaction::STATUS_BETATRANSFER_FAIL,
        ];
    }

    public function isHidden(int $currency_id, int $deposits_count, float $deposits_sum, ?PaymentSystems $system = null): bool
    {
        $po = UserMoneyStat::getStat(MUser::getAuth(), $currency_id, UserMoneyStat::TYPE_TOTAL);
        if ($system?->method_alias === 'Betatransfer.Card6') {
            if ($deposits_count >= 5) return true;
            if ($deposits_count >= 3 && ($po?->getPayoutCount()?? 0) > 0) return true;
            return false;
        }
        if ($system?->method_alias === 'Betatransfer.Card8') {
            if ($deposits_count >= 5) return false;
            if ($deposits_count >= 3 && ($po?->getPayoutCount()?? 0) > 0) return false;
            return true;
        }
        if ($system?->method_alias === 'Betatransfer.Card3') {
            if ($deposits_count >= 5) return true;
//            if ($deposits_count >= 3 && ($po?->getPayoutCount()?? 0) > 0) return true;
            return false;
        }
        if ($system?->method_alias === 'Betatransfer.Card2') {
            if ($deposits_count >= 5) return false;
//            if ($deposits_count >= 3 && ($po?->getPayoutCount()?? 0) > 0) return false;
            return true;
        }
        return false;
    }
}
