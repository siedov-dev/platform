<?php

namespace App\Models\Slc\Achievements\Data;

use App\Helpers\UI;
use App\Models\Slc\Achievements\Data\SlcAchievementData;
use App\Models\Slc\Achievements\SlcAchievement;
use App\Models\Slc\SlcInventoryLog;
use App\Models\Slc\SlcLoginRewardsLog;
use App\Models\Slc\WheelSpinLog;
use App\MUser;
use App\UserWallet;

class AchievementShopBuyData implements SlcAchievementData
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
        return SlcInventoryLog::where('user_id', $user->getId())
            ->where('source', 'shop')
            ->count();
    }

    public function getRequiredParams(): array
    {
        return [];
    }

    public function getGoalString(MUser $user): string
    {
        return __('slc.ach_shop_buy_goal',
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
