<?php

namespace App\Models\Bonuses;

use Illuminate\Database\Eloquent\Model;

/**
 * @deprecated
 */
class BonusSettings extends Model
{
    public $table = 'bonuses_settings';
    public $timestamps = false;


    private static $_properties = [];


    public static function getProperty($bonusType, $name, $default)
    {
        if (isset(self::$_properties[$bonusType][$name])) {
            return self::$_properties[$bonusType][$name];
        }

        $prop = self::where([
            'bonus_type' => $bonusType,
            'property' => $name,
        ])->first();

        if (!isset(self::$_properties[$bonusType])) {
            self::$_properties[$bonusType] = [];
        }

        if ($prop === null) {
            self::$_properties[$bonusType][$name] = null;
            return $default;
        }
        self::$_properties[$bonusType][$name] = $prop->value;
        return $prop->value;
    }
}
