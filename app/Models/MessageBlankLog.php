<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * Логи рассылок.
 *
 * @property int $id
 * @property int $schedule_id
 * @property int $user_id
 * @property string $method
 * @property string $response
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
class MessageBlankLog extends Model
{
    use HasFactory;

    protected $dates = [
        'created_at',
        'updated_at',
    ];

    public function getId(): int
    {
        return $this->id;
    }

    public function getScheduleId(): int
    {
        return $this->schedule_id;
    }

    public function setScheduleId(int $schedule_id): void
    {
        $this->schedule_id = $schedule_id;
    }

    public function getUserId(): int
    {
        return $this->user_id;
    }

    public function setUserId(int $user_id): void
    {
        $this->user_id = $user_id;
    }

    public function getMethod(): string
    {
        return $this->method;
    }

    public function setMethod(string $method): void
    {
        $this->method = $method;
    }

    public function getResponse(): string
    {
        return $this->response;
    }

    public function setResponse(string $response): void
    {
        $this->response = $response;
    }


}
