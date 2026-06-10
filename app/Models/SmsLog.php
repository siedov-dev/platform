<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Логи отправки СМС.
 * @property int $id
 * @property string $phone
 * @property string $text
 * @property string $service
 */
class SmsLog extends Model
{
    use HasFactory;

    protected $guarded = [];
}
