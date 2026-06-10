<?php

namespace App\Models\Slc\Achievements;

enum SlcAchievementType: string
{
    case Player = "player";
    case Spinner = "spinner";
    case DepMaster = "dep_master";
    case FirstSteps = "first_steps";
    case Bezdep = "bazdep";
    case DailyLogin = "daily_login";
    case WheelSpin = "wheel_spin";
    case SlotMachineSpin = "slotmachine_spin";
    case DailyTaskReward = "daily_task_reward";
    case OpenChests = "open_chests";
    case ShopBuy = "shop_buy";
    case BonusPay = "bonus_pay";
    case ImproveBonus = "improve_bonus";
    case CrashWins = "crash_wins";
    case BetsMade = "bets_made";
    case BetMultiplier = "bet_multiplier";
    case PromosActivated = "promos_activated";
    case CannonShots = "cannon_shots";
    case BarrelBreak = "barrel_break";
    case CashbackActivate = "cashback_activate";

    public function getCrmName(): string
    {
        return match ($this) {
            SlcAchievementType::Player => 'Player (ставок выше значения в играх)',
            SlcAchievementType::Spinner => 'Spinner (ставок в играх)',
            SlcAchievementType::DepMaster => 'Сделать депозиты',
            SlcAchievementType::FirstSteps => 'Первые шаги',
            SlcAchievementType::Bezdep => 'Бездеп',
            SlcAchievementType::DailyLogin => 'Забрать награду за вход',
            SlcAchievementType::WheelSpin => 'Прокрутить колесо',
            SlcAchievementType::SlotMachineSpin => 'Прокрутить слотмашину',
            SlcAchievementType::DailyTaskReward => 'Наград за ежедневные задачи',
            SlcAchievementType::OpenChests => 'Открыть сундуки',
            SlcAchievementType::ShopBuy => 'Покупки в мазагине',
            SlcAchievementType::BonusPay => 'Оплатить бонус (активировать)',
            SlcAchievementType::ImproveBonus => 'Улучшить бонус',
            SlcAchievementType::CrashWins => 'Выигрышей в краш играх',
            SlcAchievementType::BetsMade => 'Сделать ставок (прокрутов)',
            SlcAchievementType::BetMultiplier => 'Множитель в игре',
            SlcAchievementType::PromosActivated => 'Активировать промокодов',
            SlcAchievementType::CannonShots => 'Выстрелить по бочке',
            SlcAchievementType::BarrelBreak => 'Разбить бочку',
            SlcAchievementType::CashbackActivate => 'Активировать кешбэк',
            default => 'unknown',
        };
    }
}
