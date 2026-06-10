<?php

namespace App\Models\Slc;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $day
 * @property int $reward
 * @deprecated
 */
class SlcDailyTask extends Model
{
    use HasFactory;

    public function getId(): int
    {
        return $this->id;
    }

    public function getDay(): int
    {
        return $this->day;
    }

    public function getReward(): int
    {
        return $this->reward;
    }

    public function setReward(int $reward): void
    {
        $this->reward = $reward;
    }

}
