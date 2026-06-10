<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;


/**
 * Class UserBonus
 * @property int $id
 * @property int $user_id
 * @property int $partner_id
 * @property int $win
 * @property string $href
 * @property string $host
 * @property string $created_at
 * @property string $updated_at
 * @deprecated
 */
class AdBonusToken extends Model
{
    /**
     * The connection name for the model.
     *
     * @var string
     */
    protected $connection = 'partners';

    public $table = 'ad_bonuses_tokens';
    public $timestamps = true;
}
