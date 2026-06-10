<?php

namespace App\Models\Slc\UserTasks;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

/**
 * @property int $id
 * @property UserTaskGroupType $type тип группы задач (только ежедневные задачи)
 * @property UserTaskParams[] $tasks
 */
class UserTaskGroup extends Model
{
    use HasFactory;

    protected $casts = [
        'type' => UserTaskGroupType::class,
    ];

    /**
     * get group id
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * get group type
     * @return UserTaskGroupType
     */
    public function getType(): UserTaskGroupType
    {
        return $this->type;
    }

    /**
     * set group type
     * @param UserTaskGroupType $type
     * @return void
     */
    public function setType(UserTaskGroupType $type): void
    {
        $this->type = $type;
    }

    /**
     * task params relation
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function tasks(): HasMany
    {
        return $this->hasMany(UserTaskParams::class, 'group_id', 'id');
    }

}
