<?php

namespace App\Mail\Promo;

use App\Mail\SgMailable;
use App\MUser;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Mail\Mailer as MailerContract;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

/**
 * @deprecated
 */
class FirstDepReminderEmail extends SgMailable
{
    use Queueable, SerializesModels;

    /** @var MUser */
    private $_user;
    private $_mail_number;
    private $_subject;

    /**
     * Create a new message instance.
     *
     * @param MUser $user
     * @param int $mail_number
     * @param string $subject
     */
    public function __construct(MUser $user, int $mail_number, string $subject)
    {
        //
        $this->_user = $user;
        $this->_mail_number = $mail_number;
        $this->_subject = $subject;
        $this->_category = 'First deposit reminder';
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject($this->_subject)
            ->from('info@krakencassino.com')
            ->view('email.promo.first_dep.first_dep_'.$this->_mail_number, ['user' => $this->_user]);
    }
}
