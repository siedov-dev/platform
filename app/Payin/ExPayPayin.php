<?php

namespace App\Payin;

use App\Models\PaymentSystemGroup;
use App\Models\PaymentSystems;
use App\Models\UserCryptoWallet;
use App\MUser;
use App\PaymentsAPI\ExPay\ExPay;
use App\PayoutTransaction;
use App\Services\KPay\KPay;
use App\Services\KPay\PayInData;
use App\Services\KPay\UserStatsData;
use App\UserWallet;
use App\Wallet;
use Illuminate\Support\Facades\Auth;

class ExPayPayin extends Payin
{

    private ExPay $api;

    public function __construct(MUser $user)
    {
        parent::__construct($user);
        $this->name = "ExPay";
        $this->type = self::TYPE_EXPAY;
    }

    public function process($amount, $method, array $data, ?PayoutTransaction $transaction, int $method_id)
    {
        $pay_method = explode('.', $method)[1];
        [$token, $sub_token] = explode('_', $pay_method);

        /** @var MUser $user */
        $user = MUser::find(Auth::id());
//        $ps = PaymentSystems::where('type', 0)->where('method_alias', $method)->firstOrFail();
        $ps = PaymentSystems::find($method_id);
        if ($ps === null) {
            $this->_error = 'Payment system not found';
            return false;
        }

        if ($this->user->currency === UserWallet::CURRENCY_USDT) {

            $fiat_currency = $user->getWallet($user->currency)->getCurrencyName();
            $wallet = $this->getWallet(currency: 'USDT', fiat_currency: $fiat_currency);

            return [
                'modal_data' => view('pay.requisites.payin.expay.usdt', [
                    'wallet' => $wallet?->address ?? 'wallet error',
                    'method' => $pay_method,
                    'currency' => 'USDT TRC20',
                    'amount' => $amount,
                ])->render()
            ];

        } elseif ($this->user->currency === UserWallet::CURRENCY_RUB && in_array($method, ['USDTBSC', 'USDTERC'])) {

            $fiat_currency = $user->getWallet($user->currency)->getCurrencyName();
            $wallet = $this->getWallet(currency: 'USDT', fiat_currency: $fiat_currency);

            if (empty($wallet->address)) {
                $this->_error = $wallet->error ?? 'wallet address error';
                return false;
            }

            return [
                'modal_data' => view('pay.requisites.payin.expay.usdt', [
                    'wallet' => $wallet->address,
                    'method' => $pay_method,
                    'currency' => $pay_method,
                    'amount' => $amount,

                ])->render()
            ];

        } elseif ($this->user->currency === UserWallet::CURRENCY_RUB && in_array($pay_method, ['BTC', 'USDTTRC'])) {


            $fiat_currency = $user->getWallet($user->currency)->getCurrencyName();
            $wallet = $this->getWallet(currency: $pay_method, fiat_currency: $fiat_currency);

            $exchange_rate = KPay::getInstance()->cryptoCourseGet($this->type, $token, $sub_token);
            if (!$exchange_rate->isSuccess()) return $exchange_rate->getError();
            if ($exchange_rate->getResult() === 0) return 'rate error';

            $pre_price = round($amount / $exchange_rate->getResult(), 4);

            return [
                'modal_data' => view('pay.requisites.payin.expay.crypto', [
                    'wallet' => $wallet,
                    'method' => $pay_method,
                    'currency' => $pay_method,
                    'amount' => $amount,
                    'pre_price' => $pre_price,
                    'ps' => $ps
                ])->render()
            ];

        }

        $user = $this->user;

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
//
//        $currency = $this->user->currency;
//
//        $transaction->user_id = $user->id;
//        $transaction->hall_id = $user->hall_id;
//        $transaction->date = date("Y-m-d H:i:s");
//        $transaction->sum = $amount;
//        $transaction->currency = $user->getWallet($currency)->getCurrencyName();
//        $transaction->operation = 'Пополнение счета через ExPay';
//        $transaction->status_id = PayoutTransaction::STATUS_EXPAY_PENDING;
//        $transaction->system_id = PayIn::TYPE_EXPAY;
//        $transaction->save();
//
////        if ($pay_method == 'RUBCISP2P_' || $pay_method == 'CARDRUBP2P_SBERRUB' || $pay_method == 'CARDRUBP2P_SBPRUB' || $pay_method == 'CARDRUBP2P_ANRUB') {
//        if ($pay_method == 'RUBCISP2P_') $token = 'CARDRUBP2P';
//
//        $api = new ExPay();
//        $resp = $api->p2pFiat(transaction: $transaction, amount: $amount, token: $token, sub_token: $sub_token);
//        if (empty($resp['alter_refer'])) {
//            $this->_error = $resp['description'] ?? 'System error';
//            return false;
//        }
//        $transaction->o_tr_id = $resp['tracker_id'];
//        $transaction->save();
//        return ['url' => $resp['alter_refer']];
    }

