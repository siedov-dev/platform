<?php

namespace App\Models\Slc\UserTasks\DailyTask;

use App\GamesProviders\Game;
use App\Models\GameTransaction;
use App\Models\Slc\SlcInventoryLog;
use App\Models\Slc\UserTasks\UserDailyTask;
use App\MUser;
use App\Services\KSlc\KSlc;

class BuyItemTask implements DailyTask
{
    public function __construct(private readonly MUser $user, private readonly UserDailyTask $dailyTask)
    {
    }

    public function getGoal(): string
    {
        $goal = __('tasks.'.$this->dailyTask->getType()->value);
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
//        return KSlc::getInstance()->getDailyTaskValues($this->user->getId(), $this->dailyTask->getId())['goal_value']?? 1;
    }

    public function getCurrentValue(): int
    {
        return KSlc::getInstance()->getDailyTaskValues($this->user->getId(), $this->dailyTask->getId())['current_value']?? 0;
//        $tr = SlcInventoryLog::where('user_id', $this->user->getId())
//            ->where('source', 'shop')
//            ->where('created_at', '>=', now()->startOfDay()->format('Y-m-d H:i:s'))
//            ->get();
//
//        return $tr->count();
    }

    public function renderTaskItem(): string
    {
        return view('desktop.slc.daily-tasks.buy-task-item', [
            'task' => $this,
            'dailyTask' => $this->dailyTask,
            'is_completed' => $this->dailyTask->isCompleted(),
        ])->render();
    }

}
