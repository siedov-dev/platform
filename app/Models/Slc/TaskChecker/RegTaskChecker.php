<?php

namespace App\Models\Slc\TaskChecker;

use App\Models\Slc\SlcCompletedTask;
use App\Models\Slc\SlcItem;
use App\Models\Slc\SlcItemType;
use App\Models\Slc\SlcTask;
use App\Models\Slc\SlcTaskStatus;
use App\Models\Slc\SlcUserShopItem;
use App\MUser;

class RegTaskChecker implements TaskChecker
{

    // if there's a user there was a registration
    public function checkComplete(MUser $user, SlcTask $task): bool
    {
        SlcCompletedTask::setTaskStatus($user->getId(), $task->getType(), SlcTaskStatus::Completed);
        return true;
    }

    public function getReward(SlcTask $task): SlcItem
    {
        $reward = new SlcUserShopItem();
        $reward->setType(SlcItemType::Slc);
        $reward->setAmount($task->getReward());
        return $reward;
    }

    public function getCurrentValue(MUser $user, SlcTask $task): int
    {
        return 1;
    }

    public function getGoalValue(SlcTask $task): int
    {
        return 1;
    }
}
