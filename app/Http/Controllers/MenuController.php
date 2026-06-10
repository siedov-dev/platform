<?php

namespace App\Http\Controllers;

use App\Models\Rank\UserRankRewarded;
use App\Models\Slc\Achievements\SlcUserAchievement;
use App\Models\Slc\SlcCompletedTask;
use App\Models\Slc\SlcShopItem;
use App\Models\Slc\SlcTask;
use App\Models\Slc\SlcUserShopItem;
use App\Models\Slc\UserTasks\UserDailyTask;
use App\Models\Slc\WheelSpinLog;
use App\Models\UserValue;
use App\MUser;
use App\Services\Games\GameManagerApi;
use App\Services\KBonus\KBonus;
use Illuminate\Support\Facades\Auth;

class MenuController extends Controller
{
    public function updateMenu()
    {
        if (Auth::guest()) return [];
        $data = [];

        $user = MUser::getAuth();
        $slc_wallet = $user->getSlcWallet();

//        SlcTask::checkAllTasks($user);

        $data['slc_balance'] = $slc_wallet->getBalance();
        $data['new_achievement'] = SlcUserAchievement::newAchievementsCount($user);
        $data['rank_rewards'] = ($user->getRank()->getLevel() - UserRankRewarded::takenRewardsCount($user)) > 0?: 0;
        $data['completed_task'] = SlcCompletedTask::completedTaskCount($user)
            + UserDailyTask::todayCompletedCount($user)
            + ($user->getValue('login_reward_date', null) === now()->format('Y-m-d')? 0: 1);
        $data['new_shop_items'] = SlcShopItem::getNewItemsCount($user);
        $data['new_user_items'] = SlcUserShopItem::getNewItemsCount($user);
        $wheel_spins = $user->getValue('wheel_spins', 0);
        if (WheelSpinLog::where('created_at', '>=', now()->startOfDay())
                ->where('user_id', $user->getId())
                ->first() === null) $wheel_spins++;
        $data['can_spin'] = $wheel_spins;
        $data['has_new_bonus'] = $user->getRedisValue('has_new_bonus', false);
        $data['support_notify'] = $user->getValue('support_notify', false);
        return $data;
    }

    public function updateGamesMenu()
    {
        if (Auth::guest()) return [];
        $data = [];
        $user = MUser::getAuth();
        $data['active_bonus'] = false;
        $data['active_tournaments'] = false;
        $data['new_games'] = false;
        $ab = KBonus::getInstance()->getFirstActiveBonus($user->getId());
        if ($ab !== null && UserValue::getValue($user->getId(), "active_bonus") !== $ab->getId()) $data['active_bonus'] = true;
        $kgames = GameManagerApi::getInstance();
        $t_hash = $kgames->tournamentGamesHash();
        if ($t_hash !== "" && $t_hash !== $user->getValue('tnt_games_hash', '')) $data['active_tournaments'] = true;
        $new_games_hash = GameManagerApi::getInstance()->newGamesHash();
        if ($new_games_hash !== "" && $new_games_hash !== $user->getValue('new_games_hash', '')) $data['new_games'] = true;
        return $data;
    }
}
