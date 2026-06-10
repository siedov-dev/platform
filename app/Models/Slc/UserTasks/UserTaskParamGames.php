<?php

namespace App\Models\Slc\UserTasks;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * @property int $id
 * @property int $task_params_id
 * @property string $cid
 * @property UserTaskParams $taskParams
 */
class UserTaskParamGames extends Model
{
    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function getTaskParamsId(): int
    {
        return $this->task_params_id;
    }

    public function setTaskParamsId(int $task_params_id): void
    {
        $this->task_params_id = $task_params_id;
    }

    public function getCid(): string
    {
        return $this->cid;
    }

    public function setCid(string $cid): void
    {
        $this->cid = $cid;
    }

    public function taskParams(): HasOne
    {
        return $this->hasOne(UserTaskParams::class, 'id', 'task_params_id');
    }
}
