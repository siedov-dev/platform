<?php

namespace App\Models\Slc\Factories;

use App\Models\Slc\Achievements\Data\AchievementBarrelBreakData;
use App\Models\Slc\Achievements\Data\AchievementBetMultiplierData;
use App\Models\Slc\Achievements\Data\AchievementBetsMadeData;
use App\Models\Slc\Achievements\Data\AchievementBezdepData;
use App\Models\Slc\Achievements\Data\AchievementBonusPayData;
use App\Models\Slc\Achievements\Data\AchievementCahbackActivateData;
use App\Models\Slc\Achievements\Data\AchievementCannonShotsData;
use App\Models\Slc\Achievements\Data\AchievementChestsData;
use App\Models\Slc\Achievements\Data\AchievementCrashWinsData;
use App\Models\Slc\Achievements\Data\AchievementDailyLoginData;
use App\Models\Slc\Achievements\Data\AchievementDailyTaskData;
use App\Models\Slc\Achievements\Data\AchievementDepositData;
use App\Models\Slc\Achievements\Data\AchievementFirstStepsData;
use App\Models\Slc\Achievements\Data\AchievementImproveBonusData;
use App\Models\Slc\Achievements\Data\AchievementPlayerData;
use App\Models\Slc\Achievements\Data\AchievementPromosActivatedData;
use App\Models\Slc\Achievements\Data\AchievementShopBuyData;
use App\Models\Slc\Achievements\Data\AchievementSlotmachineSpinData;
use App\Models\Slc\Achievements\Data\AchievementSpinnerData;
use App\Models\Slc\Achievements\Data\AchievementWheelSpinData;
use App\Models\Slc\Achievements\Data\SlcAchievementData;
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
            SlcAchievementType::ShopBuy => new AchievementShopBuyData($achievement),
            SlcAchievementType::BonusPay => new AchievementBonusPayData($achievement),
            SlcAchievementType::ImproveBonus => new AchievementImproveBonusData($achievement),
            SlcAchievementType::CrashWins => new AchievementCrashWinsData($achievement),
            SlcAchievementType::BetsMade => new AchievementBetsMadeData($achievement),
            SlcAchievementType::BetMultiplier => new AchievementBetMultiplierData($achievement),
            SlcAchievementType::PromosActivated => new AchievementPromosActivatedData($achievement),
            SlcAchievementType::CannonShots => new AchievementCannonShotsData($achievement),
            SlcAchievementType::BarrelBreak => new AchievementBarrelBreakData($achievement),
            SlcAchievementType::CashbackActivate => new AchievementCahbackActivateData($achievement),
            default => throw new \Exception('No achievement data class'),
        };
    }
}
