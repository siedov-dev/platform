<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Предоставляет приоритеты групп игр для сортировки.
 * @property int $id
 * @property string $system_id
 * @property string $group_id
 * @property int $priority
 */
class GameGroupPriority extends Model
{
    public $timestamps = false;
    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function getSystemId(): string
    {
        return $this->system_id;
    }

    public function setSystemId(string $system_id): void
    {
        $this->system_id = $system_id;
    }

    public function getGroupId(): string
    {
        return $this->group_id;
    }

    public function setGroupId(string $group_id): void
    {
        $this->group_id = $group_id;
    }

    public function getPriority(): int
    {
        return $this->priority;
    }

    public function setPriority(int $priority): void
    {
        $this->priority = $priority;
    }
}
