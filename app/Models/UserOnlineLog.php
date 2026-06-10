<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Лог онлайн пользователей.
 *
 * @property int $id
 * @property int $users_count
 * @property string $users
 * @property string $status
 * @property Carbon $dt
 */
class UserOnlineLog extends Model
{
    public $timestamps = false;

    protected $dates = ['dt'];

    public static function addLog(array $users = [], string $status = ''): UserOnlineLog
    {
        $log = new UserOnlineLog();
        $log->setUsers(json_encode($users));
        $log->setStatus($status);
        $log->setUsersCount(count($users));
        $log->setDt(now());
        $log->save();
        return $log;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function getUsersCount(): int
    {
        return $this->users_count;
    }

    public function setUsersCount(int $users_count): void
    {
        $this->users_count = $users_count;
    }

    public function getUsers(): string
    {
        return $this->users;
    }

    public function setUsers(string $users): void
    {
        $this->users = $users;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): void
    {
        $this->status = $status;
    }

    public function getDt(): Carbon
    {
        return $this->dt;
    }

    public function setDt(Carbon $dt): void
    {
        $this->dt = $dt;
    }
}
