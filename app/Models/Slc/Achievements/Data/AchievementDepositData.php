<?php

namespace App\Models\Slc\Achievements\Data;

use App\Helpers\UI;
use App\Models\Slc\Achievements\Data\SlcAchievementData;
use App\Models\Slc\Achievements\SlcAchievement;
use App\MUser;
use App\UserWallet;

class AchievementDepositData implements SlcAchievementData
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
        return $user->getDepositsCount(
            min_sum: $this->achievement->getFloatParam(),
            currency_id: $this->achievement->getCurrency(),
        );
    }

    public function getRequiredParams(): array
    {
        return ['min_dep' => 'float_param'];
    }

    public function getGoalString(MUser $user): string
    {
        if ($this->achievement->getFloatParam() > 0)
        return __('slc.ach_dep_goal',
            [
                'count' => $this->achievement->getTargetValue(),
                'curr_icon' => UserWallet::currencySymbol($this->achievement->getCurrency()),
                'value' => UI::prettyAmount($this->achievement->getFloatParam()),
            ]
        );
        return __('slc.ach_dep_goal_all',
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
