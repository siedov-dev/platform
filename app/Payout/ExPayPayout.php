<?php

namespace App\Payout;

use App\ApplicationForPayment;
use App\Models\PaymentSystemGroup;
use App\Models\PaymentSystems;
use App\Models\UserCryptoWallet;
use App\Models\UserNotifications;
use App\MUser;
use App\PaymentsAPI\ExPay\ExPay;
use App\Services\KPay\KPay;
use App\UserWallet;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ExPayPayout extends Payout implements AutoPayoutSystem
{
    private ExPay $api;

    public string $method;

    public function __construct(string $method)
    {
        $this->api = new ExPay;
        $this->method = $method;
    }

    public function checkParams(array $params): bool
    {
        return true;
    }

    public function processAutoPayment(ApplicationForPayment $applicationForPayment)
    {
        Log::channel('payout')->info('Start : ' . $applicationForPayment->system_pay . ' ' . $applicationForPayment->id . ' ' . $applicationForPayment->currency . ' ' . $applicationForPayment->confirmed);

        if ($applicationForPayment->confirmed == ApplicationForPayment::STATUS_AUTO_PAY_PENDING) {

            $applicationForPayment->confirmed = ApplicationForPayment::STATUS_AUTO_PAY_PROCESS;
            $applicationForPayment->comentar = 'обрабатывается автовыплата ' . __CLASS__;

            if ($applicationForPayment->save()) {
//                $ps = PaymentSystems::where('method_alias', $applicationForPayment->system_pay)
//                    ->where('currency_id', $applicationForPayment->currency)
//                    ->where('type', PaymentSystems::TYPE_PAYOUT)
//                    ->firstOrFail();
                $ps = PaymentSystems::getPayoutSystemForMethod($applicationForPayment->system_pay, $applicationForPayment->currency);

                $method = explode('.', $applicationForPayment->system_pay)[1];
                [$token, $sub_token] = explode('_', $method);
                if ($sub_token === 'SBPRUB2') $sub_token = 'SBPRUB';

                $requisites = json_decode($applicationForPayment->requisites, true);

                if ($ps->system_group == PaymentSystemGroup::Crypto) {
                    $exchange_rate = $this->api->course(from: $sub_token, to: $token);

                    $amount_crypto = round($applicationForPayment->money * $exchange_rate[0]->price, 4);

                    $out_api = $this->api->crypto_out(
                        transaction: $applicationForPayment,
                        amount: $amount_crypto,
                        token: $token
                    );

                    if (!isset($out_api->status)) {
                        $applicationForPayment->confirmed = ApplicationForPayment::STATUS_AUTO_PAY_ERROR;
                        $applicationForPayment->comentar = 'Ошибка запроса, останов...';
                    } elseif ($out_api->status == 'ERROR') {
                        $applicationForPayment->confirmed = ApplicationForPayment::STATUS_AUTO_PAY_ERROR;
                        $applicationForPayment->error = $out_api->description ?? 'unknown error';
                    } else {
                        $applicationForPayment->error = $out_api->description ?? 'unknown error';
                        $applicationForPayment->outer_id = $out_api->tracker_id ?? null;
                    }

                    $applicationForPayment->save();

                } else {

                    [$token, $sub_token] = explode('_', $method);
                    if ($sub_token === 'SBPRUB2') $sub_token = 'SBPRUB';

                    $result = $this->api->withdraw($applicationForPayment, $applicationForPayment->money, $token, $sub_token, $requisites);

                    log::channel('payout')->info('result: ' . var_export($result, true));

                    if (empty($result['status'])) {
                        $applicationForPayment->confirmed = ApplicationForPayment::STATUS_AUTO_PAY_ERROR;
                        $applicationForPayment->comentar = 'Ошибка запроса, останов...';
                        $applicationForPayment->save();
                    } elseif ($result['status'] === 'ERROR') {
                        $applicationForPayment->confirmed = ApplicationForPayment::STATUS_AUTO_PAY_ERROR;
                        $applicationForPayment->error = $result['message'] ?? 'unknown error';
                        $applicationForPayment->save();
                    } else {
                        $applicationForPayment->outer_id = $result['tracker_id'] ?? null;
                        $applicationForPayment->save();
                    }

                }
            }
        } elseif ($applicationForPayment->confirmed == ApplicationForPayment::STATUS_AUTO_PAY_PROCESS) {

            if (!empty($applicationForPayment->outer_id)) {

//                $ps = PaymentSystems::where('method_alias', $applicationForPayment->system_pay)->first();
                $ps = PaymentSystems::getPayoutSystemForMethod($applicationForPayment->system_pay, $applicationForPayment->currency);

                if ($ps->system_group == PaymentSystemGroup::Crypto) {
                    $transaction_api = $this->api->getTransactionInfo($applicationForPayment->outer_id);

                    if ($transaction_api['transaction']['status'] == 'SUCCESS') {

                        $applicationForPayment->confirmed = ApplicationForPayment::STATUS_AUTO_PAY_SUCCESS;
                        $applicationForPayment->comentar = "Завершено успешно";
                        //$applicationForPayment->suma = $transaction_api['transaction']['amount'];
                        $applicationForPayment->save();

                        $amount = $applicationForPayment->suma;
                        $fee = $amount * 0.025;

                        DB::table('payout_transactions')
                            ->insert([
                                'user_id' => $applicationForPayment->user_id,
                                'hall_id' => $applicationForPayment->hall_id,
                                'date' => date('Y-m-d H:i:s'),
                                'currency' => UserWallet::currencyName($applicationForPayment->currency),
                                'operation' => 'Автовыплата ExPay',
                                'sum' => $applicationForPayment->money,
                                'fee' => $fee,
                                'total_sum' => $applicationForPayment->money + $fee,
                                'status_id' => '45',
                            ]);

                        if ($applicationForPayment->user_id) {
                            UserNotifications::notify($applicationForPayment->user_id, __('notify.title_payout'),
                                '<span class="gold-text">' . $applicationForPayment->money . ' ' . UserWallet::currencyName($applicationForPayment->currency) . '</span> ' . __('notify.payout_success'));
                        }

                    } elseif ($transaction_api['transaction']['status'] === 'ERROR') {
                        $applicationForPayment->confirmed = ApplicationForPayment::STATUS_AUTO_PAY_ERROR;
                        $applicationForPayment->error = $transaction_api['transaction']['status'];
                        $applicationForPayment->save();

                    } elseif ($transaction_api['status'] != 'ok') {

                        $applicationForPayment->confirmed = ApplicationForPayment::STATUS_AUTO_PAY_ERROR;
                        $applicationForPayment->error = $transaction_api['description'] ?? 'unknown error';;
                        $applicationForPayment->save();

                    }

                } else {

                    $result = $this->api->getTransactionInfo($applicationForPayment->outer_id);

                    log::channel('payout')->info(__CLASS__ . ' info result: ' . var_export($result, true));

                    if (empty($result) || !isset($result['status'])) {
                        $applicationForPayment->confirmed = ApplicationForPayment::STATUS_AUTO_PAY_ERROR;
                        $applicationForPayment->comentar = 'Ошибка запроса, останов...';
                        $applicationForPayment->save();

                    } elseif ($result['status'] !== 'ok') {
                        $applicationForPayment->confirmed = ApplicationForPayment::STATUS_AUTO_PAY_ERROR;
                        $applicationForPayment->error = $result['status'];
                        $applicationForPayment->save();

                    } elseif ($result['transaction']['status'] === 'ERROR') {
                        $applicationForPayment->confirmed = ApplicationForPayment::STATUS_AUTO_PAY_ERROR;
                        $applicationForPayment->error = $result['transaction']['status'];
                        $applicationForPayment->save();

                    } elseif ($result['transaction']['status'] === 'SUCCESS') {

                        $applicationForPayment->confirmed = ApplicationForPayment::STATUS_AUTO_PAY_SUCCESS;
                        $applicationForPayment->comentar = "Завершено успешно";
                        $applicationForPayment->suma = $result['transaction']['amount'];
                        $applicationForPayment->save();
                        $fee = $result['transaction']['transaction_commission'] ?? 0;
                        $fee = $fee * $result['transaction']['rate'];

                        DB::table('payout_transactions')
                            ->insert([
                                'user_id' => $applicationForPayment->user_id,
                                'hall_id' => $applicationForPayment->hall_id,
                                'date' => date('Y-m-d H:i:s'),
                                'currency' => UserWallet::currencyName($applicationForPayment->currency),
                                'operation' => 'Автовыплата ExPay',
                                'sum' => $applicationForPayment->money,
                                'fee' => $fee,
                                'total_sum' => $applicationForPayment->money + $fee,
                                'status_id' => '45',
                            ]);

                        if ($applicationForPayment->user_id) {
                            UserNotifications::notify($applicationForPayment->user_id, __('notify.title_payout'),
                                '<span class="gold-text">' . $applicationForPayment->money . ' ' . UserWallet::currencyName($applicationForPayment->currency) . '</span> ' . __('notify.payout_success'));
                        }

                    }
                }

            } else {
                $applicationForPayment->confirmed = ApplicationForPayment::STATUS_AUTO_PAY_ERROR;
                $applicationForPayment->error = 'outer_id not set';
                $applicationForPayment->save();
            }
        }
    }

    public function getFormRequisites(string $method, ?PaymentSystems $pay_method): string
    {
        $method_ = explode('.', $method)[1];
        [$token, $sub_token] = explode('_', $method_);

//        $ps = PaymentSystems::where('method_alias', $method)->where('type', PayoutTransaction::TYPE_PAYOUT)->firstOrFail();
        $ps = $pay_method;

        if ($ps->system_group == PaymentSystemGroup::Crypto) {

            $rate = KPay::getInstance()->cryptoCourseGet(self::TYPE_EXPAY, $sub_token, $token);
            if (!$rate->isSuccess() || $rate->getResult() === 0) return 'rate error';
            $exchange_rate = $rate->getResult();

//            $exchange_rate = $this->api->course(from: $sub_token, to: $token);
//            if (empty($exchange_rate[0])) return 'rate error';

            $pre_price = round($ps->min_sum / $exchange_rate, 6);

            $nn = explode(' ', $ps->name);
            $name = $nn[0] ?? '';
            $sub_name = $nn[1] ?? false;

            $wallets = UserCryptoWallet::getPayoutWallets(auth()->id(), $ps->system_name, $token);
            $wallets_exists = count($wallets) > 0;

//            $wallets_exists =
//                auth()->user()->crypto_wallet(
//                system_code: $ps->system_name,
//                payment_type: PayoutTransaction::TYPE_PAYOUT,
//                currency: $token,
//                fiat_currency: null)->exists();


            return view('pay.requisites.payout.expay.crypto', [
                'ps' => $ps,
                //'wallet' => $wallet,
//                'exchange_rate' => $exchange_rate,
                'pre_price' => $pre_price,
                'name' => $name,
                'sub_name' => $sub_name,
                'course' => $exchange_rate,
                'token' => $token,
                'wallets' => $wallets,
                'wallets_exists' => $wallets_exists,
            ])->render();

        }

        if ($method === 'ExPay.INRP2P_IMPSINR') {
            return view('pay.requisites.payout.expay-imps', ['method' => $method,])->render();
        }
        if ($method === 'ExPay.INRP2P_UPIINR') {
            return view('pay.requisites.payout.expay-upi', ['method' => $method,])->render();
        }
        if ($method === 'ExPay.INRP2P_PHPENINR') {
            return view('pay.requisites.payout.expay-phonepe', ['method' => $method,])->render();
        }
        if ($method === 'ExPay.INRP2P_PAYTMINR') {
            return view('pay.requisites.payout.expay-paytm', ['method' => $method,])->render();
        }
        if ($method === 'ExPay.CARDRUBP2P_SBPRUB') {
            return view('pay.requisites.payout.expay-sbp', ['method' => $method,])->render();
        }
        if ($method === 'ExPay.CARDRUBP2P_SBPRUB2') {
            return view('pay.requisites.payout.expay-sbp', ['method' => $method,])->render();
        }
        if ($method === 'ExPay.USDTTRC_') {
            return view('pay.requisites.payout.wallet', ['method' => $method,])->render();
        }

        return parent::getFormRequisites($method, $pay_method);
    }

    public function getRequestRequisites(string $method, array $data): array
    {
        $this->autosave_crypto_wallet();
        return parent::getRequestRequisites($method, $data);
    }

    public function autosave_crypto_wallet(): void
    {
        $request = request();
        if ($request->has('autosave_wallet') && $request->autosave_wallet) {

            /** @var MUser $user */
            $user = Auth::user();

            $method_ = explode('.', $this->method)[1];
            [$token, $sub_token] = explode('_', $method_);

            $resp = KPay::getInstance()->cryptoPayoutWalletGetOrCreate($user->getId(), "ExPay", $token, $request->get('cardNumber'));
            if (!$resp->isSuccess()) {
                Log::channel('payout')->error('autosave payout cryptowallet error: ' . $resp->getError());
            }

//            $user->crypto_wallet(
//                system_code: $token,
//                payment_type: 1,
//            )->where('address', $request->get('cardNumber'))->firstOrCreate(
//                [
//                    'payment_type' => '1',
//                    'currency' => $token,
//                    'system_id' => $this->api::system_code,
//                    'address' => $request->get('cardNumber')
//                ]
//            );
        }

    }

}
