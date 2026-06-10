<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Описания страниц по локали.
 * @property int $id
 * @property string $name
 * @property string $descriptions
 */
class DescriptionPage extends Model
{
    use HasFactory;

    public $timestamps = false;

    public static function byName(string $name): ?DescriptionPage
    {
        return DescriptionPage::where('name', $name)->first();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getDescriptions(): string
    {
        return $this->descriptions;
    }

    public function setDescriptions(string $descriptions): void
    {
        $this->descriptions = $descriptions;
    }

    public function getPage(?string $locale = null): ?string
    {
        if ($locale === null) $locale = \app()->getLocale();
        $texts = json_decode($this->getDescriptions(), true);
        return $texts[$locale]['descr']?? $texts['en']['descr']?? null;
    }
}
