<?php

namespace App\Models\Slc;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $user_id
 * @property int $amount
 * @property SlcItemType $type
 * @property int $bonus_type
 * @property string $params
 * @property string $games
 * @property int $source_id
 * @property string $source
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
class SlcInventoryLog extends Model
{
    use HasFactory;

    protected $dates = [
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'type' => SlcItemType::class,
    ];

    public function getId(): int
    {
        return $this->id;
    }

    public function getUserId(): int
    {
        return $this->user_id;
    }

    public function setUserId(int $user_id): void
    {
        $this->user_id = $user_id;
    }

    public function getAmount(): int
    {
        return $this->amount;
    }

    public function setAmount(int $amount): void
    {
        $this->amount = $amount;
    }

    public function getType(): SlcItemType
    {
        return $this->type;
    }

    public function setType(SlcItemType $type): void
    {
        $this->type = $type;
    }

    public function getBonusType(): int
    {
        return $this->bonus_type;
    }

    public function setBonusType(int $bonus_type): void
    {
        $this->bonus_type = $bonus_type;
    }

    public function getParams(): string
    {
        return $this->params;
    }

    public function setParams(string $params): void
    {
        $this->params = $params;
    }

    public function getGames(): string
    {
        return $this->games;
    }

    public function setGames(string $games): void
    {
        $this->games = $games;
    }

    public function getSourceId(): int
    {
        return $this->source_id;
    }

    public function setSourceId(?int $source_id): void
    {
        $this->source_id = $source_id;
    }

    public function getSource(): string
    {
        return $this->source;
    }

    public function setSource(string $source): void
    {
        $this->source = $source;
    }
}
