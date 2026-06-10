<?php

namespace App\Models\Slc\TaskChecker;

use App\Models\Slc\SlcItem;
use App\Models\Slc\SlcTask;
use App\MUser;

interface TaskChecker
{
    /**
     * Проверяет завершена ли задача
     * @param MUser $user
     * @param SlcTask $task
     * @return bool task completed
     */
    public function checkComplete(MUser $user, SlcTask $task): bool;

    /**
     * Награда за задачу
     * @param SlcTask $task
     * @return SlcItem
     */
    public function getReward(SlcTask $task): SlcItem;

    /**
     * Текущее значение выполнения задачи
     * @param MUser $user
     * @param SlcTask $task
     * @return int
     */
    public function getCurrentValue(MUser $user, SlcTask $task): int;

    /**
     * Целевое значение задачи
     * @param SlcTask $task
     * @return int
     */
    public function getGoalValue(SlcTask $task): int;
}
