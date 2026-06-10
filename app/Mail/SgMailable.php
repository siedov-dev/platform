<?php


namespace App\Mail;


use App\Email;
use Illuminate\Contracts\Mail\Mailer as MailerContract;
use Illuminate\Mail\Mailable;

/**
 * Mailable class use Sendgrid via App\Email to send mail
 * Class SgMailable
 * @package App\Mail
 */
class SgMailable extends Mailable
{
    protected string $_category = 'default';

    public function __construct(string $category = 'default')
    {
        $this->_category = $category;
    }

    public function send($mailer)
    {
        /** @var MailConfig $config */
        $config = MailConfig::first();

        if ($config !== null && $config->system === 'kraken') {
            parent::send($mailer);
            return;
        }

        $to = collect($this->to)->pluck('address')->toArray();
        if (count($to) == 1) $to = $to[0];
        $mail = $this->render();
        $email = new Email([
            'to' => $to,
            'html' => $mail,
            'subject' => $this->subject,
        ]);
        $email->send(true, true, $this->_category);
    }
}
