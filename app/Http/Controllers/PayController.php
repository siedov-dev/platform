<?php

namespace App\Http\Controllers;

use App\ApplicationForPayment;
use App\CustomerIo\CioEventType;
use App\CustomerIo\CustomerIo;
use App\Events\MoneyPayedOut;
use App\Helpers\Hlog;
use App\Helpers\Localization;
use App\Helpers\Popup;
use App\Models\Bonuses\KUserBonus;
use App\Models\PayCascadeRequisitesType;
use App\Models\PaymentSystems;
use App\Models\UserMoneyStat;
use App\Models\UserNotifications;
use App\PaymentsAPI\Alfakit\Alfakit;
use App\PaymentsAPI\Alphapo\AlphapoApi;
use App\PaymentsAPI\Einpayz\Einpayz;
use App\PaymentsAPI\FreeKassa\FkApi;
use App\PaymentsAPI\GateExpress\GateExpress;
use App\PaymentsAPI\InterKassa\IkInr;
use App\PaymentsAPI\InterKassa\IkInr3;
use App\PaymentsAPI\InterKassa\IkInrPtp;
use App\PaymentsAPI\InterKassa\IkKzt;
use App\PaymentsAPI\InterKassa\InterKassa;
use App\PaymentsAPI\Paycos\Paycos;
use App\PaymentsAPI\PayKassa\PayKassa;
use App\PaymentsAPI\PayLama\PayLama;
use App\PaymentsAPI\Paze\PazeApi;
use App\PaymentsAPI\SettlePay\SettlePay;
use App\PaymentsAPI\SwiftPay\SwiftPay;
use App\Payout\AutoPayoutSystem;
use App\Payin\Payin;
use App\Payout\Payout;
use App\Models\Bonuses\UserBonus;
use App\MUser;
use App\Payout\SettleAutoPayout;
use App\PayoutTransaction;
use App\Services\KPay\KPay;
use App\Services\KPay\PayInData;
use App\Services\KPay\UserStatsData;
use App\UserWallet;
use App\Wallet;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Helpers\Currency;
use \Exception;

class PayController extends Controller
{

    /**
     * Депозит
     * Создает депозит транзакцию, передает ее платежной системе.
     * Возвращает результат из соответствующей платежной системы.
     * @param Request $request
     * @return false|\Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response|string
     * @throws Exception
     */
    public function deposit(Request $request)
    {
        $user = MUser::getAuthOrFail();
        list($pay_system_id, $pay_method_alias, $method_id) = explode('/', $request->post('payment-method'));
        $pay_system = new Payin($user);

        try {
            $pay_system = $pay_system->getPayinSystem($pay_system_id);
        } catch (Exception $exception) {
            Log::error($exception);
            return response(__('wallet.paysystem_error'), 500);
        }
        /** @var PaymentSystems $system_method */
        $system_method = PaymentSystems::find($method_id);

        $transaction = new PayoutTransaction();
        $transaction->system_id = $pay_system->getType();
        $transaction->method = $pay_method_alias;
        $transaction->type = PayoutTransaction::TYPE_PAYIN;
        $transaction->status = PayoutTransaction::STATUS_PENDING;
        $transaction->method_id = $method_id;
        $transaction->currency = $user->getWallet()->getCurrencyName();
        $transaction->setCreatedOn(url('/'));

        $data = $pay_system->getRequestRequisites($pay_method_alias, $request->all(), $method_id);

        $last_data = $user->getUserData()->getValue('last_deposit_data', []);
        $last_data[$pay_system->getType()] = $data;
        if (isset($last_data[$pay_system->getType()]['cvc'])) $last_data[$pay_system->getType()]['cvc'] = '';
        $user->getUserData()->setValue('last_deposit_data', $last_data);
        $amount = $request->post('amount', 0);

        if (!empty($request->post('bonus_id')) && !$request->boolean('without_bonus')) {
            $transaction->bonus_id = $request->post('bonus_id');
            Log::channel('pay')->info('dep with bonus_id: ' . $request->post('bonus_id'));
        }

        try {
            if ($system_method?->shouldChangeAmount()) {
                $amount += random_int(1, 20);
            }
        } catch (\Exception $e) {
            Hlog::logException($e);
        }

        if (!$pay_system->canProceed($amount, $pay_method_alias, $data, $method_id)) {
            return response($pay_system->getError() ?? __('wallet.paysystem_error'), 500);
        }

        $resp = $pay_system->process($amount, $pay_method_alias, $data, $transaction, $method_id);
        if ($resp === false) {
            try {
                $moneyStat = UserMoneyStat::getStat($user, Wallet::getCurrencyCode($transaction->currency), 'total');
                if ($moneyStat->deposit_count == 0) {
                    Log::info("sending FdFail customer event");
                    CustomerIo::getInstance()->sendEvent($transaction->user_id, CioEventType::FdFail, [
                        'amount' => $transaction->sum,
                        'currency' => $transaction->currency,
                    ]);
                }
            } catch (\Throwable $e) {
                Hlog::logException($e);
            }
            $transaction->delete();
            return response($pay_system->getError() ?? __('wallet.paysystem_error'), 500);
        }

        if (!empty($resp['p2p'])) {
            $p2p_data = json_decode($resp['p2p'], true);
            return json_encode(['modal_data' => view('pay.requisites.payin.cascade.p2p-receiver', ['p2p_data' => $p2p_data, 'user' => $user])->render()]);
        }

        return json_encode($resp);
    }

