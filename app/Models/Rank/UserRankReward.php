<?php

namespace App\Models\Rank;

use App\Models\Slc\Activators\SlcItemActivator;
use App\Models\Slc\Factories\SlcItemFactory;
use App\Models\Slc\ItemInfo\ShopItemInfo;
use App\Models\Slc\SlcItem;
use App\Models\Slc\SlcItemType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\App;

/**
 * @property int $id
 * @property int $rank_level
 * @property SlcItemType $type
 * @property int $bonus_type
 * @property int $amount
 * @property ?string $params
 * @property ?string $allowed_games
 */
class UserRankReward extends Model implements SlcItem
{
    use HasFactory;

    protected $casts = [
        'type' => SlcItemType::class,
    ];

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function getRankLevel(): int
    {
        return $this->rank_level;
    }

    public function setRankLevel(int $rank_level): void
    {
        $this->rank_level = $rank_level;
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
        return $this->bonus_type?? 0;
    }

    public function setBonusType(int $bonus_type): void
    {
        $this->bonus_type = $bonus_type;
    }

    public function getAmount(): int
    {
        return $this->amount?? 0;
    }

    public function setAmount(int $amount): void
    {
        $this->amount = $amount;
    }

    public function getParams(): string
    {
        return $this->params?? '';
    }

    public function setParams(?string $params): void
    {
        $this->params = $params;
    }

    public function getAllowedGames(): string
    {
        return $this->allowed_games?? '';
    }

    public function setAllowedGames(?string $allowed_games): void
    {
        $this->allowed_games = $allowed_games;
    }

    public function getInfo(): ShopItemInfo
    {
        return  App::make(SlcItemFactory::class)->getItemInfo($this);
    }

    public function getActivator(): SlcItemActivator
    {
        return App::make(SlcItemFactory::class)->getItemActivator($this);
    }

    public function getImage(): ?string
    {
        return null;
    }

    public function getDescription(?string $locale = null): ?string
    {
        return null;
    }

    public function getName(?string $locale = null): ?string
    {
        return null;
    }

    public function getTexts(): ?string
    {
        return null;
    }
}
