<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Дополнительные данные пользователей.
 * Используйте UserValue
 * @property int $user_id
 * @property string $data
 */
class UserData extends Model
{
    protected $primaryKey = 'user_id';
    public $incrementing = false;
    protected $guarded = [];

    private $_data = null;

    public function getValue(string $key, $default = null)
    {
        return $this->getData()[$key]?? $default;
    }

    public function setValue(string $key, $value): bool
    {
        $this->getData();
        $this->_data[$key] = $value;
        $this->data = json_encode($this->_data);
        return $this->save();
    }

    public function clearValue(string $key): bool
    {
        $this->getData();
        unset($this->_data[$key]);
        $this->data = json_encode($this->_data);
        return $this->save();
    }

    private function getData(): array
    {
        if ($this->_data === null) {
            $this->_data = json_decode($this->data, true)?? [];
        }
        return $this->_data;
    }

    public static function getForUser(int $user_id): UserData
    {
        return UserData::firstOrCreate(['user_id' => $user_id]);
    }
}
