<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Управляет разнообразными данными нужными для системы.
 * В данный момент не используется.
 * @property int $id
 * @property string $key
 * @property string $value
 */
class SiteData extends Model
{
    use HasFactory;

    protected $guarded = [];

    public static function setValue(string $key, string $value): SiteData
    {
        /** @var SiteData $data */
        $data = self::updateOrCreate(['key' => $key,], ['value' => $value]);
        $data->save();
        return $data;
    }

    public static function saveJson(string $key, mixed $value): SiteData
    {
        $data = self::updateOrCreate(['key' => $key,], ['value' => json_encode($value)]);
        $data->save();
        return $data;
    }

    public static function getValue(string $key, string $default = ''): string
    {
        return self::where('key', $key)->first()?->value?? $default;
    }

    public static function getFromJson(string $key, mixed $default): mixed
    {
        return json_decode(self::where('key', $key)->first()?->value?? '')?? $default;
    }

    public static function keyExist(string $key): bool
    {
        return self::where('key', $key)->first() !== null;
    }

    public static function getInt(string $key, int $default = 0): int
    {
        return intval(self::where('key', $key)->first()?->value ?? $default);
    }
}
