<?php

namespace App\Models\Slc\Achievements\Data;

use App\Helpers\UI;
use App\Models\Slc\Achievements\Data\SlcAchievementData;
use App\Models\Slc\Achievements\SlcAchievement;
use App\Models\Slc\Chest\ChestType;
use App\Models\Slc\Chest\SlcChestLog;
use App\Models\Slc\SlcLoginRewardsLog;
use App\Models\Slc\WheelSpinLog;
use App\MUser;
use App\UserWallet;

class AchievementChestsData implements SlcAchievementData
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
        if (empty($this->achievement->getStringParam())) {
            return SlcChestLog::getOpenChestsCount($user);
        }
        return SlcChestLog::getOpenChestsCount($user, ChestType::from($this->achievement->getStringParam()));
    }

    public function getRequiredParams(): array
    {
        return [
            'chest_type' => 'string_param',
        ];
    }

    public function getGoalString(MUser $user): string
    {
        if (empty($this->achievement->getStringParam())) {
            return __('slc.ach_chest_total_goal',
                [
                    'count' => $this->achievement->getTargetValue(),
                ]
            );
        }

        return __('slc.ach_chest_goal',
            [
                'count' => $this->achievement->getTargetValue(),
                'chest_type' => __('chests.'.ChestType::from($this->achievement->getStringParam())->name),
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
