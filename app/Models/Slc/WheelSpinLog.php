<?php

namespace App\Models\Slc;

use App\MUser;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $user_id
 * @property int $bonus_id
 * @property int $whee_prize_id
 * @property int $bonus_type
 * @property string $bonus_title
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
class WheelSpinLog extends Model
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

    public function getUserId(): int
    {
        return $this->user_id;
    }

    public function getPrizeId(): int
    {
        return $this->prize_id;
    }

    public function getBonusType(): int
    {
        return $this->bonus_type;
    }

    public function getBonusTitle(): string
    {
        return $this->bonus_title;
    }

    public function getCreatedAt(): Carbon
    {
        return $this->created_at;
    }

    public function getUpdatedAt(): Carbon
    {
        return $this->updated_at;
    }

    public function getBonusId(): ?int
    {
        return $this->bonus_id;
    }

    public function getWheePrizeId(): int
    {
        return $this->whee_prize_id;
    }

    public static function getUserTotalSpins(MUser $user): int
    {
        return WheelSpinLog::where('user_id', $user->getId())->count();
    }
}
