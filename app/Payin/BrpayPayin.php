<?php


namespace App\Payin;


use App\Models\PaymentSystems;
use App\Models\UserMoneyStat;
use App\MUser;
use App\PaymentsAPI\Brpay\Brpay;
use App\PayoutTransaction;
use App\Services\KPay\KPay;
use App\Services\KPay\PayInData;
use App\Services\KPay\UserStatsData;
use App\Wallet;

class BrpayPayin extends Payin
{
    protected $api;

    public function __construct(MUser $user)
    {
        parent::__construct($user);
        $this->name = "BlackRabbit";
        $this->type = self::TYPE_BRPAY;
        $this->api  = new Brpay($user->currency);
    }

    public function process($amount, $method, array $data, ?PayoutTransaction $transaction, int $method_id)
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

//        if ($transaction === null) $transaction = new PayoutTransaction();
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
//        $transaction->operation = 'Пополнение счета через BrPay';
//        $transaction->status_id = PayoutTransaction::STATUS_BRPAY_PENDING;
//        $transaction->save();
//
//        $resp = $this->api->initPayment($transaction->id, $user, $transaction->sum, $transaction->currency, $method, $data);
//        if (!empty($resp['sp_redirect_url'])) {
//            $transaction->o_tr_id = $resp['sp_payment_id']?? null;
//            $transaction->save();
//            return ['url' => $resp['sp_redirect_url']];
//        }
//        if ($resp === false || $this->api->is_error) {
//            $this->_error = $this->api->error_descr;
//            return false;
//        };
//        return false;
    }

    public function getFormRequisites($method, ?PaymentSystems $pay_method): string
    {
        if ($method === 'Brpay.cardinr') return parent::getFormRequisites($method, $pay_method);
        if ($method === 'Brpay.CARD_RUB_PW') return parent::getFormRequisites($method, $pay_method);
        if ($method === 'CARD_INR_QP') return parent::getFormRequisites($method, $pay_method);

        if ($method === 'UPI_INR_QP') {
            return view('pay.requisites.payin.BrPay-UPI', ['method' => $method])->render();
        }

        if ($method === 'NETBANK_INR_QP') {
            return view('pay.requisites.payin.BrPay-Bank', ['method' => $method])->render();
        }

//        if ($method === 'IkInr.Wallet') {
//            return view('pay.requisites.payin.IkInr-Wallet')->render();
//        }

        $wallet = $method;
        $mop = 101;
        switch ($wallet) {
            case 'paytm':
                $mop = 101;
                break;
            case 'MobiKwik':
                $mop = 102;
                break;
            case 'OlaMoney':
                $mop = 107;
                break;
            case 'Reliance':
                $mop = 106;
                break;
        }

        $field = '<input type="hidden" name="mop_type" value="' . $mop . '">'.parent::getFormRequisites($method, $pay_method);
        return $field;
    }

    public function getStatusCodes(): array
    {
        return [
            PayoutTransaction::STATUS_BRPAY_PENDING,
            PayoutTransaction::STATUS_BRPAY_SUCCESS,
            PayoutTransaction::STATUS_BRPAY_FAIL,
        ];
    }

    public function isHidden(int $currency_id, int $deposits_count, float $deposits_sum, ?PaymentSystems $system = null): bool
    {
        if ($system?->method_alias === 'Brpay.visa_trusted') {
            $user = $this->user;
            $po = UserMoneyStat::getStat($user, $currency_id, UserMoneyStat::TYPE_TOTAL);
            // user from partner registered more than month ago, played games,
            // has more than 5 deps with total amount > 30000 and has total payouts > 5000
            if (empty($user->click_id) || $user->created_at > now()->subMonth() || $user->getWallet($currency_id)->getTotalBet() < 1 ||  $po?->getDepositCount() < 5 || $po?->getDepositSum() < 30000 || $po?->getPayoutSum() < 5000) {
                return true;
            }
        }
//        if ($system?->method_alias === 'Brpay.CRYPTOPAY_RUB_P2P_GNP_AB_SBP' || $system?->method_alias === 'Brpay.CRYPTOPAY_RUB_P2P_GNP_AB_SBP-T') {
//            $user = $this->user;
//            $po = UserMoneyStat::getStat($user, $currency_id, UserMoneyStat::TYPE_TOTAL);
//            if ($po?->getDepositCount() < 1 ) {
//                return true;
//            }
//        }
        if ($system?->method_alias === 'Brpay.CRYPTOPAY_RUB_GNP_VKG_SBP') {
            $user = $this->user;
            $po = UserMoneyStat::getStat($user, $currency_id, UserMoneyStat::TYPE_TOTAL);
            if ($po?->getDepositCount() < 5 ) {
                return true;
            }
        }

        if ($system?->method_alias === 'Brpay.CRYPTOPAY_RUB_GNP_SBER_SBP') {
            $user = $this->user;
            $po = UserMoneyStat::getStat($user, $currency_id, UserMoneyStat::TYPE_TOTAL);
            if ($po?->getDepositCount() < 3 ) {
                return true;
            }
        }

        if ($system?->method_alias === 'BrPay.CARD_KZT_STD') {
            $user = $this->user;
            $po = UserMoneyStat::getStat($user, $currency_id, UserMoneyStat::TYPE_TOTAL);
            if ($user->created_at > now()->subMonth() || $po?->getDepositCount() < 5 || $po?->getPayoutCount() < 1 || $user->getWallet($currency_id)->getTotalBet() < 1) {
                return true;
            }
        }

        if ($system?->method_alias === 'BrPay.CARD_KZT_FTD') {
            $user = $this->user;
            $po = UserMoneyStat::getStat($user, $currency_id, UserMoneyStat::TYPE_TOTAL);
            if ($user->created_at < now()->subMonth() && $po?->getDepositCount() > 5 && $po?->getPayoutCount() >= 1 && $user->getWallet($currency_id)->getTotalBet() >= 1) {
                return true;
            }
        }

        return false;
    }

}
