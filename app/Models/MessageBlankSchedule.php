<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * Запланированные рассылки.
 *
 * @property int $id
 * @property int $blank_id
 * @property string $ids
 * @property string $blade_data
 * @property int $status
 * @property int $total
 * @property int $sent
 * @property int $errors
 * @property string $method
 * @property string $last_error
 * @property string $source
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
class MessageBlankSchedule extends Model
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

    public function getBlankId(): int
    {
        return $this->blank_id;
    }

    public function setBlankId(int $blank_id): void
    {
        $this->blank_id = $blank_id;
    }

    public function getIds(): string
    {
        return $this->ids;
    }

    public function setIds(string $ids): void
    {
        $this->ids = $ids;
    }

    public function getBladeData(): string
    {
        return $this->blade_data;
    }

    public function setBladeData(string $blade_data): void
    {
        $this->blade_data = $blade_data;
    }

    public function getStatus(): int
    {
        return $this->status;
    }

    public function setStatus(int $status): void
    {
        $this->status = $status;
    }

    public function getTotal(): int
    {
        return $this->total;
    }

    public function setTotal(int $total): void
    {
        $this->total = $total;
    }

    public function getSent(): int
    {
        return $this->sent;
    }

    public function setSent(int $sent): void
    {
        $this->sent = $sent;
    }

    public function getErrors(): int
    {
        return $this->errors;
    }

    public function setErrors(int $errors): void
    {
        $this->errors = $errors;
    }

    public function getMethod(): string
    {
        return $this->method;
    }

    public function setMethod(string $method): void
    {
        $this->method = $method;
    }

    public function getLastError(): string
    {
        return $this->last_error;
    }

    public function setLastError(string $last_error): void
    {
        $this->last_error = $last_error;
    }

    public function getSource(): string
    {
        return $this->source;
    }

    public function setSource(string $source): void
    {
        $this->source = $source;
    }

    public function getCreatedAt(): Carbon
    {
        return $this->created_at;
    }

    public function getUpdatedAt(): Carbon
    {
        return $this->updated_at;
    }
}