    public function depositConfirm(Request $request)
    {
        if (!Auth::check()) return;
        $transaction_id = $request->post('transaction_id');
        KPay::getInstance()->depositConfirm($transaction_id, Auth::id());
    }

    public function depositCancel(Request $request)
    {
        if (!Auth::check()) return;
        $transaction_id = $request->input('transaction_id');
        KPay::getInstance()->depositCancel($transaction_id, Auth::id());
    }

    /**
     * Ожидание платежа.
     * Используется для 3ds платежных систем
     * @param Request $request
     * @return array|string[]
     */
    public function waitPayment(Request $request)
    {
        Log::channel('pay')->info('wait payment');
        Log::channel('pay')->info(var_export($request->all(), true));
        /** @var PayoutTransaction $transaction */
        $transaction = PayoutTransaction::find($request->input('transactionId'));

        if ($transaction === null) return ['status' => 'notFound', 'message' => 'Error, transaction not found!'];
        $form = null;
        if ($transaction->status == PayoutTransaction::STATUS_3DS_REDIRECT) {
            $tds_data = $transaction->get3dsData();
            if ($tds_data === null) return ['status' => 'error', 'message' => '3ds exchange error'];
            $form = view('pay.redirect-form', ['method' => $tds_data->method, 'url' => $tds_data->url,])->render();
        }
        if ($transaction->status == PayoutTransaction::STATUS_3DS) {
            $tds_data = $transaction->get3dsData();
            if ($tds_data === null) return ['status' => 'error', 'message' => '3ds exchange error'];
            $form = view('pay.redirect-form-3ds', [
                    'method' => 'POST',
                    'url' => $tds_data->url,
                    'PaReq' => $tds_data->params,
                    'MD' => $tds_data->md,
                    'TermUrl' => action([\App\Http\Controllers\PayController::class, 'treeDsCallback'], ['tr' => $transaction->id]),
                ])->render();
        }
        return [
            'status' => $transaction->status,
            'message' => __('wallet.wait-message_'.$transaction->status),
            'form' => $form,
        ];
    }

