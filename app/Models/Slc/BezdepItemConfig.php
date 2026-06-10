<?php

namespace App\Models\Slc;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $bonus_type
 * @property string $params
 * @property string $games
 * @property int $active_days
 * @property ?string $title
 * @property ?string $img_url
 */
class BezdepItemConfig extends Model
{
    public function getId(): ?int
    {
        return $this->id;
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
        return $this->params;
    }

    public function setParams(string $params): void
    {
        $this->params = $params;
    }

    public function getGames(): string
    {
        return $this->games;
    }

    public function setGames(string $games): void
    {
        $this->games = $games;
    }

    public function getActiveDays(): int
    {
        return $this->active_days;
    }

    public function setActiveDays(int $active_days): void
    {
        $this->active_days = $active_days;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(?string $title): void
    {
        $this->title = $title;
    }

    public function getImgUrl(): ?string
    {
        return $this->img_url;
    }

    public function setImgUrl(?string $img_url): void
    {
        $this->img_url = $img_url;
    }

}
