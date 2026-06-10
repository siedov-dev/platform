<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class ExchangeRateCache
 * Кеш значение курса валют.
 * @property int $id
 * @property string $bank_name
 * @property boolean $is_crypto
 * @property string $data
 * @property Carbon $rate_date
 */
class ExchangeRateCache extends Model
{
    //
    protected $dates = ['rate_date'];
}
