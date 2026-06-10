<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Содержит информацию о типе бонусов,
 * выдаваемых за регистрацию для определенной валюты.
 * @property int $id
 * @property int $currency_id
 * @property int $btype bonus type
 */
class BonusRegBType extends Model
{
    public $timestamps = false;

    protected $guarded = ['id'];

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function getCurrencyId(): int
    {
        return $this->currency_id;
    }

    public function setCurrencyId(int $currency_id): void
    {
        $this->currency_id = $currency_id;
    }

    public function getBtype(): int
    {
        return $this->btype;
    }

    public function setBtype(int $btype): void
    {
        $this->btype = $btype;
    }
}
