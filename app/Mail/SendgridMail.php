<?php


namespace App\Mail;


use App\Email;
use App\Models\UnsubscribedEmail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SendgridMail implements EmailInterface
{
    private $_text;
    private $_html;
    private $_from = 'Kraken Casino <info@krakencassino.com>';
    private $_to;
    private $_subject;
    private $_sender;

    const MAX_PACKET_MAILS = 500;

    protected $sg_key = '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';

    public function __construct($data)
    {
        $this->_text = isset($data['text']) ? $data['text'] : '';
        $this->_html = isset($data['html']) ? $data['html'] : '';
        $this->_subject = isset($data['subject']) ? $data['subject'] : '';
        $this->_to = isset($data['to']) ? $data['to'] : '';
        $this->_from = isset($data['from']) ? $data['from'] : 'Kraken Casino <info@krakencassino.com>';
        $this->_sender = isset($data['sender']) ? $data['sender'] : 'info@krakencassino.com';

        $this->_from = MailConfig::getFrom();
        $this->sg_key = MailConfig::getSendgridKey();
    }


    public function sendOneByOne(bool $force = false, bool $now = false)
    {
        set_time_limit(60000);
        Log::channel('mail')->info('Email: send list: ' . var_export($this->_to, true));

        $mail_list = [];

        if (is_array($this->_to)) {
            $mail_list = $this->_to;
        } else {
            $mail_list[] = $this->_to;
        }

        foreach ($mail_list as $mail_to) {
            if (!$force && UnsubscribedEmail::where('email', $mail_to)->first() !== null) continue;
            if (!preg_match('/^([a-zA-Z0-9_\-.]+)@[a-zA-Z0-9\-]+\.[a-zA-Z0-9\-.]+$/', $mail_to)) continue;

            $token = hash('sha256', $mail_to . '128tm342--');

            //add unsubscribe link
            if (stripos('***UNSUB***', $this->_html) === null && stripos('*|UNSUB|*', $this->_html) === null) {
                $this->_html = str_replace('</body>', '<div style="width: 100%; text-align: right;"><a style="font-size: x-small; color: white; margin-right: 15px; margin-top: 50px;" href="https://krakencassino.com/unsubscribe?email=' . $this->_to . '&token=' . $token . '">Отписатся от рассылки</a></div> </body>', $this->_html);
            } else {
                $this->_html = str_replace('***UNSUB***', "https://krakencassino.com/unsubscribe?email=' . $mail_to . '&token=' . $token . '", $this->_html);
                $this->_html = str_replace('*|UNSUB|*', "https://krakencassino.com/unsubscribe?email=' . $mail_to . '&token=' . $token . '", $this->_html);
            }

            $headers = [];
            $headers['List-Unsubscribe'] = "<https://krakencassino.com/unsubscribe";

            $email = new \SendGrid\Mail\Mail();
            $email->setFrom($this->_sender);
            $email->setSubject($this->_subject);
            $email->addContent("text/plain", $this->_html);
            $email->addContent(
                "text/html", $this->_html
            );
            $email->addHeaders($headers);

            $email->addTo($mail_to);

            if ($force) $email->disableBypassListManagement();

            $sendgrid = new \SendGrid($this->sg_key);
            try {
                $response = $sendgrid->send($email);
                Log::channel('mail')->info(var_export($response->statusCode(), true));
                Log::channel('mail')->info(var_export($response->headers(), true));
                Log::channel('mail')->info(var_export($response->body(), true));
            } catch (\Exception $e) {
                Log::channel('mail')->info($e->getMessage());
            }

        }
    }

    public function send(bool $force = false, bool $now = false, $category = 'default')
    {
        set_time_limit(60000);
        Log::channel('mail')->info('Email: send list: ' . var_export($this->_to, true));

        $token = hash('sha256', 'asdasd' . '128tm342--');
        //add unsubscribe link
        if (stripos('***UNSUB***', $this->_html) === null && stripos('*|UNSUB|*', $this->_html) === null) {
            $this->_html = str_replace('</body>', '<div style="width: 100%; text-align: right;"><a style="font-size: x-small; color: white; margin-right: 15px; margin-top: 50px;" href="***UNSUB***">You can unsubscribe</a></div> </body>', $this->_html);
        } else {
//            $this->_html = str_replace('***UNSUB***', "https://krakencassino.com/unsubscribe", $this->_html);
            $this->_html = str_replace('*|UNSUB|*', "***UNSUB***", $this->_html);
        }

        $mail_list = [];

        if (is_array($this->_to)) {
            $mail_list = $this->_to;
        } else {
            $mail_list[] = $this->_to;
        }

        $mails = [];
        $c = 1;
        foreach ($mail_list as $mail_to) {

            if (!preg_match('/^([a-zA-Z0-9_\-.]+)@[a-zA-Z0-9\-]+\.[a-zA-Z0-9\-.]+$/', $mail_to)) continue;
            $mails[] = $mail_to;
            $c++;
            $limit = MailConfig::getMaxMails();

            if ($c > $limit) {
                $this->sendBlock($mails, $force, $category);
                $c = 1;
                $mails = [];
            }
        }

        if (!empty($mails)) $this->sendBlock($mails, $force, $category);
    }

    protected function sendBlock(&$mail_list, $force, $category)
    {
        Log::channel('mail')->info('Email: send block: ' . var_export($mail_list, true));

        $headers = [];
        $headers['List-Unsubscribe'] = "<https://krakencassino.com/unsubscribe";

        $email = new \SendGrid\Mail\Mail();
        $email->setFrom($this->_sender);
        $email->setSubject($this->_subject);
        $email->addContent("text/plain", $this->_html);
        $email->addCategory($category);
        $email->addContent(
            "text/html", $this->_html
        );
        $email->addHeaders($headers);

        foreach ($mail_list as $mail_to) {
            $email->addTo($mail_to);
        }

        if ($force) $email->disableBypassListManagement();

        if (empty(getenv('SENDGRID_API_KEY'))) putenv("SENDGRID_API_KEY=".env('SENDGRID_API_KEY'));
        Log::info('env:'.env('SENDGRID_API_KEY'));
        Log::info(getenv('SENDGRID_API_KEY'));
        Log::info(getenv('DB_DATABASE'));
//        if (empty(getenv('SENDGRID_API_KEY'))) throw new \Exception('SENDGRID_API_KEY not set!');

        $sendgrid = new \SendGrid($this->sg_key);
        try {
            $response = $sendgrid->send($email);
            Log::channel('mail')->info(var_export($response->statusCode(), true));
            Log::channel('mail')->info(var_export($response->headers(), true));
            Log::channel('mail')->info(var_export($response->body(), true));
        } catch (\Exception $e) {
            Log::channel('mail')->info($e->getMessage());
        }
    }

    /**
     * creates html mail version
     * for karkencassino.com
     * @param string $message
     * @param string $title
     * @return string
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
                            <a style="font-size: large; color: white; margin-right: 15px; margin-top: 50px;" href="https://t.me/Kazino_Kraken">Мы в телеграмм</a>
                            </div>
                            </body>
                             </html>';
    }

    public static function testSendgrid($to)
    {
        $email = new \SendGrid\Mail\Mail();
        $email->setFrom("info@krakencassino.com");
        $email->setSubject("Sending with SendGrid is Fun");
        $email->addTo($to);
        $email->addContent("text/plain", "and easy to do anywhere, even with PHP");
        $email->addContent(
            "text/html", "<strong>and easy to do anywhere, even with PHP</strong>"
        );
        dump(env('SENDGRID_API_KEY'));
        $sendgrid = new \SendGrid(env('SENDGRID_API_KEY'));
        try {
            $response = $sendgrid->send($email);
            print $response->statusCode() . "\n";
            print_r($response->headers());
            print $response->body() . "\n";
        } catch (Exception $e) {
            echo 'Caught exception: ' . $e->getMessage() . "\n";
        }
    }
}
