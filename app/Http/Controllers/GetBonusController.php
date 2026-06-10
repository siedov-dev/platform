<?php
/**
 * Created by PhpStorm.
 * User: george
 * Date: 21.02.2019
 * Time: 16:21
 */

namespace App\Http\Controllers;

use App\GamesProviders\GamesManager;
use App\Helpers\Device;
use App\Helpers\Hlog;
use App\Helpers\Localization;
use App\Models\Bonuses\KUserBonus;
use App\Services\KBonus\KBonus;
use App\Services\KSocket\KSocket;
use App\Services\Users\UserManager;
use App\Models\Bonuses\Segments\BonusSegment;
use App\Models\BonusPatterns;
use App\Models\PersonalData;
use App\Models\Popups\UserPopup;
use App\MUser;
use App\Wallet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;


class GetBonusController extends Controller
{
    /**
     * @param $locale
     * @param $id
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     * @throws \Exception
     * @deprecated
     */
    public function transferBonusMoney($locale, $id)
    {
        $bonus = KBonus::getInstance()->getBonus($id);
//        $bonus = UserBonus::where('id', $id)->where('user_id', Auth::user()->id)->first();
//        $bonus = UserBonus::convertByType($bonus);

        /** @var MUser $user */
        $user = MUser::getAuth();

        if ($bonus === null || $user === null) {
            return response(__('bonus.not_found'), 400);
        }

        $money = $bonus->transferBonusMoney();
        if ($money !== false) {
            return response(floor($money) . $bonus->getCurrencyName() . ' ' . __('bonus.transferred_to_balance'));
        }

        return response(__('bonus.error_retry'), 400);
    }


