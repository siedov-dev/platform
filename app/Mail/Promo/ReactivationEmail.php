<?php

namespace App\Mail\Promo;

use App\Mail\SgMailable;
use App\MUser;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

/**
 * @deprecated
 */
class ReactivationEmail extends SgMailable
{
    use Queueable, SerializesModels;

    /** @var MUser */
    private $_user;
    private $_mail_number;
    private $_subject;
    private $_promocode;

    /**
     * Create a new message instance.
     *
     * @param MUser $user
     * @param int $mail_number
     * @param string $subject
     * @param string $promocode
     */
    public function __construct(MUser $user, int $mail_number, string $subject, string $promocode = "")
    {
        //
        $this->_user = $user;
        $this->_mail_number = $mail_number;
        $this->_subject = $subject;
        $this->_promocode = $promocode;
        $this->_category = 'Reactivation';
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
            ->view('email.promo.reactivation.reactivation_'.$this->_mail_number, ['user' => $this->_user, 'promocode' => $this->_promocode]);
    }
}
