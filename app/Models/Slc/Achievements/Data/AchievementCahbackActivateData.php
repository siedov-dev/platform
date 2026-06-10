<?php

namespace App\Models\Slc\Achievements\Data;

use App\Helpers\UI;
use App\Models\Bonuses\UserBonus;
use App\Models\BonusStatistic;
use App\Models\Slc\Achievements\Data\SlcAchievementData;
use App\Models\Slc\Achievements\SlcAchievement;
use App\Models\Slc\Chest\ChestType;
use App\Models\Slc\Chest\SlcChestLog;
use App\Models\Slc\SlcLoginRewardsLog;
use App\Models\Slc\WheelSpinLog;
use App\MUser;
use App\UserWallet;

class AchievementCahbackActivateData implements SlcAchievementData
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
        return BonusStatistic::where('user_id', $user->getId())
            ->where('bonus_type', UserBonus::TYPE_CASHBACK_MONEY)
            ->where('action', BonusStatistic::ACTION_ACTIVATE)
            ->count();
    }

    public function getRequiredParams(): array
    {
        return [];
    }

    public function getGoalString(MUser $user): string
    {
        return __('slc.ach_cashback_activate_goal',
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