    public function activateBonus($locale, $bonus_id, $option = null)
    {
        Log::info('Activating bonus #' . $bonus_id);
        Log::info('Activating option:' . $option);
        // закрываем игровую сессию
        EmulatorController::transferMoneyBack();
        $forceActivate = \request()->get('force');
        Log::info('Activating force:' . $forceActivate);
        Log::info('Activating request:' . var_export(\request()->all(), true));
        $user = MUser::getAuth();
        // Check already activated bonuses.
        $currentActiveBonus = KUserBonus::findFirstActiveBonus($user->getId());

        if ($currentActiveBonus?->getId() === intval($bonus_id)) {
            return response(__('bonus.activated'));
        }

        if ($currentActiveBonus) {

            if ($forceActivate) {
                try {
                    if ($currentActiveBonus->canPause()) {
                        $currentActiveBonus->pause();
                    } else {
                        $currentActiveBonus->cancelBonus();
                    }
                } catch (\Exception $e) {
                    Hlog::logException($e);
                    return response($e->getMessage(), 400);
                }
                $currentActiveBonus = KUserBonus::findFirstActiveBonus($user->getId());
            }

            if ($currentActiveBonus) {
                return response(view('common.blocks.forceBonusActivation', [
                    'bonus_id' => $bonus_id,
                    'name' => $currentActiveBonus->getTitle(),
                    'option' => $option,
                    'active' => $currentActiveBonus,
                    'canPause' => $currentActiveBonus->canPause(),
                ])->render(), 200, ['showModal' => true]);
//              return response(__('bonus.have_incomplete_bonus'), 400);
            }
        }

        $waitingDepBonus = KUserBonus::getWaitingDeposit($user);

        if ($waitingDepBonus?->getId() === intval($bonus_id)) {
            return response(__('bonus.activated'));
        }

        if ($waitingDepBonus) {

            if ($forceActivate) {
                try {
                    if ($waitingDepBonus->canPause()) {
                        $waitingDepBonus->pause();
                    } else {
                        $waitingDepBonus->cancelBonus();
                    }
                } catch (\Exception $e) {
                    Hlog::logException($e);
                    return response($e->getMessage(), 400);
                }
                $waitingDepBonus = KUserBonus::getWaitingDeposit($user);
            }

            if ($waitingDepBonus) {
                return response(view('common.blocks.forceBonusActivation',
                    [
                        'bonus_id' => $bonus_id,
                        'name' => $waitingDepBonus->getTitle(),
                        'option' => $option,
                        'active' => $waitingDepBonus,
                        'canPause' => $waitingDepBonus->canPause(),
                    ])->render(),
                    200, ['showModal' => true]);
            }
        }

        if (preg_match('#^(\w*)_(\d*)$#', $bonus_id, $match)) {
            $bonus = null;
            if ($match[1] === 'ba') {
                $ba_id = $match[2];
                $bonus = KBonus::getInstance()->createFromBonusAvailable($ba_id, $user->getId(), $user->getCurrency());
                // segment by game name
                if (!empty($bonus) && !empty($bonus->game_id) && !empty($bonus->game_provider)) {
                    $game = GamesManager::getProvider($bonus->game_provider)?->getGame('', $bonus->game_id, true);
                    if ($game) {
                        KBonus::getInstance()->addBonusToSegmentByName($bonus->getId(), $game->getName());
                    }
                }
            }
        } else {
            $bonus = KBonus::getInstance()->getBonus($bonus_id);
            if ($bonus?->getUserId() !== $user->getId()) {
                Log::error('wrong bonus for activation #' . $bonus_id);
                $bonus = null;
            }
        }

        if ($bonus) {

            if ($option !== null) {
                $activated = $bonus->ActivateByMenuOption($option);
            } else {
                $activated = $bonus->activate();
            }
            if (!$activated) {
                Log::error('cant activate bonus #' . $bonus->id);

                if (!empty($bonus->getError())) {
                    return response($bonus->getError(), 400);
                } else {
                    return response(__('bonus.activation_error'), 400);
                }
            }

            KSocket::getInstance()->sendEvent($user->getId(), "bonus:changed");

            if ($bonus->type === KUserBonus::TYPE_WELCOME_BONUS_PACK && $bonus->tries < 4) {

                $user_bonuses = KBonus::getInstance()->getUserBonuses($user->getId(), $user->getCurrency());
                $reg_ptn_ids = BonusPatterns::getRegFsPatternIds($bonus->curr_name);
                $users_fs_bonuses = [];
                foreach ($user_bonuses as $ubonus) {
                    if (in_array($ubonus->pattern_id, $reg_ptn_ids)) $users_fs_bonuses[$ubonus->pattern_id] = $ubonus;
                }
                $completed_bonuses = KBonus::getInstance()->getUserCompletedBonuses($user->getId(), $user->getCurrency());
                foreach ($completed_bonuses as $user_bonus) {
                    if (in_array($user_bonus->pattern_id, BonusPatterns::getRegFsPatternIds(Wallet::currencyName($bonus->currency)))) {
                        $users_fs_bonuses[] = $user_bonus;
                    }
                }
                krsort($users_fs_bonuses);
                $users_fs_bonuses = array_values($users_fs_bonuses);

//                $users_fs_bonuses = \App\Models\Bonuses\UserBonus::where('user_id', $user->getId())
//                    ->whereIn('pattern_id', BonusPatterns::getRegFsPatternIds($bonus->curr_name))
//                    ->orderBy('pattern_id')
//                    ->get();

                return response(view('common.bonuses.wb-pack-deposit', [
                    'user' => $user,
                    'bonus' => $bonus,
                    'users_fs_bonuses' => $users_fs_bonuses,
                ])->render(),
                    200,
                    ['showModal' => true]
                );
            }

            if ($bonus->isPaymentRequired()) {
                return response(__('bonus.activated'), 200, ['show_deposit' => true, 'deposit_value' => \request()->input('deposit', 0)]);
            }

        }
        UserManager::addEvent($user->getId(), 'wallet:update_balances', []);

        return response(__('bonus.activated'));
    }

