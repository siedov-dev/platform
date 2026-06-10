<?php

namespace App\Models\Slc\Chest;

use App\Models\Slc\Activators\SlcItemActivator;
use App\Models\Slc\Factories\SlcItemConcreteFactory;
use App\Models\Slc\Factories\SlcItemFactory;
use App\Models\Slc\ItemInfo\ShopItemInfo;
use App\Models\Slc\SlcItem;
use App\Models\Slc\SlcItemType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\App;

/**
 * @property int $id
 * @property ChestType $chest_type
 * @property int $group
 * @property float $chance
 * @property SlcItemType $item_type
 * @property int $amount
 * @property int $bonus_type
 * @property string $bonus_params
 * @property string $bonus_games
 * @property int $max_amount
 * @property int $multiplier
 */
class SlcChestItem extends Model implements SlcItem
{
    protected $casts = [
        'item_type' => SlcItemType::class,
        'chest_type' => ChestType::class,
    ];

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getChestType(): ChestType
    {
        return $this->chest_type;
    }

    public function setChestType(ChestType $chest_type): void
    {
        $this->chest_type = $chest_type;
    }

    public function getGroup(): int
    {
        return $this->group?? 1;
    }

    public function setGroup(int $group): void
    {
        $this->group = $group;
    }

    public function getChance(): float
    {
        return $this->chance?? 10;
    }

    public function setChance(float $chance): void
    {
        $this->chance = $chance;
    }

    public function getItemType(): SlcItemType
    {
        return $this->item_type;
    }

    public function setItemType(SlcItemType $item_type): void
    {
        $this->item_type = $item_type;
    }

    public function getBonusType(): int
    {
        return $this->bonus_type;
    }

    public function setBonusType(int $bonus_type): void
    {
        $this->bonus_type = $bonus_type;
    }

    public function getBonusParams(): string
    {
        return $this->bonus_params?? '';
    }

    public function setBonusParams(string $bonus_params): void
    {
        $this->bonus_params = $bonus_params;
    }

    public function getBonusGames(): string
    {
        return $this->bonus_games?? '';
    }

    public function setBonusGames(string $bonus_games): void
    {
        $this->bonus_games = $bonus_games;
    }

    public function getType(): SlcItemType
    {
        return $this->getItemType();
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
        return $this->getBonusParams();
    }

    public function getAllowedGames(): string
    {
        return $this->getBonusGames();
    }

    public function getInfo(): ShopItemInfo
    {
        return  App::make(SlcItemFactory::class)->getItemInfo($this);
    }

    public function getActivator(): SlcItemActivator
    {
        return App::make(SlcItemFactory::class)->getItemActivator($this);
    }

    public function getMaxAmount(): int
    {
        return $this->max_amount?? 0;
    }

    public function setMaxAmount(int $max_amount): void
    {
        $this->max_amount = $max_amount;
    }

    /**
     * get amount multiplier
     * minimum 1
     * @return int
     */
    public function getMultiplier(): int
    {
        return ($this->multiplier?? 1)?: 1;
    }

    public function setMultiplier(int $multiplier): void
    {
        $this->multiplier = $multiplier;
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