    public function cascadeRequisites(Request $request)
    {
        $user = MUser::getAuth();
        $cascade_id = $request->post('method');
        if ($cascade_id === null) return '';

        $resp = KPay::getInstance()->getCascadeRequisitesType($cascade_id);
        if (!$resp->isSuccess()) {
            Log::channel('pay')->error('cascadeRequisites error: ' . $resp->getError());
            return '';
        }
        $result = $resp->getResult();
        if ($result['is_email_required'] && !$user->isEmailVerified()) {
            return response(view('common.blocks.pay.email-required'), 200, ['blocked-to' => true]);
        }

        if ($result['is_phone_required'] && !$user->isPhoneVerified(true)) {
            return response(view('common.blocks.pay.phone-required'), 200, ['blocked-to' => true]);
        }
        $req_type = PayCascadeRequisitesType::tryFrom($result['requisites_type']);
        if ($req_type === null) {
            Log::channel('pay')->error('cascadeRequisites error: ' . $result['requisites_type']);
            return '';
        }

        return match ($req_type) {
            PayCascadeRequisitesType::None => '',
            PayCascadeRequisitesType::Phone => view('pay.requisites.payin.cascade.phone', ['user' => $user])->render(),
            PayCascadeRequisitesType::Email => view('pay.requisites.payin.cascade.mail', ['user' => $user])->render(),
            PayCascadeRequisitesType::EmailPhone => view('pay.requisites.payin.cascade.phone-mail', ['user' => $user])->render(),
            PayCascadeRequisitesType::Card => view('pay.requisites.payin.cascade.card', ['user' => $user])->render(),
        };
    }

    public function cascadeDeposit(Request $request)
    {
        $user = MUser::getAuthOrFail();
        $amount = $request->post('amount', 0);
        $currency_id = $user->getWallet()->getCurrencyId();
        $curr_name = $user->getWallet()->getCurrencyName();

        $cascade_id = $request->post('payment-method');
        $dep_data = new PayInData();
        $dep_data->amount = $amount;
        $dep_data->currency = $curr_name;
        $dep_data->user_id = $user->getId();
        $dep_data->method_id = 0;
        $dep_data->extra_data = $request->all();
        $dep_data->userStats = UserStatsData::getUserData($user->getId());


        if (!empty($request->post('bonus_id')) && !$request->boolean('without_bonus')) {
            $dep_data->bonus_id = $request->post('bonus_id');
            Log::channel('pay')->info('dep with bonus_id: ' . $request->post('bonus_id'));
        }

        $resp = KPay::getInstance()->cascadeProcessDeposit($cascade_id, $dep_data);
        if (!$resp->isSuccess()) {
            try {
                $moneyStat = UserMoneyStat::getStat($user, $currency_id, 'total');
                if ($moneyStat->deposit_count == 0) {
                    Log::info("sending FdFail customer event");
                    CustomerIo::getInstance()->sendEvent($user->getId(), CioEventType::FdFail, [
                        'amount' => $amount,
                        'currency' => $curr_name,
                    ]);
                }
            } catch (\Throwable $e) {
                Hlog::logException($e);
            }
            $result = $resp->getResult();
            if (is_array($result) && !empty($result['next_sum']) && $result['next_sum'] > 0) {
                return json_encode(['err' => __('wallet.cascade-error-next-sum', ['sum' => $result['next_sum']]), 'next_sum' => $result['next_sum']]);
            }
            return response(__('wallet.cascade-error'), 500);
        }
        $result = $resp->getResult();
        if (!empty($result['p2p'])) {
            $p2p_data = json_decode($result['p2p'], true);
            return json_encode(['modal_data' => view('pay.requisites.payin.cascade.p2p-receiver', ['p2p_data' => $p2p_data, 'user' => $user])->render()]);
        }

        return json_encode($resp->getResult());
    }

    /**
     * Колбэк 3ds
     * @param Request $request
     * @return RedirectResponse|void
     */
    public function treeDsCallback(Request $request)
    {
        Log::channel('pay')->info('3DS callback:'.var_export($request->all(), true));
        /** @var PayoutTransaction $transaction */
        $transaction = PayoutTransaction::find($request->input('tr'));
        if ($transaction === null) {
            Log::channel('pay')->error('transaction not found');
            return;
        }
        $api = new GateExpress();
        $res = $api->confirm($transaction->o_tr_id, $transaction->id, $request->input('PaRes'), $request->input('MD'));
        Log::channel('pay')->info('3DS confirm:'.var_export($res, true));
        return redirect()->route('landing');
    }