    public function resumeBonus($locale, $bonus_id)
    {
        Log::info('resumeBonus bonus #' . $bonus_id);
        // закрываем игровую сессию
        EmulatorController::transferMoneyBack();
        $forceActivate = \request()->get('force');
        Log::info('resumeBonus force:' . $forceActivate);
        Log::info('resumeBonus request:' . var_export(\request()->all(), true));
        $user = MUser::getAuth();
        // Check already activated bonuses.
        $currentActiveBonus = KUserBonus::findFirstActiveBonus($user->getId());

        if ($currentActiveBonus?->getId() === intval($bonus_id)) {
            return response(__('bonus.activated'));
        }

        if ($currentActiveBonus) {

            if ($forceActivate) {
                try {
                    if ($currentActiveBonus->canPause()) {
                        $currentActiveBonus->pause();
                    } else {
                        $currentActiveBonus->cancelBonus();
                    }
                } catch (\Exception $e) {
                    Hlog::logException($e);
                    return response($e->getMessage(), 400);
                }
                $currentActiveBonus = KUserBonus::findFirstActiveBonus($user->getId());
            }

            if ($currentActiveBonus) {
                return response(view('common.blocks.forceBonusResume', [
                    'bonus_id' => $bonus_id,
                    'name' => $currentActiveBonus->getTitle(),
                    'active' => $currentActiveBonus,
                    'canPause' => $currentActiveBonus->canPause(),
                ])->render(), 200, ['showModal' => true]);
//              return response(__('bonus.have_incomplete_bonus'), 400);
            }
        }

        $waitingDepBonus = KUserBonus::getWaitingDeposit($user);

        if ($waitingDepBonus?->getId() === intval($bonus_id)) {
            return response(__('bonus.activated'));
        }

        if ($waitingDepBonus) {

            if ($forceActivate) {
                try {
                    if ($waitingDepBonus->canPause()) {
                        $waitingDepBonus->pause();
                    } else {
                        $waitingDepBonus->cancelBonus();
                    }
                } catch (\Exception $e) {
                    Hlog::logException($e);
                    return response($e->getMessage(), 400);
                }
                $waitingDepBonus = KUserBonus::getWaitingDeposit($user);
            }

            if ($waitingDepBonus) {
                return response(view('common.blocks.forceBonusResume',
                    [
                        'bonus_id' => $bonus_id,
                        'name' => $waitingDepBonus->getTitle(),
                        'active' => $waitingDepBonus,
                        'canPause' => $waitingDepBonus->canPause(),
                    ])->render(),
                    200, ['showModal' => true]);
            }
        }


        $bonus = KBonus::getInstance()->getBonus($bonus_id);
        if ($bonus?->getUserId() !== $user->getId()) {
            Log::error('wrong bonus for activation #' . $bonus_id);
            $bonus = null;
        }

        if ($bonus) {

            $activated = $bonus->resume();
            if (!$activated) {
                Log::error('cant resume bonus #' . $bonus->id);

                if (!empty($bonus->getError())) {
                    return response($bonus->getError(), 400);
                } else {
                    return response(__('bonus.activation_error'), 400);
                }
            }

            KSocket::getInstance()->sendEvent($user->getId(), "bonus:changed");

            if ($bonus->isPaymentRequired()) {
                return response(__('bonus.activated'), 200, ['show_deposit' => true, 'deposit_value' => \request()->input('deposit', 0)]);
            }
        }

        return response(__('bonus.activated'));
    }

    public function pauseBonus($locale, $bonus_id)
    {
        // закрываем игровую сессию
        EmulatorController::transferMoneyBack();
        Log::info('Pause bonus request:' . var_export(\request()->all(), true));
        $user = MUser::getAuth();


        $bonus = KBonus::getInstance()->getBonus($bonus_id);
        if ($bonus?->getUserId() !== $user->getId()) {
            Log::error('wrong bonus for pause #' . $bonus_id);
            $bonus = null;
        }

        if ($bonus) {

            $paused = $bonus->pause();
            if (!$paused) {
                Log::error('can`t pause bonus #' . $bonus->id);

                if (!empty($bonus->getError())) {
                    return response($bonus->getError(), 400);
                } else {
                    return response(__('bonus.activation_error'), 400);
                }
            }

            KSocket::getInstance()->sendEvent($user->getId(), "bonus:changed");
        }
        UserManager::addEvent($user->getId(), 'wallet:update_balances', []);

        return response(__('bonus.paused'));
    }

//    public function bonusPay(Request $request)
//    {
//        Log::info('Pay bonus data' . var_export($request->all(), true));
//        $bonus_id = $request->input('bonus_id');
//
//        $bonus = UserBonus::where('id', $bonus_id)
//            ->where('user_id', Auth::id())
//            ->where('status', UserBonus::STATUS_BUY_REQUIRED)
//            ->first();
//
//        if ($bonus) {
//            $bonus = UserBonus::convertByType($bonus);
//            /** @var MUser $user */
//            $user = MUser::findOrFail($bonus->getUserId());
//
//            DB::beginTransaction();
//            try {
//                $wallet = $user->getWallet($bonus->getCurrency());
//                if ($wallet->getBalance() >= $bonus->getRequiredSum()) {
//                    if ($bonus->realPayRequired() !== false) {
//                        $wallet->remMoney($bonus->getRequiredSum());
//                    } else {
//                        return response(__('bonus.activation_error'), 400);
//                    }
//                } else {
//                    return response(__('bonus.no_money_to_activate'), 400);
//                }
//                DB::commit();
//            } catch (\Exception $e) {
//                DB::rollBack();
//                Hlog::logException($e);
//                return response($e->getMessage(), 400);
//            }
//        } else {
//            return response('Bonus not found', 400);
//        }
//        UserManager::addEvent($user->getId(), "bonus:changed");
//        return response('', 200);
//    }