    private function getWallet(string $currency, string $fiat_currency)
    {
        try {
            $wallet = UserCryptoWallet::getForUser($this->user->id, $this->name, PaymentSystems::TYPE_PAYIN, $currency, $fiat_currency);
        } catch (\Exception $e) {
            $this->_error = $e->getMessage();
            return false;
        }

//        $wallet = $this->user->crypto_wallet(
//            system_code: $this->name,
//            payment_type: PaymentSystems::TYPE_PAYIN,
//            currency: $currency,
//            fiat_currency: $fiat_currency,
//        )->first();
//
//        if ($wallet === null) {
//            $wallet = new UserCryptoWallet();
//            $wallet->user_id = $this->user->id;
//            $wallet->currency = $currency;
//            $wallet->fiat_currency = $fiat_currency;
//            $wallet->system_id = $this->name;
//            $wallet->address = '';
//            $wallet->save();
//        }

        if (empty($wallet->getAddress())) {

            $client_transaction_id = 'expsa:' . $this->user->id . ':' . $currency . ':' . $fiat_currency . ':' . time();

            $wallet_api = $this->api->createCryptoWallet(
                token: $currency,
                fiat_currency: $fiat_currency,
                client_transaction_id: $client_transaction_id,
            );

            if (!isset($wallet_api->receiver)) {
                $this->_error = $wallet_api->description ?? 'can`t get wallet address';
                return false;
                //throw new \Exception($this->_error);
            }

            $wallet->setAddress($wallet_api->receiver);
            $wallet->service_user_id = $client_transaction_id;
            $wallet->service_outer_id = $wallet_api->tracker_id;
            $wallet->save();
        }

        return $wallet;
    }

    public function getFormRequisites($method, ?PaymentSystems $pay_method): string
    {
        if ($pay_method->system_group == PaymentSystemGroup::Crypto) {
            $dep_data = new PayInData();
            $dep_data->amount = 0;
            $dep_data->currency = Wallet::currencyName($this->user->currency);
            $dep_data->user_id = $this->user->id;
            $dep_data->method_id = $pay_method->id;
            $dep_data->userStats = UserStatsData::getUserData($this->user->getId());

            $address = KPay::getInstance()->cryptoGetDepositAddress($dep_data, $pay_method->id);

            if (!$address->isSuccess()) {
                $this->_error = $address->getError();
                return false;
            }

            $method_ = explode('.', $method)[1];
            if (str_contains($method_, '_')) {
                [$token, $sub_token] = explode('_', $method_);
            } else {
                $token = $method_;
                $sub_token = Wallet::currencyName($pay_method->currency_id);
            }
//
//            $wallet = $this->getWallet(currency: $token, fiat_currency: $sub_token);
//
//            if (empty($wallet)) return 'wallet address error';

//            $exchange_rate = $this->api->course(from: $token, to: $sub_token);
//            if (empty($exchange_rate[0]) || empty($exchange_rate[0]->price)) return 'rate error';
            $exchange_rate = KPay::getInstance()->cryptoCourseGet($this->type, $token, $sub_token);
            if (!$exchange_rate->isSuccess()) return $exchange_rate->getError();
            if ($exchange_rate->getResult() === 0) return 'rate error';

            $pre_price = round($pay_method->min_sum / $exchange_rate->getResult(), 6);

            $nn = explode(' ', $pay_method->name);
            $name = $nn[0] ?? '';
            $sub_name = $nn[1] ?? false;

            return view('pay.requisites.payin.expay.crypto-new', [
                'ps' => $pay_method,
                'wallet' => $address->getResult(),
                'exchange_rate' => $exchange_rate,
                'pre_price' => $pre_price,
                'name' => $name,
                'sub_name' => $sub_name,
                'course' => $exchange_rate->getResult(),
            ])->render();
        }

        return parent::getFormRequisites($method, $pay_method);
    }

    public function getStatusCodes(): array
    {
        return [
            PayoutTransaction::STATUS_EXPAY_PENDING,
            PayoutTransaction::STATUS_EXPAY_SUCCESS,
            PayoutTransaction::STATUS_EXPAY_FAIL,
        ];
    }
}
