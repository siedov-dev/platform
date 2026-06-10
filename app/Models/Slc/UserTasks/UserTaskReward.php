<?php

namespace App\Models\Slc\UserTasks;

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
 * @property int $group_id
 * @property int $task_count
 * @property SlcItemType $type
 * @property int $amount
 * @property int $bonus_type
 * @property string $params
 * @property string $allowed_games
 * @property int $task_id
 * @property int $max_amount
 * @property int $step_amount
 */
class UserTaskReward extends Model implements SlcItem
{
    use HasFactory;

    protected $casts = [
        'type' => SlcItemType::class,
    ];

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getGroupId(): ?int
    {
        return $this->group_id;
    }

    public function setGroupId(int $group_id): void
    {
        $this->group_id = $group_id;
    }

    public function getTaskCount(): int
    {
        return $this->task_count?? 0;
    }

    public function setTaskCount(int $task_count): void
    {
        $this->task_count = $task_count;
    }

    public function getType(): SlcItemType
    {
        return $this->type;
    }

    public function setType(SlcItemType $type): void
    {
        $this->type = $type;
    }

    public function getAmount(): int
    {
        return $this->amount?? 0;
    }

    public function setAmount(int $amount): void
    {
        $this->amount = $amount;
    }

    /**
     * get random amount from amount to max_amount with step step_amount
     * if max_amount > amount just return amount
     * @return int generated amount
     */
    public function genAmountValue(): int
    {
        if ($this->getMaxAmount() <= $this->getAmount()) return $this->getAmount();
        $step = 1;
        if ($this->getStepAmount() > 0 ) $step = $this->getStepAmount();
        $values = [];
        for ($f = $this->getAmount(); $f <= $this->getMaxAmount(); $f += $step) {
            $values[] = $f;
        }
        $values = collect($values)->shuffle()->toArray();
        return array_shift($values);
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

    public function getInfo(): ShopItemInfo
    {
        return  App::make(SlcItemFactory::class)->getItemInfo($this);
    }

    public function getActivator(): SlcItemActivator
    {
        return App::make(SlcItemFactory::class)->getItemActivator($this);
    }

    public function getTaskId(): int
    {
        return $this->task_id;
    }

    public function setTaskId(int $task_id): void
    {
        $this->task_id = $task_id;
    }

    public function getMaxAmount(): int
    {
        return $this->max_amount?? 1;
    }

    public function setMaxAmount(int $max_amount): void
    {
        $this->max_amount = $max_amount;
    }

    public function getStepAmount(): int
    {
        return $this->step_amount?? 1;
    }

    public function setStepAmount(int $step_amount): void
    {
        $this->step_amount = $step_amount;
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
