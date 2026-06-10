<?php

namespace App\Models\Slc\UserTasks;

use App\Models\Slc\Activators\SlcItemActivator;
use App\Models\Slc\Factories\SlcItemConcreteFactory;
use App\Models\Slc\Factories\SlcItemFactory;
use App\Models\Slc\ItemInfo\ShopItemInfo;
use App\Models\Slc\SlcItem;
use App\Models\Slc\SlcItemType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\App;

/**
 * @property int $id
 * @property int $user_id
 * @property int $task_id
 * @property int $amount
 * @property SlcItemType $type
 * @property int $bonus_type
 * @property string $params
 * @property string $allowed_games
 */
class UserTodaysReward implements SlcItem
{
    private int $id;
    private int $task_id;
    private int $user_id;
    private int $amount;
    private SlcItemType $type;
    private int $bonus_type;
    private string $params;
    private string $allowed_games;

    public function __construct(int $id, int $task_id, int $user_id, int $amount, SlcItemType $type, int $bonus_type, string $params, string $allowed_games)
    {
        $this->id = $id;
        $this->task_id = $task_id;
        $this->amount = $amount;
        $this->type = $type;
        $this->bonus_type = $bonus_type;
        $this->params = $params;
        $this->allowed_games = $allowed_games;
        $this->user_id = $user_id;
    }

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
