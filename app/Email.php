<?php

namespace App;

use App\Mail\CustomerIoMail;
use App\Mail\EmailInterface;
use App\Mail\FakeMail;
use App\Mail\KrakenMail;
use App\Mail\MailConfig;
use App\Mail\SendgridMail;
use App\Mail\Unisender\UnisenderMail;

class Email implements EmailInterface
{
    private string $_text;
    private string$_html;
    private string $_from = 'Kraken Casino <info@krakencassino.com>';
    private string $_to;
    private string $_subject;
    private string $_sender;

    private EmailInterface $mail_system;

    public function __construct($data, $mail_system = null)
    {
        if ($mail_system === null) {
            $system = MailConfig::getSystem();
        } else {
            $system = $mail_system;
        }

        switch ($system) {
            case 'kraken':
                $this->mail_system = new KrakenMail($data);
                break;
            case 'sengrid':
                $this->mail_system = new SendgridMail($data);
                break;
            case 'fakemail':
                $this->mail_system = new FakeMail($data);
                break;
            case 'customer_io':
                $this->mail_system = new CustomerIoMail($data);
                break;
            case 'unisender':
            default:
                $this->mail_system = new UnisenderMail($data);
                break;
        }

    }

    /**
     * message will be sent by mail server
     * just store data to remote db
     * send mail
     * @param bool $now immediately send mail
     * @param bool $force ignore unsubscribed
     * @return bool
     */
    public function send(bool $force = false, bool $now = false, $category = 'default')
    {
        return $this->mail_system->send($force, $now, $category);
    }

    /**
     * creates html mail version
     * for karkencassino.com
     * @param string $message
     * @param string $title
     * @return string
     * @deprecated
     */
    public static function createKrakenMailHtml(string $message, string $title = 'Krakencassino'): string
    {
        if (stripos($message, '<html') !== null) return $message;

        return '<html>
                            <head>
                            <title>' . $title . '</title>
                            </head>
                            <body style="background-color: black; color: white; background-image: url(\'https://krakencassino.com/img/email-header.jpg\');
                            background-position: right top; background-size: 100% 100%; min-height: 400px;">
                            <h1 style="width: 100%; margin: 25px; text-align: center; color: #ecc138"><a href="https://krakencassino.com">Krakencassino</a></h1>
                            <div style="border: 2px #0d8ddb solid; border-radius: 1em; background: #0f008755; min-height: 200px; margin: 20px; padding: 15px; font-size: large; ">
                                ' . $message . '
                                 </div>
                                 <br>
                                 <div style="width: 100%; text-align: center;">
                            <a style="font-size: large; color: white; margin-right: 15px; margin-top: 50px;" href="https://t.me/KrakenCasinoSup_bot">Мы в телеграмм</a>
                            </div>
                            </body>
                             </html>';
    }

}
