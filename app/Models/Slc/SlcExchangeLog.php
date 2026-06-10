<?php

namespace App\Models\Slc;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $user_id
 * @property string $currency
 * @property int $slc_amount
 * @property float $curr_amount
 * @property Carbon $updated_at
 * @property Carbon $created_at
 */
class SlcExchangeLog extends Model
{
    use HasFactory;

    protected $dates = [
        'crated_at',
        'updated_at'
    ];

    public static function log(int $user_id, string $currency, int $slc_amount, float $curr_amount): void
    {
        $log = new SlcExchangeLog();
        $log->setUserId($user_id);
        $log->setCurrency($currency);
        $log->setSlcAmount($slc_amount);
        $log->setCurrAmount($curr_amount);
        $log->save();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function getUserId(): int
    {
        return $this->user_id;
    }

    public function setUserId(int $user_id): void
    {
        $this->user_id = $user_id;
    }

    public function getCurrency(): string
    {
        return $this->currency;
    }

    public function setCurrency(string $currency): void
    {
        $this->currency = $currency;
    }

    public function getSlcAmount(): int
    {
        return $this->slc_amount;
    }

    public function setSlcAmount(int $slc_amount): void
    {
        $this->slc_amount = $slc_amount;
    }

    public function getCurrAmount(): float
    {
        return $this->curr_amount;
    }

    public function setCurrAmount(float $curr_amount): void
    {
        $this->curr_amount = $curr_amount;
    }
}
