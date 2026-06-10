<?php

namespace App\Models\Slc;

enum SlcTaskStatus: int
{
    case NotCompleted = -1; // не завершена
    case Completed = 0; // завершена
    case Rewarded = 1; // награда получена

    public function styleName(): string
    {
        return match ($this) {
            SlcTaskStatus::NotCompleted => 'not-completed',
            SlcTaskStatus::Completed => 'done',
            SlcTaskStatus::Rewarded => 'done received',
        };
    }
}
