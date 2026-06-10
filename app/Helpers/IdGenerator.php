<?php

namespace App\Helpers;

class IdGenerator
{
    private static $_counter;

    public static function getId()
    {
        if (self::$_counter === null) {
            self::$_counter = rand(0, 100000);
        }
        return self::$_counter++;
    }
}