<?php

namespace App\Models\Slc\TaskChecker;

use App\Models\Bonuses\UserBonus;
use App\Models\Slc\Achievements\SlcAchievement;
use App\Models\Slc\Achievements\SlcAchievementType;
use App\Models\Slc\BezdepItemConfig;
use App\Models\Slc\SlcCompletedTask;
use App\Models\Slc\SlcItem;
use App\Models\Slc\SlcItemType;
use App\Models\Slc\SlcTask;
use App\Models\Slc\SlcTaskStatus;
use App\Models\Slc\SlcUserShopItem;
use App\MUser;
use Illuminate\Support\Facades\Auth;

class FirstStepsChecker implements TaskChecker
{

    // if there's a user there was a registration
    public function checkComplete(MUser $user, SlcTask $task): bool
    {
//        SlcCompletedTask::setTaskStatus($user->getId(), $task->getType(), SlcTaskStatus::Completed);
//        return true;
        if (SlcAchievement::where('type', SlcAchievementType::FirstSteps)
            ->first()?->isRewarded($user->getId()) === true) {
            SlcCompletedTask::setTaskStatus($user->getId(), $task->getType(), SlcTaskStatus::Completed);
            return true;
        }
        return false;
    }

    public function getReward(SlcTask $task): SlcItem
    {
        /** @var BezdepItemConfig $params */
        $params = BezdepItemConfig::first();

        $reward = new SlcUserShopItem();
        $reward->setType(SlcItemType::Bezdep);

        $reward->setBonusType(UserBonus::TYPE_BEZDEP);
        $reward->setParams($params->getParams());
        $reward->setAllowedGames($params->getGames());
        $reward->setAmount(1);
        return $reward;
    }

    public function getCurrentValue(MUser $user, SlcTask $task): int
    {
        return 0;
    }

    public function getGoalValue(SlcTask $task): int
    {
        return 1;
    }
}
