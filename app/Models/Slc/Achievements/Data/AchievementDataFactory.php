<?php

namespace App\Models\Slc\Achievements\Data;

use App\Models\Slc\Achievements\SlcAchievement;

interface AchievementDataFactory
{
    public function getData(SlcAchievement $achievement): SlcAchievementData;
}
