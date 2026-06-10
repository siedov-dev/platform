<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class Measure
{
    private static $_name;

    public static function setName(string $name)
    {
        self::$_name = $name;
    }
    public static function log(string $message)
    {
//        Log::channel('measure')->info('['.Device::detect() .'] '.self::$_name.' :: '.$message);
    }

    public static function logNow(string $place)
    {
        if (Auth::id() == 12) {
            Log::channel('measure')->info('['.Device::detect() .'] '.self::$_name.' :: '.$place.': '.(round((microtime(true) - LARAVEL_START) * 1000, 2)).' ms)');
        }
    }
}
