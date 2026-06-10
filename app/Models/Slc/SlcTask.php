<?php

namespace App\Models\Slc;

use App\Models\Slc\UserTasks\UserDailyTask;
use App\MUser;
use App\UserWallet;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property SlcTaskType $type
 * @property int $reward
 * @property string $params
 */
final class SlcTask extends Model
{
    use HasFactory;

    protected $casts = [
        'type' => SlcTaskType::class,
    ];

    public static function getByType(SlcTaskType $type): ?SlcTask
    {
        return SlcTask::where('type', $type)->first();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getType(): SlcTaskType
    {
        return $this->type;
    }

    public function getReward(): int
    {
        return $this->reward;
    }

    public function getParams(): array
    {
        return json_decode($this->params ?? '[]', true) ?? [];
    }

    public function getCurrencyParamValue(string $key, int|string $currency, mixed $default = null): mixed
    {
        if (is_int($currency)) $currency = UserWallet::currencyName($currency);

        return $this->getParamValue($currency.'_'.$key, $default);
    }

    public function getParamValue(string $key, mixed $default = null): mixed
    {
        return $this->getParams()[$key]?? $default;
    }

    public function setParamValue(string $key, mixed $value): void
    {
        $params = $this->getParams();
        $params[$key] =  $value;
        $this->params = json_encode($params);
    }

    public function getTaskName(string $currency): string
    {
        return __('slc.task-'.$this->getType()->value, [
            'min_dep' => $this->getCurrencyParamValue('min_dep', $currency, 0),
            'currency' => $currency,
        ]);
    }

    /**
     * check completed tasks
     * @param MUser $user
     * @return void
     * @throws \Exception
     */
    public static function checkAllTasks(MUser $user): void
    {
        // разрешены ли ежедневные задачи
        $is_daily_allowed = SlcCompletedTask::getTaskStatus($user->getId(), SlcTaskType::FirstSteps) === SlcTaskStatus::Rewarded;

        // если нет проверяем все стартовые задачи
        if (!$is_daily_allowed) {
            foreach (SlcTaskType::cases() as $slcTaskType) {
                $task_checker = $slcTaskType->getTaskChecker();
                $slc_task = SlcTask::getByType($slcTaskType);
                $task_checker->checkComplete($user, $slc_task);
            }
            // снова проверяем доступ к ежедневным
            $is_daily_allowed = SlcCompletedTask::getTaskStatus($user->getId(), SlcTaskType::FirstSteps) === SlcTaskStatus::Rewarded;
        }

        // проверяем завершение ежедневных задач
        if ($is_daily_allowed) {
            $todays_tasks = UserDailyTask::getTodaysTasks($user);
            /** @var UserDailyTask $task */
            foreach ($todays_tasks as $task) {
                $task->checkStatus($user);
            }
        }

    }

}