    //piastrix callback
    public function piastrix_pay_url(Request $request)
    {
        Log::channel('pay')->info('Start processing Piastrix transaction with id ' . $_POST['shop_order_id']);
        Log::channel('pay')->info('Piastrix raw ' . var_export($request->getContent(), true));
        Log::channel('pay')->info('Piastrix data ' . var_export($request->all(), true));
        if (!isset($_POST['shop_order_id'])) return response('', 500);

        $targetUrl = "http://127.0.0.1:23106/pay/piastrix";
        $response = Http::withHeaders($request->headers->all())
            ->withBody($request->getContent(), $request->header('Content-Type'))
            ->send($request->method(), $targetUrl);
        Log::channel('pay')->info('piastrix callback response ' . var_export($response->body(), true));
        return response($response->body(), $response->status())->withHeaders($response->headers());
    }


    /**
     * Выплата
     * Создает новую заявку на выплату (ApplicationForPayment).
     * @param Request $request
     */
    public function payout(Request $request)
    {
        list($pay_system_id, $pay_method, $method_id) = explode('/', $request->post('payment-method'));
        /** @var MUser $user */
        $user = Auth::user();

        //check payout is available
        if ($user->getWallet()->getLockedMoney() > 0) {
            return response(__('wallet.payoutRequirements', ['n' => $user->getWallet()->getLockedMoney() . ' ' . $user->getWallet()->getCurrencyName()]), 500);
        }

        $sum = floatval($request->post('sum', 0));
        $sum = round($sum, 2);

        $wallet = $user->getWallet();
        $payout = Payout::getPayoutSystem($pay_system_id);
        $card_no = $request->cardNumber;

        if ($user->requiresVerification()) {
            return response(__('wallet.payoutVerification'), 500);
        }

        if (!$payout->canProceed($sum, $pay_method, $wallet)) {
            return response($payout->getError(), 500);
        }

        log::channel('payout')->info('Creating payout for client#' . $user->id);

        /** @var AutoPayoutSystem $autopay_system */
        $pay_system = Payout::getAutoPaymentSystem($pay_method);
        $params = $request->post();
        $params['currency'] = UserWallet::currencyName($user->currency);

        if (!$pay_system->checkParams($params)) {
            return response($pay_system->getError() === null ? __('wallet.invalid_req_params') : $pay_system->getError(), 400);
        }

        $requisites = $pay_system->getRequestRequisites($pay_method, $params);

        DB::beginTransaction();

        $payout_app = new ApplicationForPayment();
        $payout_app->phone = $user->login;
        $payout_app->card_number = $card_no;
        $payout_app->FIO = $user->login;
        $payout_app->user_id = $user->id;
        $payout_app->suma = $sum;
        $payout_app->money = $sum;
        $payout_app->comentar = '';
        $payout_app->system_pay = $pay_method;
        $payout_app->confirmed = ApplicationForPayment::STATUS_PENDING;
        $payout_app->hall_id = $user->hall_id;
        $payout_app->currency = $wallet->getCurrencyId();
        $payout_app->requisites = json_encode($requisites);
        $payout_app->method_id = $method_id;
        $payout_app->system_id = $pay_system_id;

        //auto payout when user level >1 and daily withdraw sum lower than payout_limit
        if (!$user->isAutoPayDisabled() && $user->isVerified() && $this->getDailyPayedSum($wallet) + $sum <= $wallet->getMaxAutoPayoutSum()
            && $user->getRank()->getLevel() > 1 && $user->getMoneyInSince() > 0 && $user->getMoneyOutSince() > 0) {
            $payout_app->confirmed = ApplicationForPayment::STATUS_AUTO_PAY_PENDING;
            $payout_app->comentar = 'Ожидание автовыплаты';
        }

        try {
            // Withdraw given sum from the user's money.
            $wallet->remMoney($sum)->save();
        } catch (\Exception $e) {
            Hlog::logException($e);
            DB::rollBack();
            return response($payout->getError() === null
                ? __('wallet.request_error_retry')
                : $payout->getError(), 500);
        }

        if ($payout_app->save()) {
            DB::commit();

            UserBonus::cancelCashBackBonuses($user);
            KUserBonus::cancelPayoutBonuses($user);
            CustomerIo::getInstance()->sendEvent($user->getId(), CioEventType::BeforePayout);
            return ['amount' => $sum, 'tr_id' => $payout_app->getId(), 'curr' => $wallet->getCurrencyName()];
        }

        DB::rollBack();
        return response($payout->getError() === null
            ? __('wallet.request_error_retry')
            : $payout->getError(), 500);
    }

