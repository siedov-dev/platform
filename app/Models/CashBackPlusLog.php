<?php

namespace App\Models;

use App\MUser;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * Сохраняет логи изменения значение cashback+
 * @property int $id
 * @property int $user_id
 * @property int $value
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property MUser $user
 */
class CashBackPlusLog extends Model
{
    use HasFactory;

    protected $dates = [
        'created_at',
        'updated_at',
    ];

    public static function log(int $user_id, int $value): void
    {
        $log = new CashBackPlusLog();
        $log->setUserId($user_id);
        $log->setValue($value);
        $log->save();
    }

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

    public function getValue(): int
    {
        return $this->value;
    }

    public function setValue(int $value): void
    {
        $this->value = $value;
    }

    public function user(): HasOne
    {
        return $this->hasOne(MUser::class, 'id', 'user_id');
    }
}
