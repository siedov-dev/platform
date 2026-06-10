<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

/**
 * Данные пользователя которые сохраняются на протяжении недели.
 * @property int id
 * @property int user_id
 * @property string key
 * @property string value
 */
class UserWeeklyData extends Model
{
    use HasFactory;

    public static function getValue(int $user_id, string $key, mixed $default = null): mixed
    {
        $stat = self::where('user_id', $user_id)
            ->where('key', $key)->first();

        if ($stat && !empty($stat->value)) {
            return json_decode($stat->value);
        }

        return $default;
    }

    public static function setValue(int $user_id, string $key, $value): void
    {
        $stat = self::where('user_id', $user_id)
            ->where('key', $key)->first();

        if (!$stat) {
            $stat = new static();
            $stat->user_id = $user_id;
            $stat->key = $key;
        }

        $stat->value = json_encode($value);
        $stat->save();
    }

    public static function getIntValue(int $user_id, string $key): int
    {
        $stat = self::where('user_id', $user_id)
            ->where('key', $key)->first();

        if ($stat && !empty($stat->value)) {
            return intval(json_decode($stat->value));
        }

        return 0;
    }

    public static function getNumericValue(int $user_id, string $key): float
    {
        $stat = self::where('user_id', $user_id)
            ->where('key', $key)->first();

        if ($stat && !empty($stat->value)) {
            return floatval(json_decode($stat->value));
        }

        return 0;
    }

    public static function clearAll(): void
    {
        DB::table('user_weekly_data')->delete();
    }
}