    public function dropBonus(Request $request)
    {
        Log::info('complete bonus:' . var_export($request->all(), true));
        $bonus_id = $request->input('bonus_id');

        $bonus = KBonus::getInstance()->dropBonus($bonus_id);

        if (!$bonus) return response('Bonus not found', 400);
        return response('', 200);
    }

    public function activateByCrm(Request $request)
    {
        $key = 'bonus128keySzx';

        Log::info('Crm activating bonus');
        Log::info(var_export($request->all(), true));
        $data = $request->all();

        $hash = hash('sha256', $data['bonus'] . $data['user_id'] . $data['dt'] . $key);
        if ($hash !== $data['hash']) {
            Log::error('wrong hash');
            return response('wrong hash', 500);
        }

        $bonus = KBonus::getInstance()->getBonus($data['bonus']);;
        if ($bonus === null) return response('bonus not found', 404);
        /** @var MUser $user */
        $user = MUser::findOrFail($request->user_id);
        Auth::login($user);
        Localization::setAppLocale($user->getLocale());
        KSocket::getInstance()->sendEvent($user->getId(), "bonus:changed");
        UserManager::addEvent($user->getId(), 'wallet:update_balances', []);
        return $this->activateBonus($bonus->id, $request->option);
    }


    public function cancelBonus($locale, $bonus_id)
    {
        // закрываем игровую сессию
        EmulatorController::transferMoneyBack();
        if (preg_match('#^(\w*)_(\d*)$#', $bonus_id, $match)) {
            $bonus = null;
            if ($match[1] === 'ba') {
                $ba_id = $match[2];
                KBonus::getInstance()->cancelFromBonusAvailable($ba_id, Auth::id());
            }
        } else {
            /** @var KUserBonus $bonus */
            $bonus = KBonus::getInstance()->getBonus($bonus_id);
        }
        if ($bonus) {
            try {
                $bonus->cancelBonus();
            } catch (\Exception $e) {
                return response($e->getMessage(), 400);
            }
            KSocket::getInstance()->sendEvent($bonus->getUserId(), "bonus:changed");
            UserManager::addEvent($bonus->getUserId(), 'wallet:update_balances', []);
        }

        return response('');
    }

    public static function processTvBetSchedule()
    {
        $bs = DB::table('bonus_schedule')
            ->where('status', 0)
            ->where('system_type', 'tvbet')
            ->where('start_time', '<=', date('Y-m-d H:i:s'))
            ->first();

        if ($bs) {

            Log::info('SCHEDULE:' . 'starting promocodes');
            DB::table('bonus_schedule')
                ->where('id', $bs->id)
                ->update(['status' => 1]);

            Log::info('SCHEDULE: userlist ' . $bs->user_list);

            DB::connection("cms")->table("promocodes")->insert([
                'name' => $bs->promocode,
                'val' => $bs->bonus_sum,
                'expired_in' => $bs->end_time,
                'assign' => $bs->user_list,
            ]);
        }
    }

    /**
     * clears bonuses older than $days_old days
     * @param int $days_old
     */
    public static function clearBonuses(int $days_old = 7)
    {
//        UserBonus::where('active_to', '<', now()->subDays(30)->format('Y-m-d H:i:s'))->limit(2000)
//            ->delete();

//        BonusStatistic::where('created_at', '<', now()->subMonths(6)->format('Y-m-d H:i:s'))->limit(2000)
//            ->delete();
    }

