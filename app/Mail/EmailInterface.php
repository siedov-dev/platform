<?php


namespace App\Mail;


interface EmailInterface
{
    /**
     * send mail
     * @param bool $now immediately send mail
     * @param bool $force ignore unsubscribed
     * @return bool
     */
    public function send(bool $force = false, bool $now = false, $category = 'default');
}
