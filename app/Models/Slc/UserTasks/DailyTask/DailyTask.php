<?php

namespace App\Models\Slc\UserTasks\DailyTask;

use App\Models\Slc\UserTasks\UserTaskParams;
use App\MUser;

interface DailyTask
{
    /**
     * Возвращает строку задачи задания
     * @return string
     */
    public function getGoal(): string;

    /**
     * Проверяет задачу на выполнения
     * @return bool возвращает завершена ли задача.
     */
    public function checkTask(): bool;

    /**
     * Возвращает значение цели задачи
     * @return int
     */
    public function getGoalValue(): int;

    /**
     * Возвращает текущее значение выполнения задачи
     * @return int
     */
    public function getCurrentValue(): int;

    /**
     * Рендерит елемент задачи для интерфейса
     * @return string
     */
    public function renderTaskItem(): string;
}
