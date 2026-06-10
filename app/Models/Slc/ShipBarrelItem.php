<?php

namespace App\Models\Slc;

use App\Models\Slc\Activators\SlcItemActivator;
use App\Models\Slc\Factories\SlcItemFactory;
use App\Models\Slc\ItemInfo\ShopItemInfo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\App;

/**
 * @property int $id
 * @property SlcItemType $type
 * @property int $bonus_type
 * @property int $amount
 * @property string $params
 * @property string $games
 * @property int $chance
 */
class ShipBarrelItem extends Model implements SlcItem
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
        return $this->amount;
    }

    public function setAmount(int $amount): void
    {
        $this->amount = $amount;
    }

    public function getParams(): string
    {
        return $this->params?? '';
    }

    public function setParams(string $params): void
    {
        $this->params = $params;
    }

    public function getGames(): string
    {
        return $this->games?? '';
    }

    public function setGames(string $games): void
    {
        $this->games = $games;
    }

    public function getChance(): int
    {
        return $this->chance;
    }

    public function setChance(int $chance): void
    {
        $this->chance = $chance;
    }

    public function getAllowedGames(): string
    {
        return $this->getGames();
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