    public function payoutNew(Request $request)
    {
        list($pay_system_id, $pay_method) = explode('/', $request->post('payment-method'));
        /** @var MUser $user */
        $user = Auth::user();

        //check payout is available
        if ($user->getWallet()->getLockedMoney() > 0) {
            return response(__('wallet.payoutRequirements', ['n' => $user->getWallet()->getLockedMoney() . ' ' . $user->getWallet()->getCurrencyName()]), 500);
        }

        $sum = floatval($request->post('sum', 0));
        $sum = round($sum, 2);

        $wallet = $user->getWallet();
        $payout = Payout::getPayoutSystem($pay_system_id);
        $card_no = $request->cardNumber;

        if ($user->requiresVerification()) {
            return response(__('wallet.payoutVerification'), 500);
        }

        if (!$payout->canProceed($sum, $pay_method, $wallet)) {
            return response($payout->getError(), 500);
        }

        log::channel('payout')->info('Creating payout for client#' . $user->id);

        /** @var AutoPayoutSystem $autopay_system */
        $pay_system = Payout::getAutoPaymentSystem($pay_method);
        $params = $request->post();
        $params['currency'] = UserWallet::currencyName($user->currency);

        if (!$pay_system->checkParams($params)) {
            return response($pay_system->getError() === null ? __('wallet.invalid_req_params') : $pay_system->getError(), 400);
        }

        $requisites = $pay_system->getRequestRequisites($pay_method, $params);

        $autopay = false;

        //auto payout when user level >1 and daily withdraw sum lower than payout_limit
        if (!$user->isAutoPayDisabled() && $user->isVerified() && $this->getDailyPayedSum($wallet) + $sum <= $wallet->getMaxAutoPayoutSum()
            && $user->getRank()->getLevel() > 1 && $user->getMoneyInSince() > 0 && $user->getMoneyOutSince() > 0) {
            $autopay = true;
        }
        $result = KPay::getInstance()->payoutCreateApplication(
            user_id: $user->getId(),
            amount: $sum,
            currency_id: $wallet->getCurrencyId(),
            pay_method: $pay_method,
            requisites: $requisites,
            process_autopay: $autopay,
        );

        if ($result->isSuccess()) {
            UserBonus::cancelCashBackBonuses($user);
            KUserBonus::cancelPayoutBonuses($user);
            CustomerIo::getInstance()->sendEvent($user->getId(), CioEventType::BeforePayout);
            return ['amount' => $sum, 'tr_id' => $result->getResult(), 'curr' => $wallet->getCurrencyName()];
        }
        return response($payout->getError() === null
            ? __('wallet.request_error_retry')
            : $payout->getError(), 500);
    }

    /**
     * Закрывает текущее окно оплаты
     * @param Request $request
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
     */
    public function closeTab(Request $request)
    {
        return $this->viewOrJson('common.close-frame-or-tab');
    }

    /**
     * Успешный депозит
     * @param Request $request
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
     */
    public function success(Request $request)
    {
        return $this->viewOrJson('common.close-frame-or-redirect', ['url' => action('PayController@successNotify', $request->all(), false)]);
    }

    /**
     * Успешный депозит с уведомлением
     * @param Request $request
     * @return RedirectResponse
     */
    public function successNotify(Request $request)
    {
        Popup::message(__('wallet.pay_success'));
        return redirect()->route('cabinet.bonuses');
    }

    /**
     * Неудачный депозит
     * @param Request $request
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
     */
    public function fail(Request $request)
    {
        if (Auth::check()) {
            CustomerIo::getInstance()->sendEvent(Auth::id(), CioEventType::DepositFail);
        }
        return $this->viewOrJson('common.close-frame-or-redirect', ['url' => action('PayController@failNotify', $request->all(), false)]);
    }

