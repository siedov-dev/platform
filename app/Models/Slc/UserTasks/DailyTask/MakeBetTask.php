<?php

namespace App\Models\Slc\UserTasks\DailyTask;

use App\GamesProviders\Game;
use App\Models\Slc\UserTasks\UserDailyTask;
use App\MUser;
use App\Services\KSlc\KSlc;

class MakeBetTask implements DailyTask
{
    private ?Game $game = null;
    public function __construct(private readonly MUser $user, private readonly UserDailyTask $dailyTask)
    {
        if (!empty($this->dailyTask->getStringParam())) {
            $this->game = \App\GamesProviders\GamesManager::getGameByCompoundId($this->dailyTask->getStringParam());
        }
    }

    public function getGoal(): string
    {
        $goal = __('tasks.'.$this->dailyTask->getType()->value);
        if ($this->dailyTask->getFloatParam() > 0) {
            $goal .= ' ' . $this->dailyTask->getFloatParam() . '+';
            $goal .= ' ' . $this->dailyTask->getCurrency();
        }
        if ($this->game !== null) {
            $goal.= ' '. __('tasks.in_game').' '.$this->game->getName();
        }
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
//        $min_bet = $this->dailyTask->getFloatParam();
//        $tr = GameTransaction::where('user_id', $this->user->getId())
//            ->where('date', '>=', now()->startOfDay()->format('Y-m-d H:i:s'))
//            ->where('action', 'bet')
//            ->where('amount', '>=', $min_bet)
//            ->where(function ($q) {
//                $q->whereNull('bonus');
//                $q->orWhere('bonus', 0);
//            });
//
//        if (!empty($this->dailyTask->getCurrency())) {
//            $tr->where('currency', $this->dailyTask->getCurrency());
//        }
//
//        if ($this->game !== null) {
//            $tr->where('game_id', $this->game->getId());
//        }
//
//        return $tr->count();
    }

    public function renderTaskItem(): string
    {
        return view('desktop.slc.daily-tasks.bet-task-item', [
            'task' => $this,
            'is_completed' => $this->dailyTask->isCompleted(),
            'dailyTask' => $this->dailyTask,
            'game' => $this->game,
            'game_url' => route('games', ['name' => $this->game?->getName()?? ''], false),
        ])->render();
    }

}
