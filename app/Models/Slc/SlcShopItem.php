<?php

namespace App\Models\Slc;

use App\Models\Slc\Activators\SlcItemActivator;
use App\Models\Slc\Factories\SlcItemConcreteFactory;
use App\Models\Slc\Factories\SlcItemFactory;
use App\Models\Slc\ItemInfo\ShopItemInfo;
use App\Models\Slc\ItemInfo\SlcBonusItemInfo;
use App\Models\Slc\ItemInfo\SlcDepositItemInfo;
use App\Models\Slc\ItemInfo\SlcUnknownItemInfo;
use App\Models\Slc\ItemInfo\SlcWagerItemInfo;
use App\MUser;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\App;

/**
 * @property int $id
 * @property SlcItemType $type
 * @property int $bonus_type
 * @property integer $price
 * @property integer $amount
 * @property string $params
 * @property string $allowed_games
 * @property bool $disabled
 * @property int $priority
 * @property string $img
 * @property string $descr
 */
class SlcShopItem extends Model implements SlcItem
{
    use HasFactory;

    private SlcItemFactory $item_factory;

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->item_factory = App::make(SlcItemFactory::class);
    }

    protected $casts = [
        'type' => SlcItemType::class,
    ];

    public function getId(): ?int
    {
        return $this->id;
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

    public function getPrice(): int
    {
        return $this->price?? 0;
    }

    public function getAmount(): int
    {
        return $this->amount?? 0;
    }

    public function getParams(): string
    {
        return $this->params?? '';
    }

    public function getAllowedGames(): string
    {
        return $this->allowed_games?? "";
    }

    public function isDisabled(): bool
    {
        return $this->disabled?? false;
    }

    public function disable()
    {
        $this->disabled = true;
    }

    public function enable()
    {
        $this->disabled = false;
    }

    public function setPrice(int $price): void
    {
        $this->price = $price;
    }

    public function setAmount(int $amount): void
    {
        $this->amount = $amount;
    }

    public function getPriority(): int
    {
        return $this->priority;
    }

    public function setPriority(int $priority): void
    {
        $this->priority = $priority;
    }

    public function getInfo(): ShopItemInfo
    {
        return $this->item_factory->getItemInfo($this);
    }

    /**
     * @throws \Exception
     */
    public function getActivator(): SlcItemActivator
    {
        return $this->item_factory->getItemActivator($this);
    }

    public static function getNewItemsCount(MUser $user): int
    {
        $last_seen = $user->getValue('last_shop_visited');
        if ($last_seen === null) return SlcShopItem::count();
        return SlcShopItem::where('created_at', '>', $last_seen)->count();
    }

    public static function getItemPrice(SlcItemType $type): int
    {
        return SlcShopItem::where('type', $type->value)->first()?->getPrice()?? 0;
    }

    public function getImage(): ?string
    {
        return $this->img;
    }

    public function setImage(?string $img): void
    {
        $this->img = $img;
    }

    public function getDescription(?string $locale = null): ?string
    {
        if ($locale === null) $locale = \app()->getLocale();
        $texts = json_decode($this->getTexts(), true);
        return $texts[$locale]['descr']?? $texts['en']['descr']?? null;
    }

    public function getName(?string $locale = null): ?string
    {
        if ($locale === null) $locale = \app()->getLocale();
        $texts = json_decode($this->getTexts(), true);
        return $texts[$locale]['name']?? $texts['en']['name']?? null;
    }

    public function getTexts(): ?string
    {
        return $this->descr;
    }

    public function setTexts(?string $texts): void
    {
        $this->descr = $texts;
    }
}
