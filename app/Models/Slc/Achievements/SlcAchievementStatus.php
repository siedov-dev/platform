<?php

namespace App\Models\Slc\Achievements;

/**
 * @deprecated
 */
enum SlcAchievementStatus: int
{
    case InProgress = 0;
    case Done = 1;
    case Rewarded = 2;
}
