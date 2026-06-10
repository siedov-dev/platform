<?php

namespace App\Models\Slc\UserTasks;

use App\Models\Slc\SlcTaskStatus;
use App\Models\Slc\SlcUserInventory;
use App\Models\Slc\UserTasks\DailyTask\DailyTask;
use App\Models\Slc\UserTasks\DailyTask\DepositTask;
use App\Models\Slc\UserTasks\DailyTask\MakeBetTask;
use App\Models\Slc\UserTasks\DailyTask\WheelSpinTask;
use App\MUser;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

/**
 * @property int $id
 * @property int $group_id
 * @property string $currency
 * @property UserTaskType $type
 * @property int $target_value
 * @property int $target_value_to
 * @property int $target_value_step
 * @property float $float_param
 * @property float $float_param_to
 * @property float $float_param_step
 * @property int $int_param
 * @property int $int_param_to
 * @property int $int_param_step
 * @property string $string_param
 * @property string $text_param
 * @property Collection<UserTaskParamGames> $games
 * @property int $priority
 */
class UserTaskParams extends Model
{
    use HasFactory;

    protected $casts = [
        'type' => UserTaskType::class,
    ];

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getGroupId(): int
    {
        return $this->group_id;
    }

    public function setGroupId(int $group_id): void
    {
        $this->group_id = $group_id;
    }

    public function getCurrency(): string
    {
        return $this->currency?? '';
    }

    public function setCurrency(?string $currency): void
    {
        $this->currency = $currency;
    }

    public function getType(): UserTaskType
    {
        return $this->type;
    }

    public function setType(UserTaskType $type): void
    {
        $this->type = $type;
    }

    public function getTargetValue(): int
    {
        return $this->target_value?? 0;
    }

    public function setTargetValue(int $target_value): void
    {
        $this->target_value = $target_value;
    }

    public function getTargetValueTo(): int
    {
        return $this->target_value_to?? 0;
    }

    public function genTargetValue(): int
    {
        if ($this->getTargetValueTo() <= $this->getTargetValue()) return $this->getTargetValue();
        $step = 1;
        if ($this->getTargetValueStep() > 0 ) $step = $this->getTargetValueStep();
        $values = [];
        for ($f = $this->getTargetValue(); $f <= $this->getTargetValueTo(); $f += $step) {
            $values[] = $f;
        }
        $values = collect($values)->shuffle()->toArray();
        return array_shift($values);
    }

    public function setTargetValueTo(int $target_value_to): void
    {
        $this->target_value_to = $target_value_to;
    }

    public function getTargetValueStep(): int
    {
        return $this->target_value_step?? 0;
    }

    public function setTargetValueStep(int $target_value_step): void
    {
        $this->target_value_step = $target_value_step;
    }

    public function getFloatParam(): float
    {
        return $this->float_param?? 0;
    }

    public function genFloatParam(): float
    {
        if ($this->getFloatParamTo() <= $this->getFloatParam()) return $this->getFloatParam();
        $step = 1;
        if ($this->getFloatParamStep() > 0 ) $step = $this->getFloatParamStep();
        $values = [];
        for ($f = $this->getFloatParam(); $f <= $this->getFloatParamTo(); $f += $step) {
            $values[] = $f;
        }
        $values = collect($values)->shuffle()->toArray();
        return array_shift($values);
    }


    public function setFloatParam(float $float_param): void
    {
        $this->float_param = $float_param;
    }

    public function getFloatParamTo(): float
    {
        return $this->float_param_to?? 0;
    }

    public function setFloatParamTo(float $float_param_to): void
    {
        $this->float_param_to = $float_param_to;
    }

    public function getFloatParamStep(): float
    {
        return $this->float_param_step?? 0;
    }

    public function setFloatParamStep(float $float_param_step): void
    {
        $this->float_param_step = $float_param_step;
    }

    public function getIntParam(): int
    {
        return $this->int_param?? 0;
    }

    public function genIntParam(): int
    {
        if ($this->getIntParamTo() <= $this->getIntParam()) return $this->getIntParam();
        $step = 1;
        if ($this->getIntParamStep() > 0 ) $step = $this->getIntParamStep();
        $values = [];
        for ($f = $this->getIntParam(); $f <= $this->getIntParamTo(); $f += $step) {
            $values[] = $f;
        }
        $values = collect($values)->shuffle()->toArray();
        return array_shift($values);
    }

    public function setIntParam(int $int_param): void
    {
        $this->int_param = $int_param;
    }

    public function getIntParamTo(): int
    {
        return $this->int_param_to?? 0;
    }

    public function setIntParamTo(int $int_param_to): void
    {
        $this->int_param_to = $int_param_to;
    }

    public function getIntParamStep(): int
    {
        return $this->int_param_step?? 0;
    }

    public function setIntParamStep(int $int_param_step): void
    {
        $this->int_param_step = $int_param_step;
    }

    public function getStringParam(): string
    {
        return $this->string_param?? '';
    }

    public function setStringParam(string $string_param): void
    {
        $this->string_param = $string_param;
    }

    public function getTextParam(): string
    {
        return $this->text_param?? '';
    }

    public function setTextParam(string $text_param): void
    {
        $this->text_param = $text_param;
    }

    /**
     * get UserTodaysReward collection for the task
     * @return UserTaskReward[]
     */
    public function getRewards()
    {
        return UserTaskReward::where('task_id', $this->getId())->get();
    }

    public function getPriority(): int
    {
        return $this->priority;
    }

    public function setPriority(int $priority): void
    {
        $this->priority = $priority;
    }

    public function games(): HasMany
    {
        return $this->hasMany(UserTaskParamGames::class, 'task_params_id', 'id');
    }

    public function makeCopy(): UserTaskParams
    {
        //copy task
        $copy_task = $this->replicate();
        $copy_task->setCreatedAt(now());
        $copy_task->setUpdatedAt(now());
        $copy_task->save();

        //copy rewards
        /** @var UserTaskReward $reward */
        foreach (UserTaskReward::where('task_id', $this->id)->get() as $reward) {
            $reward_copy = $reward->replicate();
            $reward_copy->setCreatedAt(now());
            $reward_copy->setUpdatedAt(now());
            $reward_copy->setGroupId($reward->getGroupId());
            $reward_copy->setTaskId($copy_task->getId());
            $reward_copy->save();
        }

        // copy games
        /** @var UserTaskParamGames $reward */
        foreach (UserTaskParamGames::where('task_params_id', $this->id)->get() as $game) {
            $game_copy = $game->replicate();
            $game_copy->setCreatedAt(now());
            $game_copy->setUpdatedAt(now());
            $game_copy->setTaskParamsId($copy_task->getId());
            $game_copy->save();
        }

        return $copy_task;
    }
}
