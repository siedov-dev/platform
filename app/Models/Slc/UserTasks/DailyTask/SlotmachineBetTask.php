<?php

namespace App\Models\Slc\UserTasks\DailyTask;

use App\Models\Slc\UserTasks\UserDailyTask;
use App\MUser;
use App\Services\KSlc\KSlc;

class SlotmachineBetTask implements DailyTask
{
    public function __construct(private readonly MUser $user, private readonly UserDailyTask $dailyTask)
    {
    }

    public function getGoal(): string
    {
        $goal = __('tasks.'.$this->dailyTask->getType()->value);
        if ($this->dailyTask->getTargetValue() > 0) $goal .= ' ' . $this->dailyTask->getTargetValue() . ' SLC';
        return $goal;
    }

    public function checkTask(): bool
    {
        return KSlc::getInstance()->checkDailyTaskCompleted($this->user->getId(), $this->dailyTask->getId());
//        return $this->getCurrentValue() >= ($this->dailyTask->getTargetValue()?: 1);
    }

    public function getGoalValue(): int
    {
        return $this->dailyTask->getTargetValue();
    }

    public function getCurrentValue(): int
    {
        return KSlc::getInstance()->getDailyTaskValues($this->user->getId(), $this->dailyTask->getId())['current_value']?? 0;
//        $tr = SlcSlotMachineLog::where('user_id', $this->user->getId())
//            ->where('created_at', '>=', now()->startOfDay()->format('Y-m-d H:i:s'));
//        return $tr->count() * SlcConfig::getBetCost();
    }

    public function renderTaskItem(): string
    {
        return view('desktop.slc.daily-tasks.slotmachine-task-item', [
            'task' => $this,
            'is_completed' => $this->dailyTask->isCompleted(),
            'dailyTask' => $this->dailyTask,
        ])->render();
    }

}
