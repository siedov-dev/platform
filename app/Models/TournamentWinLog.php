<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Модель данных для логирования выигрышей в турнирах.
 *
 * @property int $id
 * @property int $tournament_id
 * @property int $user_id
 * @property int $position
 * @property float $win_amount
 * @property string $currency
 * @property float $item_eval
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
class TournamentWinLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'tournament_id',
        'user_id',
        'win_amount',
        'currency',
        'item_eval'
    ];

    protected $dates = [
        'created_at',
        'updated_at',
    ];

    /**
     * Получить ID записи
     * @return int ID записи
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * Получить ID турнира
     * @return int ID турнира
     */
    public function getTournamentId(): int
    {
        return $this->tournament_id;
    }

    /**
     * Установить ID турнира
     * @param int $tournament_id ID турнира
     */
    public function setTournamentId(int $tournament_id): void
    {
        $this->tournament_id = $tournament_id;
    }

    /**
     * Получить ID пользователя
     * @return int ID пользователя
     */
    public function getUserId(): int
    {
        return $this->user_id;
    }

    /**
     * Установить ID пользователя
     * @param int $user_id ID пользователя
     */
    public function setUserId(int $user_id): void
    {
        $this->user_id = $user_id;
    }

    /**
     * Получить позицию
     * @return int Текущая позиция
     */
    public function getPosition(): int
    {
        return $this->position;
    }

    /**
     * Установить позицию
     * @param int $position Позиция
     * @return void
     */
    public function setPosition(int $position): void
    {
        $this->position = $position;
    }

    /**
     * Получить сумму выигрыша
     * @return float Сумма выигрыша
     */
    public function getWinAmount(): float
    {
        return $this->win_amount;
    }

    /**
     * Установить сумму выигрыша
     * @param float $win_amount Сумма выигрыша
     */
    public function setWinAmount(float $win_amount): void
    {
        $this->win_amount = $win_amount;
    }

    /**
     * Получить валюту
     * @return string Валюта
     */
    public function getCurrency(): string
    {
        return $this->currency;
    }

    /**
     * Установить валюту
     * @param string $currency Валюта
     */
    public function setCurrency(string $currency): void
    {
        $this->currency = $currency;
    }

    /**
     * Получить оценку предмета
     * @return float Оценка предмета
     */
    public function getItemEval(): float
    {
        return $this->item_eval;
    }

    /**
     * Установить оценку предмета
     * @param float $item_eval Оценка предмета
     */
    public function setItemEval(float $item_eval): void
    {
        $this->item_eval = $item_eval;
    }

}
