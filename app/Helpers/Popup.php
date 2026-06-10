<?php

namespace App\Helpers;


/**
 * Class Popup
 * This class sets message to be shown as popup to the client.
 *
 * Usage: Popup::message('Your profile was successfully saved.');
 */
class Popup
{
    private static $_msg = '';
    private static $_error = '';
    private static $_warn = '';

    public static function message(string $msg = null)
    {
        if ($msg === null) {
            return session()->has('msg') ? session()->get('msg') : self::$_msg;
        }
        self::$_msg = $msg;
        session()->flash('msg', $msg);
        return true;
    }

    public static function error(string $msg = null)
    {
        if ($msg === null) {
            return session()->has('error') ? session()->get('error') : self::$_error;
        }
        self::$_error = $msg;
        session()->flash('error', $msg);
        return true;
    }

    public static function warning(string $msg = null)
    {
        if ($msg === null) {
            return session()->has('warn') ? session()->get('warn') : self::$_warn;
        }
        self::$_warn = $msg;
        session()->flash('warn', $msg);
        return true;
    }
}
