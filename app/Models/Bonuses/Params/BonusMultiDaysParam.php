<?php

namespace App\Models\Bonuses\Params;

use App\GamesProviders\GamesManager;
use App\Services\KBonus\KBonus;

/**
 * @property ?int $id
 * @property ?int $day
 * @property ?string $game_cid
 * @property ?int $spins
 * @property ?int $wager
 * @property ?string $params
 * @property ?string $game_name
 */
class BonusMultiDaysParam
{
    protected $attributes = [];

    public function __construct(array $attributes = [])
    {
        $this->attributes = $attributes;
    }

    public function __get(string $name)
    {
        return $this->attributes[$name] ?? null;
    }

    public function __set(string $name, $value): void
    {
        $this->attributes[$name] = $value;
    }

    public function __isset(string $name): bool
    {
        return isset($this->attributes[$name]);
    }

    public function offsetExists($offset): bool
    {
        return isset($this->attributes[$offset]);
    }

    /**
     * Get the value for a given offset.
     *
     * @param  mixed  $offset
     * @return mixed
     */
    public function offsetGet($offset): mixed
    {
        return $this->attributes[$offset];
    }

    /**
     * Set the value for a given offset.
     *
     * @param  mixed  $offset
     * @param  mixed  $value
     * @return void
     */
    public function offsetSet($offset, $value): void
    {
        $this->attributes[$offset] = $value;
    }

    public function offsetUnset(mixed $offset): void
    {
        $this->attributes[$offset] = null;
    }

    public function toArray()
    {
        return $this->attributes;
    }


    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function getDay(): ?int
    {
        return $this->day;
    }

    public function setDay(int $day): void
    {
        $this->day = $day;
    }

    public function getGameCid(): ?string
    {
        return $this->game_cid;
    }

    public function setGameCid(string $game_cid): void
    {
        $this->game_cid = $game_cid;
    }

    public function getSpins(): int
    {
        return $this->spins;
    }

    public function setSpins(int $spins): void
    {
        $this->spins = $spins;
    }

    public function getWager(): ?int
    {
        return $this->wager;
    }

    public function setWager(int $wager): void
    {
        $this->wager = $wager;
    }

    public function getParams(): ?string
    {
        return $this->params;
    }

    public function setParams(string $params): void
    {
        $this->params = $params;
    }

    public static function getForDay(int $day): ?BonusMultiDaysParam
    {
//        return BonusMultiDaysParam::where('day', $day)->first();
        return KBonus::getInstance()->multidaysParamsForDay($day);
    }

    public function getGameName(): ?string
    {
        return GamesManager::getGameByCompoundId($this->game_cid)?->getName();
    }

    public function setGameName(?string $game_name): void
    {
        $this->game_name = $game_name;
    }
}
