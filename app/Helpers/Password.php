<?php

namespace App\Helpers;

class Password
{
    const LENGTH = 6;

    public static function generate()
    {
        $chars = '1234567890';
        $numChars = strlen($chars);
        $string = '';
        for ($i = 0; $i < self::LENGTH; $i++) {
            $string .= substr($chars, rand(1, $numChars) - 1, 1);
        }
        return $string;
    }


    public static function hash(string $password) : string
    {
        return md5(md5($password) . 'q3jkp455ct6e64d5');
    }
}