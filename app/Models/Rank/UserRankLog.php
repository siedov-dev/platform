<?php

namespace App\Models\Rank;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Logs when user gets a new rank.
 *
 * @property int $id
 * @property int $user_id
 * @property int $prev_rank_level
 * @property int $new_rank_level
 * @property int $currency
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
class UserRankLog extends Model
{
    use HasFactory;

    protected $dates = [
        'created_at',
        'updated_at',
    ];

    protected $guarded = [];

    public static function log(int $user_id, int $currency, int $p_rank, int $c_rank): void
    {
        $log = new UserRankLog();
        $log->setUserId($user_id);
        $log->setCurrency($currency);
        $log->setPrevRankLevel($p_rank);
        $log->setNewRankLevel($c_rank);
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

    public function getCurrency(): int
    {
        return $this->currency;
    }

    public function setCurrency(int $currency): void
    {
        $this->currency = $currency;
    }

    public function getPrevRankLevel(): int
    {
        return $this->prev_rank_level;
    }

    public function setPrevRankLevel(int $prev_rank_level): void
    {
        $this->prev_rank_level = $prev_rank_level;
    }

    public function getNewRankLevel(): int
    {
        return $this->new_rank_level;
    }

    public function setNewRankLevel(int $new_rank_level): void
    {
        $this->new_rank_level = $new_rank_level;
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
