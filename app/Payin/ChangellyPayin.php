<?php

namespace App\Payin;

use App\Models\PaymentSystems;
use App\Models\UserCryptoWallet;
use App\MUser;
use App\PaymentsAPI\Changelly\Changelly;
use App\PaymentsAPI\ExPay\ExPay;
use App\PaymentsAPI\Passimpay\Passimpay;
use App\PayoutTransaction;
use Illuminate\Support\Facades\Auth;

class ChangellyPayin extends Payin
{
    public function __construct(MUser $user)
    {
        parent::__construct($user);
        $this->name = "Changelly";
        $this->type = self::TYPE_EXPAY;
    }

    public function process($amount, $method, array $data, ?PayoutTransaction $transaction, int $method_id)
    {
        $crypto_api = new Passimpay();

        $transaction = new PayoutTransaction();
        $user_id = Auth::id();
        /** @var MUser $user */
        $user = MUser::find($user_id);

        $currency = $this->user->currency;

        $transaction->user_id = $user->id;
        $transaction->hall_id = $user->hall_id;
        $transaction->date = date("Y-m-d H:i:s");
        $transaction->sum = 0;
        $transaction->currency = $user->getWallet($currency)->getCurrencyName();
        $transaction->operation = 'Пополнение счета через Changelly';
        $transaction->status_id = PayoutTransaction::STATUS_PASSIMPAY_PENDING;
        $transaction->save();

        $crypto_address = $crypto_api->getAddress($transaction->getId(), $crypto_api::USDT_TRC20);

        $method = explode('.', $method)[1];
        $fiat_api = new Changelly();

        $wallet_valid = $fiat_api->validateWallet('USDTRX', $crypto_address['address']?? 'noaddres');
        if (!$wallet_valid['result']) {
            $this->_error = 'Wrong wallet address';
            return false;
        }

        $result = $fiat_api->createOrder(order_id: time(), user_id: Auth::id(), provider: $method, currency: $data['currencyIn'], crypto_currency: 'USDTRX', amountFrom: $amount, wallet: $crypto_address['address']?? 'noaddres');
        if (!empty($result['errorMessage'])) {
            $this->_error = $result['errorMessage'];
            return false;
        }
        return ['url' => $result['redirectUrl']];
    }

    public function getFormRequisites($method, ?PaymentSystems $pay_method): string
    {

//        $cr_wallet = UserCryptoWallet::getForUser($this->user->getId(), $this->getName(), 'USDT');
//        $expay = new ExPay();
        $fiat_api = new Changelly();



//        if (empty($cr_wallet->getAddress())) {
//            $wallet_url = $expay->createCryptoWallet($this->user, 'USDTTRC');
//            if (empty($wallet_url['refer'])) throw new \Exception('Can`t get wallet address');
//            $cr_wallet->setAddress($wallet_url['refer']);
//        }

//        $wallet_valid = $fiat_api->validateWallet('USDTRX', $cr_wallet->getAddress());


        $currencies = $fiat_api->currencies('fiat', explode('.', $method)[1]);

        return view('pay.requisites.payin.changelly', ['currencies' => $currencies, 'method' => $method,]);
    }

    public function getStatusCodes(): array
    {
        return [
        ];
    }
}
