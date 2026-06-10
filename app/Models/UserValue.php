<?php

namespace App\Models;

use App\Services\Users\UserManager;

/**
 * Дополнительные данные пользователей.
 * Используется для сохранения дополнительных данных пользователя
 * разного типа.
 *
 * Например: Текущий день авторизации, текущий уровень cashback+ и т.п.
 */
class UserValue
{

    public static function getValue(int $user_id, string $key, $default = null): mixed
    {
        $resp = UserManager::getUserValue($user_id, $key);
        if ($resp['cd'] === 200) {
            return json_decode($resp['value'])?? $default;
        }
        return $default;
    }

    public static function getRawValue(int $user_id, string $key, $default = null): mixed
    {
        $resp = UserManager::getUserValue($user_id, $key);
        if ($resp['cd'] === 200) {
            return $resp['value']?? $default;
        }
        return $default;
    }

    public static function setValue(int $user_id, string $key, mixed $value): bool
    {
        $encoded = json_encode($value);
        return UserManager::setUserValue($user_id, $key, $encoded)['cd']?? 500 === 200;
    }

    public static function clearValue(int $user_id, string $key): void
    {
        UserManager::deleteUserValue($user_id, $key);
    }

}
