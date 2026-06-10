<?php

namespace App\GamesProviders\BetBy\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $betslip_id
 * @property int $player_id
 * @property int $operator_id
 * @property int $operator_brand_id
 * @property int $ext_player_id
 * @property float $timestamp
 * @property int $amount
 * @property string $currency
 * @property float $cross_rate_euro
 * @property string $operation
 * @property int $bonus_id
 * @property int $parent_transaction_id
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
class BetByTransaction extends Model
{
    protected $dates = [
        'created_at',
        'updated_at'
    ];

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function getBetslipId(): int
    {
        return $this->betslip_id;
    }

    public function setBetslipId(int $betslip_id): void
    {
        $this->betslip_id = $betslip_id;
    }

    public function getPlayerId(): int
    {
        return $this->player_id;
    }

    public function setPlayerId(int $player_id): void
    {
        $this->player_id = $player_id;
    }

    public function getOperatorId(): int
    {
        return $this->operator_id;
    }

    public function setOperatorId(int $operator_id): void
    {
        $this->operator_id = $operator_id;
    }

    public function getOperatorBrandId(): int
    {
        return $this->operator_brand_id;
    }

    public function setOperatorBrandId(int $operator_brand_id): void
    {
        $this->operator_brand_id = $operator_brand_id;
    }

    public function getExtPlayerId(): int
    {
        return $this->ext_player_id;
    }

    public function setExtPlayerId(int $ext_player_id): void
    {
        $this->ext_player_id = $ext_player_id;
    }

    public function getTimestamp(): float
    {
        return $this->timestamp;
    }

    public function setTimestamp(float $timestamp): void
    {
        $this->timestamp = $timestamp;
    }

    public function getAmount(): int
    {
        return $this->amount;
    }

    public function setAmount(int $amount): void
    {
        $this->amount = $amount;
    }

    public function getCurrency(): string
    {
        return $this->currency;
    }

    public function setCurrency(string $currency): void
    {
        $this->currency = $currency;
    }

    public function getCrossRateEuro(): float
    {
        return $this->cross_rate_euro;
    }

    public function setCrossRateEuro(float $cross_rate_euro): void
    {
        $this->cross_rate_euro = $cross_rate_euro;
    }

    public function getOperation(): string
    {
        return $this->operation;
    }

    public function setOperation(string $operation): void
    {
        $this->operation = $operation;
    }

    public function getBonusId(): int
    {
        return $this->bonus_id;
    }

    public function setBonusId(int $bonus_id): void
    {
        $this->bonus_id = $bonus_id;
    }

    public function getParentTransactionId(): int
    {
        return $this->parent_transaction_id;
    }

    public function setParentTransactionId(int $parent_transaction_id): void
    {
        $this->parent_transaction_id = $parent_transaction_id;
    }
}
