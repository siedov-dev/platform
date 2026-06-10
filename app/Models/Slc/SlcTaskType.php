<?php

namespace App\Models\Slc;

use App\Helpers\Device;
use App\Models\Slc\TaskChecker\EmailVerifyChecker;
use App\Models\Slc\TaskChecker\FirstBuyChecker;
use App\Models\Slc\TaskChecker\FirstDepChecker;
use App\Models\Slc\TaskChecker\FirstSpinChecker;
use App\Models\Slc\TaskChecker\FirstStepsChecker;
use App\Models\Slc\TaskChecker\PhoneVerifyChecker;
use App\Models\Slc\TaskChecker\RegTaskChecker;
use App\Models\Slc\TaskChecker\TaskChecker;
use App\Models\Slc\TaskChecker\ThreeTaskChecker;
use App\Models\Slc\TaskChecker\WheelSpinChecker;

enum SlcTaskType: string
{
    case Registration = "registration";
    case EmailConfirm = "email_confirm";
    case PhoneConfirm = "phone_confirm";
    case FirstSteps = "first_steps";
//    case FirstDep = "first_dep";
//    case FirstSpin = "first_spin";
//    case WheelSpin = "wheel_spin";
//    case ShopBuy = "shop_buy";
//    case TheeTasksReward = "tree_tasks_reward";
//    case TelegramSubscribe = "telegram_subscribe";
//    case Verification = "verification";
//    case SecondDep = "second_dep";
//    case ThirdDep = "third_dep";

    public function getName(): string
    {
        return __('slc.'.$this->value);
    }

    public function geRequiredParams(): array
    {
        return match ($this) {
//            SlcTaskType::FirstDep => [
//                'min_dep',
//            ],
            default => [],
        };
    }

    public function getTaskChecker(): TaskChecker
    {
        return match ($this) {
            self::Registration => new RegTaskChecker(),
            self::PhoneConfirm => new PhoneVerifyChecker(),
            self::EmailConfirm => new EmailVerifyChecker(),
            self::FirstSteps => new FirstStepsChecker(),
//            self::FirstDep => new FirstDepChecker(),
//            self::FirstSpin => new FirstSpinChecker(),
//            self::WheelSpin => new WheelSpinChecker(),
//            self::ShopBuy => new FirstBuyChecker(),
//            self::TheeTasksReward => new ThreeTaskChecker(),
            default => throw new \Exception('Not task checker'),
        };
    }

    public function renderTaskPad(): string
    {
        return match ($this) {
            self::Registration => view(Device::detect().'.slc.start-tasks.default', ['slcTaskType' => $this]),
            self::PhoneConfirm => view(Device::detect().'.slc.start-tasks.phone', ['slcTaskType' => $this]),
            self::EmailConfirm => view(Device::detect().'.slc.start-tasks.email', ['slcTaskType' => $this]),
            self::FirstSteps => view(Device::detect().'.slc.start-tasks.steps', ['slcTaskType' => $this]),
//            self::FirstDep => view(Device::detect().'.slc.start-tasks.first-dep', ['slcTaskType' => $this]),
//            self::FirstSpin => view(Device::detect().'.slc.start-tasks.first-spin', ['slcTaskType' => $this]),
//            self::WheelSpin => view(Device::detect().'.slc.start-tasks.wheel', ['slcTaskType' => $this]),
//            self::ShopBuy => view(Device::detect().'.slc.start-tasks.shop', ['slcTaskType' => $this]),
//            self::TheeTasksReward => view(Device::detect().'.slc.start-tasks.default', ['slcTaskType' => $this]),
            default => view(Device::detect().'.slc.start-tasks.default', ['slcTaskType' => $this]),
        };
    }
}
