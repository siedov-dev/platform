<?php

namespace App\GamesProviders\SoftSwiss\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $spins_id
 * @property int $user_id
 * @property string $currency
 * @property string $status
 * @property int $amount
 */
class SoftSwissFs extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public function getId(): int
    {
        return $this->id;
    }

    /**
     * free spins id in soft swiss
     * @return string
     */
    public function getSpinsId(): string
    {
        return $this->spins_id;
    }

    /**
     * user id
     * @return int
     */
    public function getUserId(): int
    {
        return $this->user_id;
    }

    /**
     * currency string code
     * @return string
     */
    public function getCurrency(): string
    {
        return $this->currency;
    }

    /** free spins status
     * @return FsStatus
     */
    public function getStatus(): FsStatus
    {
        return FsStatus::from($this->status);
    }

    /** free spins amount
     * @return int
     */
    public function getAmount(): int
    {
        return $this->amount;
    }

    /**
     * set fs status
     * @param FsStatus $status
     * @return void
     */
    public function setStatus(FsStatus $status): void
    {
        $this->status = $status;
    }

}
