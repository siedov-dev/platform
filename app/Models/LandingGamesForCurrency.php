<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $type
 * @property string $currency
 * @property string $cid
 */
class LandingGamesForCurrency extends Model
{
    public $timestamps = false;
}
