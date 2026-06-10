<?php

namespace App\Models\Slc\UserTasks;

enum UserTaskGroupType: int
{
    case Daily = 0;

    public function getCrmName(): string
    {
        return match ($this) {
            self::Daily => "Ежедневная задача",
        };
    }
}