    /**
     * Неудачный депозит с уведомлением
     * @param Request $request
     * @return RedirectResponse
     */
    public function failNotify(Request $request)
    {
        Popup::message(__('wallet.pay_error'));
        return redirect()->route('cabinet.bonuses');
    }

    /**
     * Ручной депозит из CRM
     * @param Request $request
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response|string
     */
    public function crmDeposit(Request $request)
    {
        Log::channel('pay')->info('crm deposit request: '.var_export($request->all(), true));
        $key = config('services.crmDepKey');

        $data = $request->all();
        $hash = base64_decode($data['hash']);

        unset($data['hash']);

        if ($hash !== hash_hmac('sha256', $data['manager_id']. $data['manager_login']. $data['amount'] . $data['wallet_id'] . $data['dt'], $key)) {
            return response('hash error', 403);
        }

        /** @var UserWallet $wallet */
        $wallet = UserWallet::findOrFail($data['wallet_id']);

        $wager = $data['wager']?? 0;

        $transaction = new PayoutTransaction();
        $transaction->user_id = $wallet->user_id;
        $transaction->hall_id = UserWallet::currencyHallId($wallet->getCurrencyId());
        $transaction->currency = $wallet->getCurrencyName();
        $transaction->hall_credits = 0;
        $transaction->date = date('Y-m-d H:i:s');
        $transaction->sum = $data['amount'];
        $transaction->operation = 'crm payment';
        $transaction->status_id = PayoutTransaction::STATUS_CRM_PAYMENT;
        $transaction->type_payout_id = 'crm';
        $transaction->use_bonus = 0;
        $transaction->system_id = 0;
        $transaction->method = 'crm';
        $transaction->coment = $data['manager_login'];
        $transaction->o_tr_id = $data['manager_id'];
        $transaction->bonus_id = intval($data['bonus']?? 0);

        try {
            Payin::completeTransaction(
                user: $wallet->getUser(),
                transaction: $transaction,
                amount: $data['amount'],
                status: PayoutTransaction::STATUS_CRM_PAYMENT,
                currency: $wallet->getCurrencyId(),
                dep_wager: $wager,
            );
        } catch (\Exception $e) {
            return response($e->getMessage(), 500);
        }

        return 'OK';
    }

    /**
     * Подтверждение ОТП кода (Мобильная коммерция)
     * @param Request $request
     * @return \Illuminate\Contracts\Foundation\Application|RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function confirmOtpCode(Request $request)
    {
        $api  = new Paycos('KZT', 'kz_mobile');
        $h2h = $api->h2hConfirm($request->otpurl, ['otp' => $request->otp]);
        if (isset($h2h['success']) && $h2h['success'] == true) {
            return redirect($h2h['processingUrl']);
        }
        return $this->back();
    }

    public function confirmOtp(Request $request)
    {
        $user = MUser::getAuthOrFail();
        $pay_system_id = $request->post('pid');
        $method_id = $request->post('mid');
        $otpid = $request->post('otpid');
        $otp_code = $request->post('otp');

        if (empty($otp_code)) {
            return $this->viewOrJson('pay.requisites.payin.otp-confirm',
                ['otpid' => $otpid, 'pid' => $pay_system_id, 'mid' => $method_id, 'error' => __('wallet.enter_otp_code')]);
        }

        $pay_system = new Payin($user);

        try {
            $pay_system = $pay_system->getPayinSystem($pay_system_id);
            $res = $pay_system->confirmOtp($pay_system_id, $method_id, $otpid, $otp_code);
            if (!$res->isSuccess()) {
                return $this->viewOrJson('pay.requisites.payin.otp-confirm',
                    ['otpid' => $otpid, 'pid' => $pay_system_id, 'mid' => $method_id, 'error' => $res->getError()]);
            } else {
                return $this->viewOrJson('pay.requisites.payin.otp-confirm',
                    ['otpid' => $otpid, 'pid' => $pay_system_id, 'mid' => $method_id, 'success' => true, 'msg' => __('wallet.otp_confirmed')]);
            }
        } catch (Exception $exception) {
            Hlog::logException($exception);
            return response(__('wallet.paysystem_error'), 500);
        }
    }

    public function resendOtp(Request $request)
    {
        $user = MUser::getAuthOrFail();
        $pay_system_id = $request->post('pid');
        $method_id = $request->post('mid');
        $otpid = $request->post('otpid');
        $otp_code = $request->post('otp');

        $pay_system = new Payin($user);

        try {
            $pay_system = $pay_system->getPayinSystem($pay_system_id);
            $res = $pay_system->resendOtp($pay_system_id, $method_id, $otpid);
            if (!$res->isSuccess()) {
                return $this->viewOrJson('pay.requisites.payin.otp-confirm',
                    ['otpid' => $otpid, 'pid' => $pay_system_id, 'mid' => $method_id, 'error' => $res->getError()]);
            } else {
                return $this->viewOrJson('pay.requisites.payin.otp-confirm',
                    ['otpid' => $otpid, 'pid' => $pay_system_id, 'mid' => $method_id, 'msg' => __('wallet.otp_resent')]);
            }
        } catch (Exception $exception) {
            Hlog::logException($exception);
            return response(__('wallet.paysystem_error'), 500);
        }
    }

    //settlepay payin
    public function settlepay(Request $request)
    {
        Log::channel('pay')->info('Start processing settlepay ' . PHP_EOL . var_export($request->all(), true));

        $trans_id = $request->get('transaction');

        if (!$trans_id) {
            Log::channel('pay')->error('transaction field not set');
            return response('Платеж не найден', 200);
        }

        // Find transaction.
        /** @var PayoutTransaction $transaction */
        $transaction = PayoutTransaction::where('id', $trans_id)
            ->where('status_id', PayoutTransaction::STATUS_SETTLE_PENDING)
            ->first();

