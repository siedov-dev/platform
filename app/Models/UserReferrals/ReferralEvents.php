<?php

namespace App\Models\UserReferrals;

use App\MUser;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class ReferralEvents
 * @package App\Models
 * @property int $id
 * @property int $ref_id
 * @property int $type
 * @property int $user_id
 * @property float $amount
 * @property float $profit
 * @property int $currency
 * @property string $data
 * @property int $status
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
class ReferralEvents extends Model
{
    const TYPE_CLICK = 0;
    const TYPE_REGISTER = 1;
    const TYPE_MONEY_SPENT = 2;
    const TYPE_DEPOSIT = 3;
    const TYPE_FIRST_DEPOSIT = 4;

    const STATUS_NONE = 0;
    const STATUS_HOLD = 1;
    const STATUS_PAYED = 2;
    const STATUS_CANCELED = 3;
    //
    protected $guarded = [];

    protected $dates = [
        'created_at',
        'updated_at',
    ];
}
