<?php

namespace App\Payin;

use App\Models\PaymentSystems;
use App\Models\UserMoneyStat;
use App\MUser;
use App\PaymentsAPI\Aifory\Aifory;
use App\PayoutTransaction;
use App\UserWallet;
use Illuminate\Support\Facades\Auth;

class AiforyPayin extends Payin
{
    public function __construct(MUser $user)
    {
        parent::__construct($user);
        $this->name = "Aifory";
        $this->type = self::TYPE_AIFORY;
    }

    public function process($amount, $method, array $data, ?PayoutTransaction $transaction, int $method_id)
    {
        if ($transaction === null) $transaction = new PayoutTransaction();

        $user = MUser::getAuth();

        $currency = $this->user->currency;

        $transaction->user_id = $user->id;
        $transaction->hall_id = $user->hall_id;
        $transaction->date = date("Y-m-d H:i:s");
        $transaction->sum = $amount;
        $transaction->currency = $user->getWallet($currency)->getCurrencyName();
        $transaction->operation = 'Пополнение счета через Aifory';
        $transaction->status_id = PayoutTransaction::STATUS_AIFORY_PENDING;
        $transaction->save();


        $api = new Aifory();

        if ($method === 'aifory.rub_untrusted') {
            $resp = $api->invoiceUntrusted($transaction->id, $amount, $currency);
        } else {
            if (str_starts_with($method, "aifory.p2p")) {
                $resp = $api->p2p($transaction->id, $amount, $currency);
            } elseif (str_starts_with($method, "aifory.m_") || str_starts_with($method, "Aifory.m_")) {
                $method_id = explode('_', $method)[1];
                $resp = $api->invoiceByMethodIds($transaction->id, $amount, $currency, [intval($method_id)]);
            } else {
                $resp = $api->invoice($transaction->id, $amount, $currency);
            }
        }

        if (empty($resp['status']) || $resp['status'] !== 'accepted') {
            $this->_error = $resp['failCause'] ?? 'system error';
            return false;
        }

        $transaction->o_tr_id = $resp['orderID'] ?? null;
        $transaction->save();

        return ['url' => $resp['paymentURL']];
    }

    public function getStatusCodes(): array
    {
        return [
            PayoutTransaction::STATUS_AIFORY_PENDING,
            PayoutTransaction::STATUS_AIFORY_SUCCESS,
            PayoutTransaction::STATUS_AIFORY_FAIL,
        ];
    }

//    public function isHidden(int $currency_id, int $deposits_count, float $deposits_sum, ?PaymentSystems $system = null): bool
//    {
//        if (str_starts_with($system->method_alias, "aifory.p2p")) return false;
//
//        $user = MUser::getAuth();
//
//        $stat = UserMoneyStat::getStat($this->user, $currency_id, UserMoneyStat::TYPE_AIFORY);
//
//        if ($stat === null) {
//            $stat = new UserMoneyStat();
//            $stat->setUserId($user->getId());
//            $stat->setCurrencyId($currency_id);
//            $stat->setType(UserMoneyStat::TYPE_AIFORY);
//
//            $deps = PayoutTransaction::selectRaw('user_id, sum(`sum`) as sum, count(id) as cnt, max(`date`) as last_dep, min(`date`) as first_dep')
//                ->where('user_id', $user->getId())
//                ->whereIn('status_id', PayoutTransaction::getIncomingStatuses())
//                ->where('currency', UserWallet::currencyName($currency_id))
//                ->where('status_id', PayoutTransaction::STATUS_AIFORY_SUCCESS)
//                ->groupBy('user_id')
//                ->first();
//
//            $stat->setDepositCount($deps?->cnt?? 0);
//            $stat->setDepositSum($deps?->sum?? 0);
//            $stat->first_dep = $deps?->first_dep;
//            $stat->last_dep =  $deps?->last_dep;
//
//            $payouts = PayoutTransaction::selectRaw('user_id, sum(`sum`) as sum, count(id) as cnt, max(`date`) as last_pay, min(`date`) as first_pay')
//                ->where('user_id', $user->getId())
//                ->whereIn('status_id', PayoutTransaction::getOutgoingStatuses())
//                ->where('currency', UserWallet::currencyName($currency_id))
//                ->where('operation', 'Автовыплата Aifory')
//                ->groupBy('user_id')
//                ->first();
//
//            $stat->setPayoutCount($payouts?->cnt?? 0);
//            $stat->setPayoutSum($payouts?->sum?? 0);
//            $stat->first_payout = $payouts?->first_pay;
//            $stat->last_payout = $payouts?->last_pay;
//            $stat->save();
//        }
//
//        $deps_count = $stat->getDepositCount();
//        $pays_count = $stat->getPayoutCount();
//
//        if ($system->method_alias === 'aifory.rub_untrusted') {
//            return !($deps_count < 3 && $pays_count < 1);
//        } else {
//            return $deps_count < 3 && $pays_count < 1;
//        }
//    }
}
