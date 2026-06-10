<?php

namespace App\Http\Controllers;

use App\Events\CurrencyChangedEvent;
use App\GamesProviders\WalletGameWalletAdapter;
use App\Helpers\Hlog;
use App\Models\Bonuses\KUserBonus;
use App\Services\KPay\KPay;
use App\Services\KPay\UserStatsData;
use App\Services\KSocket\KSocket;
use App\Services\Users\UserManager;
use App\Http\Responses\NoCookiesResponse;
use App\Models\PaymentSystems;
use App\MUser;
use App\Payin\Payin;
use App\Payout\Payout;
use App\UserWallet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class WalletController extends Controller
{
    public function wallet()
    {
        return $this->viewOrJson('common.blocks.user-wallet');
    }

    public function deposit(Request $request)
    {
        $user = \App\MUser::getAuth();
        $group_id = $request->input('group_id');
        $dep_bonus = \App\Models\Bonuses\KUserBonus::getWaitingDeposit($user);
        $start_amount = $request->input('deposit', 0);
        $without_bonus = $request->boolean('without_bonus', false);
        if (config('payment_systems.cascade_enabled') && $group_id === null && $group_id !== '0') {
            $cascade_id = $request->input('cascade_id');
            $cascades = [];
            $groups = \App\Models\PaymentSystems::getDepositGroups($user->getCurrency());
            $groups = $groups->where('group_id', 4); // crypto hardcoded
            if ($cascade_id === null) {
                $user_stats = UserStatsData::getUserData($user->getId());
                $res = KPay::getInstance()->getDepositCascades($user->getId(), $user->getCurrency(), $user->isTestUser(), $user_stats);
                if ($res->isSuccess()) {
                    $cascades = $res->getResult();
                }
            }

            $view_name = match ($group_id) {
                '4' => 'deposit-crypto',
                default => 'deposit-cascade',
            };

            return $this->viewOrJson(('common.blocks.' . $view_name), compact('group_id', 'cascade_id',
                    'start_amount', 'without_bonus',
                    'dep_bonus', 'user', 'cascades', 'groups'));
        }
        if ($group_id === '0') {
            $group_id = null;
        }

        $view_name = match ($group_id) {
            '4' => 'deposit-crypto',
            default => 'deposit',
        };

        return $this->viewOrJson(('common.blocks.' . $view_name), compact('group_id', 'start_amount', 'without_bonus', 'dep_bonus', 'user'));
    }

    public function payout(Request $request)
    {
        $group_id = $request->input('group_id');
        return $this->viewOrJson('common.blocks.payout', compact('group_id'));
    }

    public function buyCrypto()
    {
        return $this->viewOrJson('common.blocks.buy-crypto');
    }

    public function switchWallet(Request $request)
    {

        /** @var MUser $user */
        $user = MUser::find(Auth::id());
        if ($user) {
            $user->currency = $request->post('currency', UserWallet::CURRENCY_RUB);
            $user->hall_id = UserWallet::currencyHallId($user->currency);
            $user->save();
            event(new CurrencyChangedEvent($user->getId(), $user->getCurrency()));
            KSocket::getInstance()->sendEvent($user->getId(), 'wallet:switch_currency', [
                'currency' => $user->getCurrency(),
                'currency_name' => UserWallet::currencyName($user->getCurrency()),
                'currency_symbol' => UserWallet::currencySymbol($user->getCurrency()),
                'balance' => $user->getBalance(),
            ]);
        }
        return response('', 200);
    }

    public function getBalance(Request $request)
    {
        /** @var MUser $user */
        $user = MUser::getAuth();
        if ($user === null) {
            $id = $request->get('id');
            $user = MUser::where('id', $id)->first();
        }

        if ($user === null) {
            return response('Authorization required', 401);
        }

        try {
            $balances = UserManager::getBalances($user->getId());
        } catch (\Throwable $e) {
            Hlog::logException($e);
        }
        if ($balances['cd'] !== 200) {
            Log::error("balances error ".($balances['err']??""));
        }
         foreach ($balances['Values'] as $balance) {
             if ($balance['ci'] == $user->getCurrency()) {
                 $data['current']['id'] = $balance['ci'];
                 $data['current']['name'] = $balance['cn'];
                 $data['current']['value'] = $balance['v'];
                 $data['current']['real']  = $balance['v'];
             }
             if (!$balance['a']) continue;
             $data[$balance['cn']] = $balance['v'];

             // if user has active bonus with shared balance
            if ($balance['b'] > 0) {
                $wallet = $user->getWallet($balance['ci']);
                $bonus = KUserBonus::find($balance['b']);
                if ($bonus === null) {
                    Log::info('balance bonus not found #'.$balance['b']);
                    $user->getWallet($balance['ci'])->setBonusId(0);
                    continue;
                }
//                $bonus = KUserBonus::convertByType($bonus);
                if ($bonus->isCompleted()) {
                    Log::info('balance bonus completed #'.$balance['b']);
                    $user->getWallet($balance['ci'])->setBonusId(0);
                } elseif($bonus->isActive()) {
                    $game_wallet = new WalletGameWalletAdapter($wallet);
                    $cents = $bonus->getBalanceCents($game_wallet);
//                    $data[$wallet->getCurrencyName()] = $cents / 100;
//                    if ($wallet->getCurrencyId() == $user->getCurrency()) {
//                        $data['current']['value'] = $cents / 100;
//                    }
                    $data['bonus_balance'] = [
                        'currency' => $wallet->getCurrencyName(),
                        'value' => $cents / 100,
                    ];
                    if ($wallet->getCurrencyId() == $user->getCurrency()) {
                        $data['current']['bonus_balance'] = $data['bonus_balance'];
                    }
                }
            }
         }

         // user events in balance response
//         try {
//             $events = UserManager::pullEvents($user->getId());
//             $data['events'] = $events;
//         } catch (\Throwable $e) {
//             Hlog::logException($e);
//         }

        $response = new NoCookiesResponse(json_encode($data), 200);
        $response->header('Access-Control-Allow-Origin', '*')
            ->header('Content-Type', 'application/json')
            ->header('Cache-Control', 'no-cache');
        return $response;
    }

    public function getPayoutRequisites(Request $request)
    {
        $method_str = $request->post('method');
        if ($method_str === null) return '';

        $payout = new Payout();

        $system = $payout->getPayoutSystem(explode('/', $method_str)[0]);
        $method = explode('/', $method_str)[1];
        $method_id = explode('/', $method_str)[2];
        $paySystem = PaymentSystems::find($method_id);

        return $system->getFormRequisites($method, $paySystem);
    }

    public function getPayinRequisites(Request $request)
    {
        $method_str = $request->post('method');
        if ($method_str === null) return '';

        $user = MUser::getAuth();

        $payin = new Payin($user);

        $payInSystem = $payin->getPayinSystem(explode('/', $method_str)[0]);
        $method = explode('/', $method_str)[1];
        $method_id = explode('/', $method_str)[2];
        /** @var PaymentSystems $paySystem */
        $paySystem = PaymentSystems::find($method_id);
        if ($paySystem === null) return '';

        if ($paySystem->isEmailReq() && !$user->isEmailVerified()) {
            return response(view('common.blocks.pay.email-required'), 200, ['blocked-to' => true]);
        }

        if ($paySystem->isPhoneReq() && !$user->isPhoneVerified(true)) {
            return response(view('common.blocks.pay.phone-required'), 200, ['blocked-to' => true]);
        }

        $block = $payInSystem->isMultidepBlock($method, $user);
        if ($block !== null) {
            return response(view('common.blocks.pay.deposit-blocked', ['block_to' => $block]), 200, ['blocked-to' => true]);
        }

        return $payInSystem->getFormRequisites($method, $paySystem);
    }

    public function add(Request $request)
    {
        if ($request->isMethod('POST')) {
            if (!empty($request->currency)) {
                /** @var MUser $user */
                $user = auth()->user();
                $wallet = $user->getWallet($request->currency);
                $wallet->setActive();
            }
            return redirect()->route('cabinet');
        }
        return $this->viewOrJson('common.blocks.wallet-add');
    }

    public function del($lang, Request $request, $currency_id)
    {
        if ($request->isMethod('POST')) {
            /** @var MUser $user */
            $user = auth()->user();
            if (!empty($currency_id) && $currency_id != $user->currency) {
                $user->getWallet($currency_id)->setInactive();
            }
            return redirect()->route('cabinet');
        }
        return $this->viewOrJson('common.blocks.wallet-del', ['currency_id' => $currency_id]);
    }
}
