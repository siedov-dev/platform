<?php

namespace App\Models\TvBet;

use App\MUser;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;


/**
 * @property int $id
 * @property int $user_id
 * @property string $token
 * @property string $data
 * @property string $created_at
 * @property string $updated_at
 *
 * @property MUser $user
 */
class TVBetToken extends Model
{
    protected $table = 'log_from_tvbet';

    public function generateToken()
    {
        if ($this->exists) {
            $this->token = '';
            $this->save();
        }
        $this->token = Str::random(12);
        $tries = 0;
        while ($tries < 10 && TVBetToken::where('token', $this->token)->first() !== null) {
            $this->token = Str::random(12);
            $tries++;
        }
    }


    public function getCurrency() : string
    {
        return $this->data;
    }

    public function setCurrency(string $currency)
    {
        $this->data = $currency;
    }

    public function user()
    {
        return $this->hasOne(MUser::class, 'id', 'user_id');
    }
}
