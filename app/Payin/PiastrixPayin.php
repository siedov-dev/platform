<?php


namespace App\Payin;


use App\Models\PaySystemsConfig;
use App\MUser;
use App\PaymentsAPI\Piastrix;
use App\PayoutTransaction;
use App\Services\KPay\KPay;
use App\Services\KPay\PayInData;
use App\Services\KPay\UserStatsData;
use App\UserWallet;
use App\Wallet;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;

class PiastrixPayin extends Payin
{
//    protected int $shop_id = 2509;
//    protected string $key = 'P3wOB1pwqXuUrY8VPC';

    public function __construct(MUser $user)
    {
        parent::__construct($user);
        $this->name = "Piastrix";
        $this->type = self::TYPE_PIASTRIX;

//        $conf = PaySystemsConfig::getSystemConfig(self::TYPE_PIASTRIX);
//        $this->shop_id = $conf['shop_id'] ?? 2509;
//        $this->key = $conf['key'] ?? 'P3wOB1pwqXuUrY8VPC';
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
//        $dep_data->extra_data = [
//            'email' => $user->email,
//        ];
        $dep_data->userStats = UserStatsData::getUserData($this->user->getId());
        $result = KPay::getInstance()->processDeposit($dep_data);
        if (!$result->isSuccess()) {
            $this->_error = $result->getError();
            return false;
        }
        return $result->getResult();
//        if ($transaction === null) $transaction = new PayoutTransaction();
//        $user = $this->user;
//
//        $currency = $user->currency;
//
//        $transaction->user_id = $user->id;
//        $transaction->hall_id = $user->hall_id;
//        $transaction->date = date("Y-m-d H:i:s");
//        $transaction->sum = $amount;
//        $transaction->currency = $user->getWallet($currency)->getCurrencyName();
//        $transaction->operation = 'Пополнение счета через Piastrix';
//        $transaction->status_id = PayoutTransaction::STATUS_PIASTRIX_PENDING;
//        $transaction->save();
//
//        $currency_code = $user->getWallet($currency)->getCurrencyIntCode();
//
//        $piastrix = $this->getPaySystem();
//
////        switch ($currency_code) {
////            case 643:
////                $payway = 'card_rub';
////                break;
////            case 840:
////                $payway = 'card_usd';
////                break;
////            case 974:
////                $payway = 'card_eur';
////                break;
////            case 980:
////                $payway = 'card_uah';
////                break;
////            default:
////                $payway = 'card_rub';
////                break;
////        }
//
//        $currency = '' . $currency_code;
//
//        $data = [];
//        $data['success_url'] = action('PayController@success');
//        $data['fail_url'] = action('PayController@fail');
//
//        if ($method !== 'all') {
//            $data['payway'] = $method;
//        }
//
//        $resp = $piastrix->getInvoiceUrl($amount, $currency, $transaction->id, App::getLocale(), $data);
//        return ['url' => $resp];
    }
//
//    protected function getPaySystem()
//    {
//        return new Piastrix([
//            'shop_id' => $this->shop_id,
//            'key' => $this->key,
//        ]);
//    }

    public function getStatusCodes(): array
    {
        return [
            PayoutTransaction::STATUS_PIASTRIX_PENDING,
            PayoutTransaction::STATUS_PIASTRIX_SUCCESS,
            PayoutTransaction::STATUS_PIASTRIX_ERROR,
        ];
    }
}
