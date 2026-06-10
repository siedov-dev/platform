<?php


namespace App\Payin;


use App\Models\PaymentSystems;
use App\MUser;
use App\PaymentsAPI\FreeKassa\FkApi;
use App\PayoutTransaction;
use App\Services\KPay\KPay;
use App\Services\KPay\PayInData;
use App\Services\KPay\UserStatsData;
use App\Wallet;

class FreeKassaPayin extends Payin
{
    protected $api;

    protected $methods = [
        'FK.WALLET_RUB' => 1,
        'FK.WALLET_USD' => 2,
        'FK.WALLET_EUR' => 3,
        'FK.VISA_RUB' => 4,
        'FK.Yoomoney' => 6,
        'FK.VISA_UAH' => 7,
        'FK.MasterCard_RUB' => 8,
        'FK.MasterCard_UAH' => 9,
        'FK.Qiwi' => 10,
        'FK.VISA EUR' => 11,
        'FK.Mir' => 12,
        'FK.online_bank' => 12,
        'FK.BTC' => 24,
        'FK.USDT' => 15,
        'FK.VISA_RUB2' => 36,
        'FK.VISA_MASTER_KZT' => 41,
    ];

    public function __construct(MUser $user)
    {
        parent::__construct($user);
        $this->name = "FreeKassa";
        $this->type = self::TYPE_FREEKASSA;
        $this->api = new FkApi();
    }

    public function canProceed(float $amount, string $method, array $data, int $method_id): bool
    {
        if (empty($data['email']) || !preg_match('/^([a-zA-Z0-9_\.-]+)@([a-zA-Z0-9_\.-]+)\.([a-zA-Z\.]{2,6})$/', $data['email'])) {
            $this->_error = __('wallet.email_required');
            return false;
        }
        return parent::canProceed($amount, $method, $data, $method_id);
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
//        $transaction->user_id = $this->user->id;
//        $transaction->hall_id = $this->user->hall_id;
//        $transaction->date = date("Y-m-d H:i:s");
//        $transaction->sum = $amount;
//        $transaction->currency =$this->user->getWallet()->getCurrencyName();
//        $transaction->operation = 'Пополнение счета через FreeKassa';
//        $transaction->status_id = PayoutTransaction::STATUS_FK_PENDING;
//        $transaction->save();
//
//        return ['url' => $this->api->createOrder($transaction->id, $amount, $this->currency_name, $this->methods[$method], $data['email'], $this->user->last_ip)['location']];
    }

    public function getFormRequisites($method, ?PaymentSystems $pay_method): string
    {
        return view('pay.requisites.payin.with-email', ['email' => $this->getLastRequisite('email')?? $this->user->email, 'method' => $method,]);
    }

    public function getStatusCodes(): array
    {
        return [
            PayoutTransaction::STATUS_FK_PENDING,
            PayoutTransaction::STATUS_FK_SUCCESS,
            PayoutTransaction::STATUS_FK_FAIL,
        ];
    }
}
