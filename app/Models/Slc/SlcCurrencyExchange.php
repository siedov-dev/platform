<?php

namespace App\Models\Slc;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $currency
 * @property int $slc_amount
 * @property float $currency_amount
 * @property int $prc
 * @property string $img
 * @property string $texts
 */
class SlcCurrencyExchange extends Model
{
    use HasFactory;
    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function getCurrency(): string
    {
        return $this->currency;
    }

    public function setCurrency(string $currency): void
    {
        $this->currency = $currency;
    }

    public function getSlcAmount(): int
    {
        return $this->slc_amount;
    }

    public function setSlcAmount(int $slc_amount): void
    {
        $this->slc_amount = $slc_amount;
    }

    public function getCurrencyAmount(): float
    {
        return $this->currency_amount;
    }

    public function setCurrencyAmount(float $currency_amount): void
    {
        $this->currency_amount = $currency_amount;
    }

    public function getPrc(): int
    {
        return $this->prc;
    }

    public function setPrc(int $prc): void
    {
        $this->prc = $prc;
    }

    public function getImg(): string
    {
        return $this->img;
    }

    public function setImg(string $img): void
    {
        $this->img = $img;
    }

    public function getTitle(?string $locale = null): ?string
    {
        if ($locale === null) $locale = \app()->getLocale();
        $texts = json_decode($this->getTexts(), true);
        return $texts[$locale]['title']?? $texts['en']['title']?? null;
    }

    public function getTexts(): ?string
    {
        return $this->texts;
    }

    public function setTexts(?string $texts): void
    {
        $this->texts = $texts;
    }
}
