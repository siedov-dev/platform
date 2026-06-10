<?php

namespace App\Notifications;

interface NotifierApiInterface
{
    public function send(Notification $ntf): bool;
}
