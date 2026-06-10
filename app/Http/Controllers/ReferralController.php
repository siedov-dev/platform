<?php

namespace App\Http\Controllers;

use App\Models\Slc\Achievements\SlcAchievement;
use App\MUser;
use App\PayoutTransaction;
use App\Services\Referrals\ReferralsService;
use App\UserWallet;
use App\Wallet;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ReferralController extends Controller
{

    public function cabinet(Request $request)
    {
        \session()->flash('cabinetShow', 'partnership');

        /** @var MUser $user */
        $user = MUser::getAuth();

        if (!$user) {
            return redirect(action('IndexController@index'));
        }
        $user_currency = $user->getCurrency();

        $ref_service = ReferralsService::getInstance();

        $user_ref = $ref_service->getRefCode($user->getId());
        $ref_params = $ref_service->getRefConfig($user->getId(), $user->getCurrency());
        $achievement_rewards = $ref_service->getAchievementsRewards($user->getCurrency());
        $achievements = SlcAchievement::whereIn('id', array_keys($achievement_rewards))->get()->keyBy('id');

        $r_history = $ref_service->getReferralEvents($user_ref['ref_id'], 1, 10);
        $ref_history = collect($r_history['events']?? []);
        $events_paginator = new LengthAwarePaginator($ref_history, $r_history['total_rows'], 10, 1,
            ['path' => route('cabinet.partnership.eventsPage', [], false), 'query' => $request->query(), 'pageName' => 'eventsPage']);

        $ref_event_users = MUser::whereIn('id', $ref_history->pluck('user_id')->toArray())->pluck('login', 'id');

        $r_users = $ref_service->getReferralUsers($user_ref['ref_id'], 1, 10);
        $ref_users = collect($r_users['users']?? []);
        $users_with_deps = $ref_service->getReferralUsersCountWithDeps($user_ref['ref_id']);
        $users_paginator = new LengthAwarePaginator($ref_users, $r_users['total_rows'], 10, 1,
            ['path' => route('cabinet.partnership.refUsersPage', [], false), 'query' => $request->query(), 'pageName' => 'usersPage']
        );

        $params = [
            'user' => $user,
            'referral' => $user_ref,
            'users_with_deposit' => $users_with_deps,
            'ref_params' => $ref_params,
            'currency' => $user->getCurrencyName(),
            'ref_history' => $ref_history,
            'r_users' => $r_users,
            'ref_users' => $ref_users,
            'achievements' => $achievements,
            'achievement_rewards' => $achievement_rewards,
            'ref_event_users' => $ref_event_users,
            'events_paginator' => $events_paginator,
            'users_paginator' => $users_paginator,
            'user_currency' => $user_currency,
        ];

        if ($request->ajax()) {
            $this->renderAdaptive('cabinet.partnership', $params, $this->getLayouts());
        } else {
            $this->renderAdaptive('landing.index', $params, ['layouts.main']);
        }
        return $this->sendPreparedResponse();
    }

    public function eventsPage(Request $request)
    {
        /** @var MUser $user */
        $user = MUser::getAuth();

        if (!$user) {
            return "";
        }

        $user_ref = ReferralsService::getInstance()->getRefCode($user->getId());
        $user_currency = $user->getCurrency();

        $page = $request->get('eventsPage', 1);

        $r_history = ReferralsService::getInstance()->getReferralEvents($user_ref['ref_id'], $page, 10);
        $ref_history = collect($r_history['events']?? []);
        $ref_event_users = MUser::whereIn('id', $ref_history->pluck('user_id')->toArray())->pluck('login', 'id');
        $events_paginator = new LengthAwarePaginator($ref_history, $r_history['total_rows'], 10, $page,
            ['path' => route('cabinet.partnership.eventsPage', [], false), 'query' => $request->query(), 'pageName' => 'eventsPage']);

        return $this->viewOrJson('desktop.cabinet.ref-history', [
            'events_paginator' => $events_paginator,
            'ref_event_users' => $ref_event_users,
            'ref_history' => $ref_history,
            'user_currency' => $user_currency,
        ], []);
    }

    public function refUsersPage(Request $request)
    {
        /** @var MUser $user */
        $user = MUser::getAuth();

        if (!$user) {
            return "";
        }

        $user_ref = ReferralsService::getInstance()->getRefCode($user->getId());
        $user_currency = $user->getCurrency();

        $page = $request->get('usersPage', 1);

        $r_users = ReferralsService::getInstance()->getReferralUsers($user_ref['ref_id'], $page, 10);
        $ref_users = collect($r_users['users']?? []);
        $users_paginator = new LengthAwarePaginator($ref_users, $r_users['total_rows'], 10, $page,
            ['path' => route('cabinet.partnership.refUsersPage', [], false), 'query' => $request->query(), 'pageName' => 'usersPage']
        );

        return $this->viewOrJson('desktop.cabinet.ref-users', [
            'users_paginator' => $users_paginator,
            'ref_users' => $ref_users,
            'user_currency' => $user_currency,
            'r_users' => $r_users,
        ], []);
    }

    public function transferMoney()
    {
        /** @var MUser $user */
        $user = Auth::user();
        if (!$user) {
            return response(__('cabinet.user_not_found'), 400);
        }
        $message = '';
        $money_given = false;

        foreach (Wallet::activeCurrencies() as $currency) {
            if ($user->getWallet($currency)->getReferralMoney() > 0) {
                $resp = ReferralsService::getInstance()->transferRefMoney($user->getId(), $currency);
                if ($resp['cd'] !== 200) {
                    return response($resp['err'], 400);
                } else {
                    $transferred = $resp['v'];
                    $message .= __('wallet.transferred_to_wallet', ['amount' => $transferred, 'curr' => UserWallet::currencyName($currency)]).PHP_EOL;
                    $money_given = true;
                    DB::table('payout_transactions')
                        ->insert([
                            'user_id' => $user->id,
                            'hall_id' => UserWallet::currencyHallId($currency),
                            'date' => date('Y-m-d H:i:s'),
                            'sum' => $transferred,
                            'status_id' => PayoutTransaction::STATUS_REFERRAL_PAY,
                            'currency' => UserWallet::currencyName($currency),
                            'operation' => 'referral payout'
                        ]);
                }
            }
        }
        if (!$money_given) {
            return response(__('referral.not_enough_money'), 400);
        }

        return response($message);
    }

    private function getLayouts()
    {
        return empty(request()->get('layout', null)) ? [] : ['cabinet.index'];
    }


    public function renderPage(string $page, array $params = [])
    {
        if (request()->ajax()) {
            $this->renderAdaptive('cabinet.' . $page, $params, $this->getLayouts());
        } else {
            $this->renderAdaptive('landing.index', [], ['layouts.main']);
        }
        return $this->sendPreparedResponse();
    }
}
