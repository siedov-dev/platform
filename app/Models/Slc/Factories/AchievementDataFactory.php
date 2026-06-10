<?php

namespace App\Models\Slc\Factories;

use App\Models\Slc\Achievements\Data\SlcAchievementData;
use App\Models\Slc\Achievements\SlcAchievement;

interface AchievementDataFactory
{
    public function getData(SlcAchievement $achievement): SlcAchievementData;
}
