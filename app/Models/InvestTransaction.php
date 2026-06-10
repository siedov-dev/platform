<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @deprecated
 * @property int $id
 * @property string $pay_system
 * @property int $user_id
 * @property string $created
 * @property float $amount
 * @property int $type
 * @property float $total
 * @property string $complete_date
 * @property string $token
 * @property int $extra
 */
class InvestTransaction extends Model
{
    const STATUS_IN_PROGRESS = 1;
    const STATUS_DONE = 2;

    /**
     * The connection name for the model.
     *
     * @var string
     */
    protected $connection = 'invest';

    public $table = 'investments';
    public $timestamps = false;
}
