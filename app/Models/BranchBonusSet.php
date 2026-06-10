<?php

namespace App\Models;

use App\Models\Bonuses\Segments\BonusSegment;
use App\Models\Bonuses\Segments\BonusSegmentAttachable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * Наборы бонусов для отделов менеджеров
 *
 * @property int $id
 * @property int $branch_id
 * @property string $name
 * @property bool $active
 * @property int $bonus_type
 * @property ?string $params
 * @property ?string $games
 * @property ?string $title
 * @property ?string $img
 */
class BranchBonusSet extends Model implements BonusSegmentAttachable
{
    use HasFactory;

    protected $fillable = [
        'branch_id',
        'name',
        'active',
        'bonus_type',
        'params',
        'games',
        'title',
        'img'
    ];

    /**
     * Получить ID набора бонусов
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * Получить ID филиала
     */
    public function getBranchId(): int
    {
        return $this->branch_id;
    }

    /**
     * Установить ID филиала
     */
    public function setBranchId(int $branchId): self
    {
        $this->branch_id = $branchId;
        return $this;
    }

    /**
     * Получить название набора бонусов
     */
    public function getName(): string
    {
        return $this->name?? '';
    }

    /**
     * Установить название набора бонусов
     */
    public function setName(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    /**
     * Проверить активность набора бонусов
     */
    public function isActive(): bool
    {
        return $this->active;
    }

    /**
     * Установить активность набора бонусов
     */
    public function setActive(bool $active): self
    {
        $this->active = $active;
        return $this;
    }

    /**
     * Получить тип бонуса
     */
    public function getBonusType(): int
    {
        return $this->bonus_type;
    }

    /**
     * Установить тип бонуса
     */
    public function setBonusType(int $bonusType): self
    {
        $this->bonus_type = $bonusType;
        return $this;
    }

    /**
     * Получить параметры набора бонусов
     */
    public function getParams(): ?string
    {
        return $this->params;
    }

    /**
     * Установить параметры набора бонусов
     */
    public function setParams(?string $params): self
    {
        $this->params = $params;
        return $this;
    }

    /**
     * Получить список игр
     */
    public function getGames(): ?string
    {
        return $this->games;
    }

    /**
     * Установить список игр
     */
    public function setGames(?string $games): self
    {
        $this->games = $games;
        return $this;
    }

    /**
     * Получить заголовок
     */
    public function getTitle(): ?string
    {
        return $this->title;
    }

    /**
     * Установить заголовок
     */
    public function setTitle(?string $title): self
    {
        $this->title = $title;
        return $this;
    }

    /**
     * Получить изображение
     */
    public function getImg(): ?string
    {
        return $this->img;
    }

    /**
     * Установить изображение
     */
    public function setImg(?string $img): self
    {
        $this->img = $img;
        return $this;
    }

    /**
     * Отношение к BonusSegment
     * @return BelongsToMany
     */
    public function bonusSegments(): BelongsToMany
    {
        return $this->belongsToMany(BonusSegment::class);
    }
}
