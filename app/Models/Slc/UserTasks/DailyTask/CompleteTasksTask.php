<?php

namespace App\Models\Slc\UserTasks\DailyTask;

use App\Models\Slc\UserTasks\UserDailyTask;
use App\MUser;
use App\Services\KSlc\KSlc;

class CompleteTasksTask implements DailyTask
{
    public function __construct(private readonly MUser $user, private readonly UserDailyTask $dailyTask)
    {
    }

    public function getGoal(): string
    {
        $goal = __('tasks.complete_tasks', ['count' => $this->dailyTask->getTargetValue()]);
        return $goal;
    }

    public function checkTask(): bool
    {
        return $this->getCurrentValue() >= ($this->dailyTask->getTargetValue()?: 1);
    }

    public function getGoalValue(): int
    {
        return $this->dailyTask->getTargetValue();
    }

    public function getCurrentValue(): int
    {
        return KSlc::getInstance()->getDailyTaskCompletedAndRewardedCount($this->user->getId())['completed']?? 0;
//        return UserDailyTask::where('created_at', '>=', now()->startOfDay()->format('Y-m-d H:i:s'))
//            ->where('is_completed', true)
//            ->where('user_id', $this->user->getId())
//            ->count();
    }

    public function renderTaskItem(): string
    {
        return view('desktop.slc.daily-tasks.complete_tasks-task-item', [
            'task' => $this,
            'dailyTask' => $this->dailyTask,
            'is_completed' => $this->dailyTask->isCompleted(),
        ])->render();
    }

}
