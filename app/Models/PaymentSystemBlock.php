<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Пользователи заблокированные за мультидеп (слишком частый вызов депозита)
 * в платежных системах где используется такая блокировка.
 * @property int $id
 * @property int $user_id
 * @property int $method_id
 * @property Carbon $block_to
 * @property Carbon $unblock_to
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @deprecated вынесено в сервис
 */
class PaymentSystemBlock extends Model
{
    use HasFactory;

    protected $dates = [
        'block_to',
        'unblock_to',
        'created_at',
        'updated_at',
    ];

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function getUserId(): int
    {
        return $this->user_id;
    }

    public function setUserId(int $user_id): void
    {
        $this->user_id = $user_id;
    }

    public function getMethodId(): int
    {
        return $this->method_id;
    }

    public function setMethodId(int $method_id): void
    {
        $this->method_id = $method_id;
    }

    public function getBlockTo(): Carbon
    {
        return $this->block_to;
    }

    public function setBlockTo(Carbon $block_to): void
    {
        $this->block_to = $block_to;
    }

    public function getUnblockTo(): ?Carbon
    {
        return $this->unblock_to;
    }

    public function setUnblockTo(Carbon $unblock_to): void
    {
        $this->unblock_to = $unblock_to;
    }

    public static function userBlockTime(int $user_id, int $system_id): ?PaymentSystemBlock
    {
        return self::where('user_id', $user_id)->where('method_id', $system_id)->first();
    }
}
