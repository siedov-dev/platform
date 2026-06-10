<?php

namespace App\Models\Slc\Achievements\Data;

use App\Models\Slc\Achievements\SlcAchievement;
use App\Models\Slc\Achievements\SlcAchievementType;

class AchievementDataConcreteFactory implements AchievementDataFactory
{
    public function getData(SlcAchievement $achievement): SlcAchievementData
    {
        return match ($achievement->getAchievementType()) {
            SlcAchievementType::Spinner => new AchievementSpinnerData($achievement),
            SlcAchievementType::Player => new AchievementPlayerData($achievement),
            SlcAchievementType::DepMaster => new AchievementDepositData($achievement),
            SlcAchievementType::FirstSteps => new AchievementFirstStepsData($achievement),
            SlcAchievementType::Bezdep => new AchievementBezdepData($achievement),
            SlcAchievementType::DailyLogin => new AchievementDailyLoginData($achievement),
            SlcAchievementType::WheelSpin => new AchievementWheelSpinData($achievement),
            SlcAchievementType::SlotMachineSpin => new AchievementSlotmachineSpinData($achievement),
            SlcAchievementType::DailyTaskReward => new AchievementDailyTaskData($achievement),
            SlcAchievementType::OpenChests => new AchievementChestsData($achievement),
            default => throw new \Exception('No achievement data class'),
        };
    }
}