        if ($transaction === null) {
            Log::channel('pay')->error('Transaction with id ' . $trans_id . ' is not found. Request: ' . PHP_EOL . var_export($request->all(), true));
            return response('Transaction not found', 200);
        }

        // Find user which create this transaction.
        /** @var MUser $user $user */
        $user = MUser::find($transaction->user_id);

        if ($user === null) {
            Log::channel('pay')->error('User of the transaction is not found.');
            return response('User not found.', 200);
        }

        $settle_api = new SettlePay();

        //check status on unverified mode
        $tr_status = $settle_api->transactionFind($trans_id);

        if ($tr_status === false || $settle_api->is_error) {
            //or in verified mode
            $settle_api->setMode(SettlePay::MODE_VERIFIED);
            $tr_status = $settle_api->transactionFind($trans_id);

            if ($tr_status === false || $settle_api->is_error) {
                Log::channel('pay')->error('Can`t get transaction status');
                return response('Can`t get transaction status', 500);
            }
        }

        if ($tr_status['status'] != SettlePay::STATUS_SUCCESS) {
            $transaction->error_code_id = intval($tr_status['status']);
            $transaction->status_id = PayoutTransaction::STATUS_SETTLE_FAIL;
            $transaction->save();
            return response('', 200);
        }

        $transaction->sum = $tr_status['amount'] / 100;
        $transaction->fee = ($tr_status['amount'] - $tr_status["account_wallet_amount"]) / 100;
        $transaction->total_sum = $transaction->amount + $transaction->fee;
        if (isset($tr_status['fields']['card_number'])) {
            $transaction->card_number = $tr_status['fields']['card_number'];
        }

        $currency = UserWallet::getCurrencyCode($tr_status['amount_currency']);

        $this->completeTransaction($user, $transaction, $tr_status['amount'] / 100, PayoutTransaction::STATUS_SETTLE_SUCCESS, $currency);

