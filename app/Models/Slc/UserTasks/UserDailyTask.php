<?php

namespace App\Models\Slc\UserTasks;

use App\Models\Slc\UserTasks\DailyTask\BuyItemTask;
use App\Models\Slc\UserTasks\DailyTask\CompleteTasksTask;
use App\Models\Slc\UserTasks\DailyTask\DailyTask;
use App\Models\Slc\UserTasks\DailyTask\DepositTask;
use App\Models\Slc\UserTasks\DailyTask\GetMultTask;
use App\Models\Slc\UserTasks\DailyTask\MakeBetTask;
use App\Models\Slc\UserTasks\DailyTask\SlotmachineBetTask;
use App\Models\Slc\UserTasks\DailyTask\WheelSpinTask;
use App\MUser;
use App\Services\KSlc\KSlc;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;


/**
 * @property int $id
 * @property int $group_id
 * @property int $user_id
 * @property string $currency
 * @property UserTaskType $type
 * @property int $target_value
 * @property float $float_param
 * @property int $int_param
 * @property string $string_param
 * @property bool $is_completed
 * @property bool $is_rewarded
 */
class UserDailyTask
{
    private int $id;
    private int $group_id;
    private int $user_id;
    private string $currency;
    private UserTaskType $type;
    private int $target_value;
    private float $float_param;
    private int $int_param;
    private string $string_param;
    private bool $is_completed;
    private bool $is_rewarded;
    public function __construct(int $id, int $group_id, int $user_id, string $currency, UserTaskType $type, int $target_value, float $float_param, int $int_param, string $string_param, bool $is_completed, bool $is_rewarded)
    {
        $this->id = $id;
        $this->group_id = $group_id;
        $this->user_id = $user_id;
        $this->currency = $currency;
        $this->type = $type;
        $this->target_value = $target_value;
        $this->float_param = $float_param;
        $this->int_param = $int_param;
        $this->string_param = $string_param;
        $this->is_completed = $is_completed;
        $this->is_rewarded = $is_rewarded;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * Возвращает ид группы из которой создана задача
     * @return int
     */
    public function getGroupId(): int
    {
        return $this->group_id;
    }

    /**
     * Возвращает ид пользователя для которого создана задача
     * @return int|null
     */
    public function getUserId(): ?int
    {
        return $this->user_id;
    }

    public function setUserId(int $user_id): void
    {
        $this->user_id = $user_id;
    }

    public function setGroupId(int $group_id): void
    {
        $this->group_id = $group_id;
    }

    /**
     * Возвращает валюту для которой создана задача
     * @return string
     */
    public function getCurrency(): string
    {
        return $this->currency ?? '';
    }

    public function setCurrency(?string $currency): void
    {
        $this->currency = $currency;
    }

    /**
     * Возвращает тип задачи
     * @return UserTaskType
     */
    public function getType(): UserTaskType
    {
        return $this->type;
    }

    public function setType(UserTaskType $type): void
    {
        $this->type = $type;
    }

    /**
     * Возвращает целевое значение задача
     * @return int
     */
    public function getTargetValue(): int
    {
        return $this->target_value ?? 0;
    }

    public function setTargetValue(int $target_value): void
    {
        $this->target_value = $target_value;
    }

    public function getFloatParam(): float
    {
        return $this->float_param ?? 0;
    }

    public function setFloatParam(float $float_param): void
    {
        $this->float_param = $float_param;
    }

    public function getIntParam(): int
    {
        return $this->int_param ?? 0;
    }

    public function setIntParam(int $int_param): void
    {
        $this->int_param = $int_param;
    }

    public function getStringParam(): string
    {
        return $this->string_param ?? '';
    }

    public function setStringParam(string $string_param): void
    {
        $this->string_param = $string_param;
    }

    /**
     * Возвращает завершена ли задача
     * @return bool
     */
    public function isCompleted(): bool
    {
        return $this->is_completed;
    }

    public function setCompleted(): void
    {
        if (KSlc::getInstance()->setDailyTaskCompleted($this->user_id, $this->getId())) {
            $this->is_completed = true;
        }
    }

    /**
     * Возвращает получена ли награда за задачу
     * @return bool
     */
    public function isRewarded(): bool
    {
        return $this->is_rewarded;
    }

    public function setRewarded(): void
    {
        if (KSlc::getInstance()->setDailyTaskRewarded($this->user_id, $this->getId())) {
            $this->is_rewarded = true;
        }
    }

    /**
     * Проверяет завершение задачи
     * @param MUser $user
     * @return bool
     */
    public function checkStatus(MUser $user): bool
    {
        // if already completed
        if ($this->isCompleted()) return true;

        // if the task have become completed
        if ($this->getTask($user)->checkTask()) {
            // mark as completed
            $this->setCompleted();
//            $this->save();
            return true;
        }

        return false;
    }

    /**
     * Возвращает к-во завершенных, но не награжденных задач
     * @param MUser $user
     * @return mixed
     */
    public static function todayCompletedCount(MUser $user)
    {
        $stats = KSlc::getInstance()->getDailyTaskCompletedAndRewardedCount($user->getId());
        return $stats['completed'] - $stats['rewarded'];
//        return UserDailyTask::where('user_id', $user->getId())
//            ->where('created_at', '>=', now()->startOfDay()->format('Y-m-d H:i:s'))
//            ->where('is_completed', true)
//            ->where('is_rewarded', false)
//            ->count();
    }

    /**
     * Возвращает DailyTask для текущей задачи
     * @param MUser $user
     * @return DailyTask
     */
    public function getTask(MUser $user): DailyTask
    {
        return match ($this->getType()) {
            UserTaskType::MakeDeposit => new DepositTask($user, $this),
            UserTaskType::WheelSpin => new WheelSpinTask($user, $this),
            UserTaskType::MakeBet => new MakeBetTask($user, $this),
            UserTaskType::ButItem => new BuyItemTask($user, $this),
            UserTaskType::SlotmachineBet => new SlotmachineBetTask($user, $this),
            UserTaskType::GetMult => new GetMultTask($user, $this),
            UserTaskType::CompleteTasks => new CompleteTasksTask($user, $this),
        };
    }

    /**
     * Возвращает сегодняшние задачи пользователя.
     * При отсутствии - генерирует.
     * @param MUser $user
     * @return null|Collection|UserDailyTask[]
     */
    public static function getTodaysTasks(MUser $user): ?Collection
    {
        return KSlc::getInstance()->getUserDailyTasks($user->getId(), $user->getCurrencyName());
//        DB::beginTransaction();
//        try {
//            //get tasks
//            $tasks = UserDailyTask::where('user_id', $user->getId())
//                ->where('created_at', '>=', now()->startOfDay()->format('Y-m-d H:i:s'))
//                ->where(function ($q) use ($user) {
//                    $q->whereNull('currency');
//                    $q->orWhere('currency', '');
//                    $q->orWhere('currency', $user->getCurrencyName());
//                })
//                ->orderBy('id')
//                ->get();
//
//            //if there's no any - generate
//            if ($tasks->count() === 0) {
//                $group_id = UserTodaysTask::getTodaysGroup($user)->getId();
//                $params = UserTaskParams::with('games')->where('group_id', $group_id)
//                    ->orderBy('priority', 'asc')->get();
//
//                // Создаем сегодняшние задачи
//                /** @var UserTaskParams $task_params */
//                foreach ($params as $task_params) {
//                    $task = new UserDailyTask();
//                    $task->setGroupId($group_id);
//                    $task->setUserId($user->getId());
//                    $task->setType($task_params->getType());
//                    $task->setCurrency($task_params->getCurrency());
//                    $task->setTargetValue($task_params->genTargetValue());
//                    $task->setIntParam($task_params->genIntParam());
//                    $task->setFloatParam($task_params->genFloatParam());
//                    $task->setStringParam($task_params->getStringParam());
//                    if ($task_params->games->count() > 0) {
//                        $task->setStringParam($task_params->games->shuffle()->first()->getCid());
//                    }
//
//                    $task->save();
//
//                    // создание наград
//                    /** @var UserTaskReward $reward_params */
//                    foreach ($task_params->getRewards() as $reward_params) {
//                        $reward = new UserTodaysReward();
//                        $reward->user_id = $user->getId();
//                        $reward->task_id = $task->getId();
//                        $reward->setAmount($reward_params->genAmountValue());
//                        $reward->setType($reward_params->getType());
//                        $reward->setBonusType($reward_params->getBonusType());
//                        $reward->setParams($reward_params->getParams());
//                        $reward->setAllowedGames($reward_params->getAllowedGames());
//                        $reward->save();
//                    }
//                }
//
//                // выбираем созданные задачи
//                $tasks = UserDailyTask::where('user_id', $user->getId())
//                    ->where('created_at', '>=', now()->startOfDay()->format('Y-m-d H:i:s'))
//                    ->where(function ($q) use ($user) {
//                        $q->whereNull('currency');
//                        $q->orWhere('currency', '');
//                        $q->orWhere('currency', $user->getCurrencyName());
//                    })
//                    ->orderBy('id')
//                    ->get();
//            }
//
//            return $tasks;
//        } catch (\Exception $e) {
//            DB::commit();
//            Hlog::logException($e);
//        } finally {
//            DB::commit();
//        }
//
//        return null;
    }

    /**
     * Возвращает награды за выполнение задачи
     * @return mixed
     */
    public function getRewards()
    {
        return KSlc::getInstance()->getUserDailyTaskRewards($this->getUserId(), $this->getId());
//        return UserTodaysReward::where('task_id', $this->getId())->get();
    }

    /**
     * Возвращает общее к-во выполненных и награжденных ежедневных задач
     * @param MUser $user
     * @return int
     */
    public static function getTotalCompletedTaskCount(MUser $user): int
    {
        return KSlc::getInstance()->dailyTaskGetTotalRewardedCount($user->getId());
//        return DB::table('user_daily_tasks')->where('user_id', $user->getId())
//            ->where('is_rewarded', true)
//            ->count();
//        return UserDailyTask::where('user_id', $user->getId())
//            ->where('is_rewarded', true)
//            ->count();
    }
}
