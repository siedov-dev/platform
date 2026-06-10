<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Данные с мин ставками и макс. уровнем ставок для фриспинов
 * провайдера Pragmatic системы Api LX
 * @property int $id
 * @property int $game_id
 * @property string $currency
 * @property float $min_bet
 * @property int $max_bet_level
 */
class ApiLxPragmaticGameBets extends Model
{

    protected $guarded = ['id'];

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function getGameId(): int
    {
        return $this->game_id;
    }

    public function setGameId(int $game_id): void
    {
        $this->game_id = $game_id;
    }

    public function getCurrency(): string
    {
        return $this->currency;
    }

    public function setCurrency(string $currency): void
    {
        $this->currency = $currency;
    }

    public function getMinBet(): float
    {
        return $this->min_bet;
    }

    public function setMinBet(float $min_bet): void
    {
        $this->min_bet = $min_bet;
    }

    public function getMaxBetLevel(): int
    {
        return $this->max_bet_level;
    }

    public function setMaxBetLevel(int $max_bet_level): void
    {
        $this->max_bet_level = $max_bet_level;
    }
}