        return response('', 200);
    }

    public function settlepayout(Request $request)
    {
        Log::channel('pay')->info('payout settlepay ' . PHP_EOL . var_export($request->all(), true));

        $trans_id = $request->get('transaction');
        $app_id = $request->get('app_id');

        $application = ApplicationForPayment::find($app_id);

        $payout = new SettleAutoPayout();

        if ($application) {
            $payout->processAutoPayment($application);
        } else {
            Log::channel('pay')->error('Can`t get application id#' . $app_id);
        }

        return response('', 200);
    }


    /**
     * Редирект пользователя на сайт
     * с которого была совершена транзакция
     * @param Request $request
     * @return \Illuminate\Contracts\Foundation\Application|RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function redirectByTransaction(Request $request)
    {
        if (!empty($request->input('order_id'))) {
            /** @var PayoutTransaction $transaction */
            $transaction = PayoutTransaction::find($request->input('order_id'));
            if ($transaction !== null && !empty($transaction->getCreatedOn())) {
                return redirect($transaction->getCreatedOn());
            }
        }
        return redirect()->route('landing', [], false);
    }

    /**
     * Завершает входящую транзакцию
     * @param MUser $user
     * @param PayoutTransaction $transaction
     * @param float $amount
     * @param int $status
     * @param int $currency
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     * @throws Exception
     */
    public function completeTransaction(MUser $user, PayoutTransaction $transaction, float $amount, int $status, int $currency = UserWallet::CURRENCY_RUB)
    {
        return Payin::completeTransaction($user, $transaction, $amount, $status, $currency);
    }

    /**
     * Возвращает суммы выплаченные за сегодня
     * @param Wallet $wallet
     * @return mixed
     */
    private function getDailyPayedSum(Wallet $wallet)
    {
        $payed = PayoutTransaction::where('user_id', Auth::id())
            ->where('hall_id', $wallet->getHallId())
            ->whereIn('status_id', ['4', '25', '61', '45'])
            ->where('date', '>=', now()->format('Y-m-d 00:00:00'))
            ->where('date', '<', now()->addDay()->format('Y-m-d 00:00:00'))
            ->sum('sum');

        $pending = ApplicationForPayment::where('user_id', Auth::id())
            ->whereIn('confirmed', [
                ApplicationForPayment::STATUS_PENDING,
                ApplicationForPayment::STATUS_AUTO_PAY_PENDING,
                ApplicationForPayment::STATUS_AUTO_PAY_PROCESS
            ])
            ->where('currency', $wallet->getCurrencyId())
            ->where('date', '>=', now()->format('Y-m-d 00:00:00'))
            ->where('date', '<', now()->addDay()->format('Y-m-d 00:00:00'))
            ->sum('money');

        return $payed + $pending;
    }


    /**
     * Обработка автовыплат
     * @return void
     */
    public static function processAutoPayments()
    {
        try {
            $apps = ApplicationForPayment::where('confirmed', ApplicationForPayment::STATUS_AUTO_PAY_PENDING)->get();

            /** @var ApplicationForPayment $app */
            foreach ($apps as $app) {
                //withdraw
                if ($app) {
                    log::channel('payout')->info('processing autopay application #' . $app->id);
                    /** @var MUser $user */
                    $user = MUser::find($app->user_id);
                    Localization::setAppLocale($user->getLocale());
                    $payout_system = Payout::getAutoPaymentSystem($app->system_pay);
                    try {
                        $payout_system->processAutoPayment($app);
                    } catch (\Throwable $e) {
                        Hlog::logException($e, 'payout');
                    }
                }
            }

            $apps = ApplicationForPayment::where('confirmed', ApplicationForPayment::STATUS_AUTO_PAY_PROCESS)->get();

            foreach ($apps as $app) {
                if ($app) {
                    log::channel('payout')->info('check payment status application #' . $app->id);
                    /** @var MUser $user */
                    $user = MUser::find($app->user_id);
                    Localization::setAppLocale($user->getLocale());
                    $payout_system = Payout::getAutoPaymentSystem($app->system_pay);
                    try {
                        $payout_system->processAutoPayment($app);
                        $app->refresh();
                        if ($app->confirmed == ApplicationForPayment::STATUS_AUTO_PAY_SUCCESS) {
                            event(new MoneyPayedOut($user, $app->suma, $app->getCurrency()));
                        }
                    } catch (\Throwable $e) {
                        Hlog::logException($e, 'payout');
                    }
                }
            }

        } catch (\Throwable $e) {
            Hlog::logException($e, 'payout');
        }

    }
}
