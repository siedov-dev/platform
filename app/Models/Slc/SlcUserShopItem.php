<?php

namespace App\Models\Slc;

use App\Models\Slc\Activators\SlcItemActivator;
use App\Models\Slc\Factories\SlcItemConcreteFactory;
use App\Models\Slc\Factories\SlcItemFactory;
use App\Models\Slc\ItemInfo\ShopItemInfo;
use App\MUser;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\App;

/**
 * @property int $id
 * @property int $user_id
 * @property int $amount
 * @property Carbon $active_to
 * @property SlcUserItemStatus $status
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property SlcItemType $type
 * @property int $bonus_type
 * @property string $params
 * @property string $allowed_games
 * @property string $item_source
 * @property int $source_id
 * @property string $img
 * @property string $descr
 * @property ?int $group_id
 */
class SlcUserShopItem extends Model implements SlcItem
{
    use HasFactory;

    private SlcItemFactory $item_factory;

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->item_factory = App::make(SlcItemFactory::class);
    }

    protected $dates = [
        'active_to',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'status' => SlcUserItemStatus::class,
        'type' => SlcItemType::class,
    ];

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUserId(): ?int
    {
        return $this->user_id;
    }

    public function getActiveTo(): ?Carbon
    {
        return $this->active_to;
    }

    public function setUserId(int $user_id): void
    {
        $this->user_id = $user_id;
    }

    public function setActiveTo(Carbon $active_to): void
    {
        $this->active_to = $active_to;
    }

    public function getAmount(): int
    {
        return $this->amount;
    }

    public function setAmount(int $amount): void
    {
        $this->amount = $amount;
    }

    public function addAmount(int $amount): int
    {
        $this->setAmount($this->getAmount() + $amount);
        return $this->getAmount();
    }

    public function remAmount(int $amount): int
    {
        if ($this->getAmount() < $amount) throw new \Exception('Not enough items');
        $this->setAmount($this->getAmount() - $amount);
        return $this->getAmount();
    }

    public function getStatus(): SlcUserItemStatus
    {
        return $this->status;
    }

    public function setStatus(SlcUserItemStatus $status): void
    {
        $this->status = $status;
    }

    public function activate(array $params = []): void
    {
        $this->getActivator()->activateItem($this, $params);
    }

    public static function getNewItemsCount(MUser $user): int
    {
        $last_seen = $user->getValue('last_inventory_visited');
        if ($last_seen === null) return SlcUserShopItem::where('user_id', $user->getId())
            ->where('status', SlcUserItemStatus::New->value)
            ->count();

        return SlcUserShopItem::where('status', SlcUserItemStatus::New->value)
            ->where('user_id', $user->getId())
            ->where('created_at', '>', $last_seen)
            ->count();
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

    public function getParams(): string
    {
        return $this->params?? "";
    }

    public function setParams(string $params): void
    {
        $this->params = $params;
    }

    public function getAllowedGames(): string
    {
        return $this->allowed_games?? "";
    }

    public function setAllowedGames(string $allowed_games): void
    {
        $this->allowed_games = $allowed_games;
    }

    public function getItemSource(): string
    {
        return $this->item_source;
    }

    public function setItemSource(string $item_source): void
    {
        $this->item_source = $item_source;
    }

    public function getSourceId(): int
    {
        return $this->source_id;
    }

    public function setSourceId(?int $source_id): void
    {
        $this->source_id = $source_id;
    }

    public function getInfo(): ShopItemInfo
    {
        return $this->item_factory->getItemInfo($this, MUser::find($this->getUserId()));
    }

    /**
     * @throws \Exception
     */
    public function getActivator(): SlcItemActivator
    {
        return $this->item_factory->getItemActivator($this);
    }

    public function checkStatus(): SlcUserItemStatus
    {
        if ($this->getAmount() <= 0) {
            $this->setStatus(\App\Models\Slc\SlcUserItemStatus::Depleted);
            $this->save();
        }
        if ($this->getInfo()->isExpirable() && $this->getActiveTo() < now()) {
            $this->setStatus(\App\Models\Slc\SlcUserItemStatus::Expired);
            $this->save();
        }

        return $this->getStatus();
    }

    public function isActive(): bool
    {
        $this->checkStatus();
        return $this->getStatus() === SlcUserItemStatus::New;
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

    public function getGroup(): ?int
    {
        return $this->group_id;
    }

    public function setGroup(?int $group): void
    {
        $this->group_id = $group;
    }

}
