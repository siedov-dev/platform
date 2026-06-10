<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * VIP статус
 *
 * @property int $id
 * @property string $name
 */
class VipStatus extends Model
{
    use HasFactory;

    public function getId(): int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getImgUrl(string $theme = 'dark'): string
    {
        return '/img/ui/ranks/' . str_replace(' ', '_', $this->getName()) . ($theme === 'dark'? '' : '-light') . '.png';
    }
}
