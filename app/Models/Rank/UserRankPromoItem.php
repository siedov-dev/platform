<?php

namespace App\Models\Rank;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

/**
 * Промо элементы ранга
 *
 * @property int $id
 * @property int $level
 * @property string $titles
 * @property string $img
 * @property string $values
 * @property int $priority
 */
class UserRankPromoItem extends Model
{
    use HasFactory;

    /**
     * @param int $level
     * @return Collection|UserRankPromoItem[]
     */
    public static function getItemsForLevel(int $level): Collection
    {
        return UserRankPromoItem::where('level', $level)->get();
    }

    /**
     * Возвращает идентификатор элемента
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * Устанавливает идентификатор элемента
     * @param int $id Идентификатор
     * @return void
     */
    public function setId(int $id): void
    {
        $this->id = $id;
    }

    /**
     * Возвращает уровень ранга элемента
     * @return int
     */
    public function getLevel(): int
    {
        return $this->level;
    }

    /**
     * Устанавливает уровень ранга элемента
     * @param int $level Уровень
     * @return void
     */
    public function setLevel(int $level): void
    {
        $this->level = $level;
    }

    /**
     * Возвращает заголовки элемента
     * @return string
     */
    public function getTitles(): string
    {
        return $this->titles;
    }

    /**
     * Устанавливает заголовки элемента
     * @param string $titles Заголовки
     * @return void
     */
    public function setTitles(string $titles): void
    {
        $this->titles = $titles;
    }

    /**
     * Возвращает путь к изображению элемента
     * @return string
     */
    public function getImg(): string
    {
        return $this->img;
    }

    /**
     * Устанавливает путь к изображению элемента
     * @param string $img Путь к изображению
     * @return void
     */
    public function setImg(string $img): void
    {
        $this->img = $img;
    }

    /**
     * Возвращает значения элемента
     * @return string
     */
    public function getValues(): string
    {
        return $this->values;
    }

    /**
     * Устанавливает значения элемента
     * @param string $values Значения
     * @return void
     */
    public function setValues(string $values): void
    {
        $this->values = $values;
    }

    /**
     * Возвращает приоритет элемента
     * @return int
     */
    public function getPriority(): int
    {
        return $this->priority;
    }

    /**
     * Устанавливает приоритет элемента
     * @param int $priority Приоритет
     * @return void
     */
    public function setPriority(int $priority): void
    {
        $this->priority = $priority;
    }

    /**
     * Возвращает значение для локали
     * @param string|null $locale
     * @return string|null
     */
    public function getValue(?string $locale = null): ?string
    {
        if ($locale === null) $locale = \app()->getLocale();
        $texts = json_decode($this->getValues(), true);
        return $texts[$locale]['value']?? $texts['en']['value']?? null;
    }

    /**
     * Возвращает тайтл для локали
     * @param string|null $locale
     * @return string|null
     */
    public function getTitle(?string $locale = null): ?string
    {
        if ($locale === null) $locale = \app()->getLocale();
        $texts = json_decode($this->getTitles(), true);
        return $texts[$locale]['title']?? $texts['en']['title']?? null;
    }


}
