<?php

namespace App\Models\Slc;

use App\Models\Bonuses\UserBonus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $prize_type
 * @property int $bonus_type
 * @property string $bonus_params
 * @property string $bonus_games
 * @property string $bonus_title
 */
class FortuneWheelBonuses extends Model
{
    use HasFactory;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPrizeType(): int
    {
        return $this->prize_type;
    }

    public function getBonusType(): int
    {
        return $this->bonus_type?? UserBonus::TYPE_DEPOSIT_WAGER;
    }

    public function getBonusParams(): string
    {
        return $this->bonus_params?? '';
    }

    public function getBonusGames(): string
    {
        return $this->bonus_games?? '';
    }

    public function getBonusTitle(): string
    {
        return $this->bonus_title?? '';
    }

    public function setBonusTitle(string $bonus_title): void
    {
        $this->bonus_title = $bonus_title;
    }

}
