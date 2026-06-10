<?php

namespace App\Models\Slc;

use App\MUser;
use App\Services\KSlc\KSlc;

/**
 * @property int $id
 * @property int $user_id
 * @property SlcTaskType $task_type
 * @property SlcTaskStatus $status
 */
class SlcCompletedTask
{
    private int $user_id;
    private SlcTaskType $task_type;
//    use HasFactory;

//    protected $casts = [
//        'status' => SlcTaskStatus::class,
//        'task_type' => SlcTaskType::class,
//    ];
    public function __construct(int $user_id, SlcTaskType $task_type)
    {
        $this->user_id = $user_id;
        $this->task_type = $task_type;
    }

    public function getId(): string
    {
        return $this->task_type->value;
    }

    public function getUserId(): int
    {
        return $this->user_id;
    }

    public function getTaskType(): SlcTaskType
    {
        return $this->task_type;
    }

    public function getStatus(): SlcTaskStatus
    {
        return SlcTaskStatus::from(KSlc::getInstance()->getStartTaskStatus($this->user_id, $this->task_type->value));
//        return $this->status;
    }

    public function setStatus(SlcTaskStatus $status): void
    {
        KSlc::getInstance()->setStartTaskStatus($this->user_id, $this->task_type->value, $status->value);
//        $this->status = $status;
    }

    public static function getTask(int $user_id, SlcTaskType $task_type): ?SlcCompletedTask
    {
        return new SlcCompletedTask($user_id, $task_type);
//        return SlcCompletedTask::where('user_id', $user_id)
//            ->where('task_type', $task_type)
//            ->first();
    }

    /**
     * Возвращает статус стартовой задачи
     * @param int $user_id
     * @param SlcTaskType $task_type
     * @return SlcTaskStatus
     */
    public static function getTaskStatus(int $user_id, SlcTaskType $task_type): SlcTaskStatus
    {
        return SlcTaskStatus::from(KSlc::getInstance()->getStartTaskStatus($user_id, $task_type->value));
//        return self::getTask($user_id, $task_type)?->getStatus() ?? SlcTaskStatus::NotCompleted;
    }

    /**
     * Устанавливает статус стартовой задачи
     * @param int $user_id
     * @param SlcTaskType $task_type
     * @param SlcTaskStatus $status
     * @return void
     */
    public static function setTaskStatus(int $user_id, SlcTaskType $task_type, SlcTaskStatus $status): void
    {
        KSlc::getInstance()->setStartTaskStatus($user_id, $task_type->value, $status->value);
//        try {
//            DB::beginTransaction();
//            $task = SlcCompletedTask::where('user_id', $user_id)
//                ->where('task_type', $task_type)
//                ->lockForUpdate()
//                ->first();
//
//            if ($task === null) {
//                $task = new SlcCompletedTask();
//                $task->task_type = $task_type;
//                $task->user_id = $user_id;
//                $task->status = SlcTaskStatus::NotCompleted;
//            }
//
//            if ($task->getStatus() !== SlcTaskStatus::Rewarded) {
//                $task->setStatus($status);
//                $task->save();
//            }
//            DB::commit();
//        } catch (\Exception $e) {
//            DB::rollBack();
//            Log::error($e->getMessage());
//            Log::error($e->getLine());
//            Log::error($e->getFile());
//            Log::error($e->getTraceAsString());
//        }
    }


    /**
     * Возвращает к-во завершенных, но не награжденных стартовых задач
     * @param MUser $user
     * @return int
     */
    public static function completedTaskCount(MUser $user): int
    {
        return KSlc::getInstance()->getStartTaskCompletedCount($user->getId());
//        return SlcCompletedTask::where('user_id', $user->getId())
//            ->where('status', SlcTaskStatus::Completed)
//            ->count();
    }

}
