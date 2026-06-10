<?php

namespace App\Models\Slc\UserTasks\DailyTask;

use App\Models\Slc\UserTasks\UserDailyTask;
use App\MUser;
use App\Services\KSlc\KSlc;

class DepositTask implements DailyTask
{
    public function __construct(private readonly MUser $user, private readonly UserDailyTask $dailyTask)
    {
    }

    public function getGoal(): string
    {
        $goal = __('tasks.' . $this->dailyTask->getType()->value);
        if ($this->dailyTask->getFloatParam() > 0) $goal .= ' ' . $this->dailyTask->getFloatParam() . '+';
        $goal .= ' ' . $this->dailyTask->getCurrency();
        return $goal;
    }

    public function checkTask(): bool
    {
        return KSlc::getInstance()->checkDailyTaskCompleted($this->user->getId(), $this->dailyTask->getId());
//        return ($this->getCurrentValue() >= ($this->dailyTask->getTargetValue() ?: 1));
    }

    public function getGoalValue(): int
    {
        return KSlc::getInstance()->getDailyTaskValues($this->user->getId(), $this->dailyTask->getId())['goal_value']?? 1;
    }

    public function getCurrentValue(): int
    {
        return KSlc::getInstance()->getDailyTaskValues($this->user->getId(), $this->dailyTask->getId())['current_value']?? 0;
//        $query = PayoutTransaction::where('user_id', $this->user->getId())
//            ->whereIn('status_id', PayoutTransaction::getIncomingStatuses());
//        $query->where('date', '>=', now()->startOfDay()->format('Y-m-d H:i:s'));
//
//        if (!empty($this->dailyTask->getCurrency())) {
//            $query->where('currency', $this->dailyTask->getCurrency());
//        }
//
//        if ($this->dailyTask->getFloatParam() > 0) {
//            $query->where('sum', '>=', $this->dailyTask->getFloatParam());
//        }
//
//        $query->selectRaw('count(`id`) as total_deps, count(id) as cnt');
//
//        $data = $query->first();
//
//        return $data['total_deps'] ?? 0;
    }

    public function renderTaskItem(): string
    {
        return view('desktop.slc.daily-tasks.deposit-task-item', [
            'task' => $this,
            'daily_task' => $this->dailyTask,
            'dailyTask' => $this->dailyTask,
            'is_completed' => $this->dailyTask->isCompleted(),
        ])->render();
    }
}
