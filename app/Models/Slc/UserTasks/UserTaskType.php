<?php

namespace App\Models\Slc\UserTasks;

enum UserTaskType: string
{
    case MakeDeposit = "make_deposit";
    case WheelSpin = "wheel_spin";
    case MakeBet = "make_bet";
    case ButItem = "buy_item";
    case SlotmachineBet = "slotmachine_bet";
    case GetMult = "get_mult";
    case CompleteTasks = "complete_tasks";

    public function getCrmName(): string
    {
        return match ($this) {
            self::MakeDeposit => 'Сделать депозит',
            self::WheelSpin => 'Прокрутить колесо',
            self::MakeBet => 'Сделать ставку',
            self::ButItem => 'Сделать покупку',
            self::SlotmachineBet => 'Ставки в слотмашине',
            self::GetMult => 'Получить множитель',
            self::CompleteTasks => 'Выполнить задачи',
        };
    }
}
