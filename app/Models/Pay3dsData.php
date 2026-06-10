<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * 3дс данные для платежной системы GateExpress
 * @property int $id
 * @property int $transaction_id
 * @property string $method
 * @property string $url
 * @property string $params
 * @property string $md
 */
class Pay3dsData extends Model
{
    use HasFactory;
}
