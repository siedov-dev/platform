<?php

namespace App\Mail;

use App\MUser;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class EmailVerification extends SgMailable
{
    use Queueable, SerializesModels;

    private $_user;

    /**
     * Create a new message instance.
     * @param MUser $user
     */
    public function __construct(MUser $user)
    {
        $this->_user = $user;
        $this->_category = 'Verification';
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject('KrakenCasino - confirm email')
            ->from('info@krakencassino.com')
            ->view('email.emailVerification', ['user' => $this->_user]);
    }
}
