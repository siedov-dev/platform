<?php

namespace App\Models\Slc;

use App\Models\Slc\Activators\SlcItemActivator;
use App\Models\Slc\Factories\SlcItemConcreteFactory;
use App\Models\Slc\Factories\SlcItemFactory;
use App\Models\Slc\ItemInfo\ShopItemInfo;
use App\Services\KSlc\KSlc;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\App;

/**
 * @property int $id
 * @property int $day
 * @property int $amount
 * @property SlcItemType $type
 * @property int $bonus_type
 * @property string $params
 * @property string $allowed_games
 */
class SlcDailyReward implements SlcItem
{
    private int $id;
    private int $day;
    private SlcItemType $type;
    private int $bonus_type;
    private string $params;
    private string $allowed_games;
    private int $amount;
//    protected $casts = [
//        'type' => SlcItemType::class,
//    ];

    public function __construct(int $id, int $day, SlcItemType $type, int $bonus_type, string $params, string $allowed_games, int $amount)
    {
        $this->id = $id;
        $this->day = $day;
        $this->type = $type;
        $this->bonus_type = $bonus_type;
        $this->params = $params;
        $this->allowed_games = $allowed_games;
        $this->amount = $amount;
    }

    public function getId(): ?int
    {
        return $this->id;
    }
    public function getDay(): int
    {
        return $this->day?? 1;
    }

    public function setDay(int $day): void
    {
        $this->day = $day;
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
        return $this->params?? '';
    }

    public function setParams(string $params): void
    {
        $this->params = $params;
    }

    public function getAllowedGames(): string
    {
        return $this->allowed_games?? '';
    }

    public function setAllowedGames(string $allowed_games): void
    {
        $this->allowed_games = $allowed_games;
    }

    public function getAmount(): int
    {
        return $this->amount?? 0;
    }

    public function setAmount(int $amount): void
    {
        $this->amount = $amount;
    }

    public function getInfo(): ShopItemInfo
    {
        return  App::make(SlcItemFactory::class)->getItemInfo($this);
    }

    public function getActivator(): SlcItemActivator
    {
        return App::make(SlcItemFactory::class)->getItemActivator($this);
    }

    public static function getForDay(int $day): Collection
    {
        return KSlc::getInstance()->DailyLoginGetRewardsForDay($day);
//        return self::where('day', $day)->get();
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
