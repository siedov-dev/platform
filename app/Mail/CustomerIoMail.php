<?php

namespace App\Mail;

use App\CustomerIo\CustomerIo;
use App\Helpers\Hlog;
use App\Models\UnsubscribedEmail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CustomerIoMail implements EmailInterface
{
    private $_text;
    private $_html;
    private $_from = 'Kraken Casino <info@krakencassino.com>';
    private $_to;
    private $_subject;
    private $_sender;

    public function __construct($data)
    {
        $this->_text = isset($data['text']) ? $data['text'] : '';
        $this->_html = isset($data['html']) ? $data['html'] : '';
        $this->_subject = isset($data['subject']) ? $data['subject'] : '';
        $this->_to = isset($data['to']) ? $data['to'] : '';
        $this->_from = isset($data['from']) ? $data['from'] : MailConfig::getFrom();
        $this->_sender = isset($data['sender']) ? $data['sender'] : MailConfig::getFrom();
        Log::channel('mail')->info('create mail data:'.var_export($data));

        $this->_from = MailConfig::getFrom();
    }

    public function send(bool $force = false, bool $now = false, $category = 'default')
    {
        $mail_list = [];
        if (is_array($this->_to)) {
            $mail_list = $this->_to;
        } else {
            $mail_list[] = $this->_to;
        }

        //statistics
        $total = 0;
        $sent = 0;
        $not_sent = 0;
        $title = $this->_subject;
        $text = $this->_text;
        $html = $this->_html;
        $sender = $this->_sender;
        $cio = new CustomerIo();

        $result = false;
        Log::channel('mail')->info('CustomerIoMail: sending mail subject: ' . $this->_subject);
        Log::channel('mail')->info('CustomerIoMail: sending mail: ' . $this->_html);
        // Send Email
        if (is_string($this->_to)) {
            //do not send to unsubscribed email if force is not set
            if (!$force && UnsubscribedEmail::where('email', $this->_to)->first()) return false;
            Log::channel('mail')->info('CustomerIoMail: sending mail to: ' . $this->_to);

            try {
                $result = $cio->sendEmail($this->_from, $this->_subject, $this->_to, $this->_html);
                Log::channel('mail')->info('CustomerIoMail: sending mail result: ' . $this->_to, $result);
            } catch (\Throwable $e) {
                Hlog::logException($e);
            }

            $total++;
            $sent += $result ? 1 : 0;
            $not_sent += $result ? 0 : 1;

        } elseif (is_array($this->_to)) {
            set_time_limit(60000);
            Log::channel('mail')->info('CustomerIoMail: send list: ' . var_export($this->_to, true));

            foreach ($this->_to as $to) {
                Log::channel('mail')->info(' CustomerIoMail: sending mail to: ' . $to);
                //do not send to unsubscribed email if force is not set
                if (!$force && UnsubscribedEmail::where('email', $to)->first()) continue;

                try {
                    $result = $cio->sendEmail($this->_from, $this->_subject, $to, $this->_html);
                    Log::channel('mail')->info('CustomerIoMail: sending mail result: ' . $to, $result);
                    usleep(100000);
                } catch (\Throwable $e) {
                    Hlog::logException($e);
                }

                $total++;
                $sent += $result ? 1 : 0;
                $not_sent += $result ? 0 : 1;
            }

            $result = true;
        }

        DB::table('all_email_history')
            ->insert([
                'total' => $total,
                'sent' => $sent,
                'not_sent' => $not_sent,
                'title' => $title,
                'text' => $text,
                'html' => $html,
                'sender' => $sender,
                'created_at' => now()->format('Y-m-d H:i:s')
            ]);
        return $result;
    }
}
