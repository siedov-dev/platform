<?php

namespace App\Models\Slc\UserTasks\DailyTask;

use App\Models\Slc\UserTasks\UserDailyTask;
use App\MUser;
use App\Services\KSlc\KSlc;

class WheelSpinTask implements DailyTask
{
    public function __construct(private readonly MUser $user, private readonly UserDailyTask $dailyTask)
    {
    }

    public function getGoal(): string
    {
        return __('tasks.'.$this->dailyTask->getType()->value);
    }

    public function checkTask(): bool
    {
//        return KSlc::getInstance()->checkDailyTaskCompleted($this->user->getId(), $this->dailyTask->getId());
        return $this->getCurrentValue() >= $this->dailyTask->getTargetValue();
    }

    public function getGoalValue(): int
    {
        return $this->dailyTask->getTargetValue();
    }

    public function getCurrentValue(): int
    {
        return KSlc::getInstance()->getDailyTaskValues($this->user->getId(), $this->dailyTask->getId())['current_value']?? 0;
//        $today_spins = WheelSpinLog::where('created_at', '>=', now()->startOfDay())
//            ->where('user_id', $this->user->getId())
//            ->get();
//        return $today_spins->count();
    }

    public function renderTaskItem(): string
    {
        return view('desktop.slc.daily-tasks.wheel-task-item', [
            'task' => $this,
            'is_completed' => $this->dailyTask->isCompleted(),
            'dailyTask' => $this->dailyTask,
        ])->render();
    }
}
