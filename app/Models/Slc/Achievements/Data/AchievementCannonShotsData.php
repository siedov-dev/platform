<?php

namespace App\Models\Slc\Achievements\Data;

use App\Helpers\UI;
use App\Models\Slc\Achievements\SlcAchievement;
use App\Models\Slc\SlcBarrelLog;
use App\MUser;

class AchievementCannonShotsData implements SlcAchievementData
{
    private SlcAchievement $achievement;

    /**
     * @param SlcAchievement $achievement
     */
    public function __construct(SlcAchievement $achievement)
    {
        $this->achievement = $achievement;
    }

    public function getCurrentValue(MUser $user): int
    {
        return SlcBarrelLog::where('user_id', $user->getId())
            ->count();
    }

    public function getRequiredParams(): array
    {
        return [];
    }

    public function getGoalString(MUser $user): string
    {
        return __('slc.ach_cannon_shot_goal',
            [
                'count' => $this->achievement->getTargetValue(),
            ]
        );
    }

    public function getValueHint(MUser $user): string
    {
        return $this->getCurrentValue($user);
    }

    public function getCurrentPercent(MUser $user): int
    {
        $target = $this->achievement->getTargetValue();
        $current = $this->getCurrentValue($user);
        if ($target === 0 || $current > $target) return 100;
        return floor($current / $target * 100) ;
    }
}
