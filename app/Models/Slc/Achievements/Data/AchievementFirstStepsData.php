<?php

namespace App\Models\Slc\Achievements\Data;

use App\Models\Slc\Achievements\SlcAchievement;
use App\Models\Slc\SlcTaskType;
use App\MUser;
use App\Services\KSlc\KSlc;

class AchievementFirstStepsData implements SlcAchievementData
{
    private SlcAchievement $achievement;
    /**
     * @param SlcAchievement $user
     */
    public function __construct(SlcAchievement $achievement)
    {
        $this->achievement = $achievement;
    }

    public function getCurrentValue(MUser $user): int
    {
        return KSlc::getInstance()->getStartTaskRewardedCount($user->getId());
//        return SlcCompletedTask::where('user_id', $user->getId())->where('status', SlcTaskStatus::Rewarded)->count();
    }

    public function getRequiredParams(): array
    {
        return [];
    }

    public function getGoalString(MUser $user): string
    {
        $goal = __('slc.ach_first_steps_goal',
            [
                'count' => $this->tasksRequired(),
            ]
        );

        return $goal;
    }

    public function getValueHint(MUser $user): string
    {
        return $this->getCurrentValue($user);
    }

    public function getCurrentPercent(MUser $user): int
    {
        $target = $this->tasksRequired();
        $current = $this->getCurrentValue($user);
        if ($target === 0 || $current > $target) return 100;
        return floor($current / $target * 100) ;
    }

    private function tasksRequired(): int
    {
        return count(SlcTaskType::cases()) -1;
    }
}