    public static function processBirthdayBonuses()
    {
        Log::info('checking for birthdays');
        $birthdays = PersonalData::where('status', PersonalData::STATUS_CONFIRMED)
            ->where('birthday', 'like', '%-' . now()->format('m-d'))
            ->get();

        /** @var BonusPatterns $bonus_ptn */
        $bonus_ptn = BonusPatterns::find(BonusPatterns::PTN_BIRTHDAY);

        if ($bonus_ptn === null) {
            Log::error('birthday bonus pattern not found');
            return;
        }

        if ($bonus_ptn->isDisabled()) {
            Log::info('birthday bonus is disabled');
            return;
        }

        foreach ($birthdays as $personal_data) {
            /** @var MUser $user */
            $user = $personal_data->user;
            if ($user !== null) {
                if ($user->getDepositsCount(now()->subDays(30)->format('Y-m-d H:i:s')) === 0) {
                    Log::info('user has birthday but has not made deposits in 30 days #' . $user->id);
                    continue;
                }
                Log::info('creating birthday bonus for user #' . $user->id);
                Localization::setAppLocale($user->getLocale());
                $user->notify(__('bonus.birthday'), __('notify.birthday_bonus'));
                $bonus = $bonus_ptn->createForUser($user);
                KBonus::getInstance()->addBonusToSegmentByName($bonus->getId(), BonusSegment::BIRTHDAY);

                UserPopup::showBirthdayPopup($user);
            }
        }
    }

    public function welcomeBonusDeposit(Request $request)
    {
        $user = MUser::getAuth();

        $bonus = KBonus::getInstance()->getBonus($request->input('bonus_id'));
        if ($bonus === null) {
            return response('No bonus found', 404);
        }
        if ($bonus->type === KUserBonus::TYPE_PS_SFS_DYNAMIC) {
            return $this->viewOrJson(Device::detect() . '.popups.fdep', [
                'user' => $user,
                'bonus' => $bonus,
            ]);
        }

        $user_bonuses = KBonus::getInstance()->getUserBonuses($user->getId(), $user->getCurrency());
        $users_fs_bonuses = [];
        foreach ($user_bonuses as $user_bonus) {
            if (in_array($user_bonus->pattern_id, BonusPatterns::getRegFsPatternIds(Wallet::currencyName($bonus->currency)))) {
                $users_fs_bonuses[] = $user_bonus;
            }
        }
        $completed_bonuses = KBonus::getInstance()->getUserCompletedBonuses($user->getId(), $user->getCurrency());
        foreach ($completed_bonuses as $user_bonus) {
            if (in_array($user_bonus->pattern_id, BonusPatterns::getRegFsPatternIds(Wallet::currencyName($bonus->currency)))) {
                $users_fs_bonuses[] = $user_bonus;
            }
        }

        return $this->viewOrJson('common.bonuses.wb-pack-deposit', [
            'user' => $user,
            'bonus' => $bonus,
            'users_fs_bonuses' => $users_fs_bonuses,
        ]);
    }

    public function welcomeBonusNonDep(Request $request)
    {
        $user = MUser::getAuth();
        $bonus = KBonus::getInstance()->getBonus($request->input('bonus_id'));
        if ($bonus === null) {
            return response('No bonus found', 404);
        }

        $user_bonuses = KBonus::getInstance()->getUserBonuses($user->getId(), $user->getCurrency());
        $users_fd_bonuses = [];
        foreach ($user_bonuses as $user_bonus) {
            if (in_array($user_bonus->pattern_id, BonusPatterns::getRegPatternIds(Wallet::currencyName($bonus->currency)))) {
                $users_fd_bonuses[] = $user_bonus;
            }
        }

        return $this->viewOrJson('common.bonuses.wb-fs-play-dialog', [
            'user' => $user,
            'bonus' => $bonus,
            'users_fd_bonuses' => $users_fd_bonuses,
        ]);
    }

    public function wbConditions(Request $request)
    {
        $user = MUser::getAuth();
        $bonus = KBonus::getInstance()->getBonus($request->input('bonus_id'));
        if ($bonus === null) {
            return response('No bonus found', 404);
        }

        $user_bonuses = KBonus::getInstance()->getUserBonuses($user->getId(), $user->getCurrency());
        $users_fd_bonuses = [];
        foreach ($user_bonuses as $user_bonus) {
            if (in_array($user_bonus->pattern_id, BonusPatterns::getRegPatternIds(Wallet::currencyName($bonus->currency)))) {
                $users_fd_bonuses[] = $user_bonus;
            }
        }

        return $this->viewOrJson('common.bonuses.wb-conditions', [
            'user' => $user,
            'bonus' => $bonus,
            'users_fd_bonuses' => $users_fd_bonuses,
        ]);
    }
}
