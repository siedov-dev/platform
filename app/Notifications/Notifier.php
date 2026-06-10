<?php

namespace App\Notifications;

abstract class Notifier
{
    private static $_api = null;


    private static function getApi(): NotifierApiInterface
    {
        if (self::$_api === null) {
            $class = config('notifications.class');
            self::$_api = new $class();
        }
        return self::$_api;
    }


    public static function send(Notification $ntf): bool
    {
        return self::getApi()->send($ntf);
    }
}
