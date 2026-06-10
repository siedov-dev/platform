<?php

namespace App\Models\Slc\UserTasks;

use App\MUser;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $user_id
 * @property int $group_id
 */
class UserTodaysTask extends Model
{
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUserId(): int
    {
        return $this->user_id;
    }

    public function setUserId(int $user_id): void
    {
        $this->user_id = $user_id;
    }

    public function getGroupId(): int
    {
        return $this->group_id;
    }

    public function setGroupId(int $group_id): void
    {
        $this->group_id = $group_id;
    }

    public static function getTodaysGroup(MUser $user): UserTaskGroup
    {
        $group_id = UserTodaysTask::where('user_id', $user->getId())
            ->where('created_at', '>=', now()->startOfDay()->format('Y-m-d H:i:s'))
            ->first()?->getGroupId();
        if ($group_id === null) {
            $all_groups = UserTaskGroup::where('type', UserTaskGroupType::Daily)->get();
            $group = $all_groups->shuffle()->first();
            $group_id = $group->getId();

            $task = new UserTodaysTask();
            $task->setUserId($user->getId());
            $task->setGroupId($group_id);
            $task->save();
        }

        return UserTaskGroup::find($group_id);
    }

}
