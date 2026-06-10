<?php

namespace App\Models;

use App\Services\KBonus\KBonus;
use ArrayAccess;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Database\Eloquent\Model;

/**
 * Модель содержит параметры для меню бонусов,
 * или параметры для бонусов, которые работают в зависимости
 * от определенных значений действий пользователя.
 * Например: значение ведйжера при выборе определенного меню активации,
 * или уровень кешбэка в зависимости от суммы депозита.
 *
 * Crm: Настройка значений меню/уровней бонусов.
 *
 * @property int $id
 * @property int $currency
 * @property int $bonus_type
 * @property int $bonus_pattern
 * @property string $name
 * @property float $min_value
 * @property float $max_value deprecated
 * @property float $value
 * @property float $value1
 * @property float $value2
 * @property float $value3
 * @property float $value4
 * @property float $value5
 */
class BonusMenuParam implements Arrayable, ArrayAccess
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

    /**
     * @return float
     */
    public function getMinValue(): float
    {
        return $this->min_value;
    }

    /**
     * @param int $num value number
     * @return float
     */
    public function getValue(int $num = 0): float
    {
        return $this['value'.($num > 0? $num : '')];
    }


    /**
     * @param int $bonus_type
     * @param int $currency_id
     * @return BonusMenuParam[]
     */
    public static function getBonusParams(int $bonus_type, int $currency_id): array
    {
        return KBonus::getInstance()->getBonusParams($bonus_type, $currency_id);
    }

    /**
     * get bonus params by value in min-max range
     * @param int $bonus_type
     * @param int $currency
     * @param float $value
     * @return BonusMenuParam|null
     */
    public static function findInRangeParams(int $bonus_type, int $currency, float $value): ?BonusMenuParam
    {
        return KBonus::getInstance()->paramsFindInRange($bonus_type, $currency, $value);
    }

    /**
     * get bonus params by key value (min_value)
     * @param int $bonus_type
     * @param int $currency
     * @param float $key
     * @return BonusMenuParam|null
     */
    public static function getKeyParams(int $bonus_type, int $currency, float $key): ?BonusMenuParam
    {
        return KBonus::getInstance()->paramGetByKeyValue($bonus_type, $currency, $key);
    }

    /**
     * get lowest min_value
     * @param int $bonus_type
     * @param int $currency
     * @return float
     */
    public static function getLowestMinValue(int $bonus_type, int $currency): float
    {
        return KBonus::getInstance()->paramLowestMinValue($bonus_type, $currency);
    }
}
