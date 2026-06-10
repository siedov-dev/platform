<?php


namespace App\Mail\Unisender;


use App\Email;
use App\Mail\EmailInterface;
use App\Mail\MailConfig;
use App\Models\MailSubscribe;
use App\Models\UnsubscribedEmail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class UnisenderMail implements EmailInterface
{
//    protected string $key = "6eb6kxj8wuw76ya4brrtpm74eq9dyuueu8w5aoje";
    protected string $key = "6xhcm9rikbi68gunj1jnbq7diqwr6d3uska5tdzy";

    private $_text;
    private $_html;
    private $_from = 'Kraken Casino <info@krakenmail.info>';
    private $_to;
    private $_subject;
    private $_sender;

    private UnisenderApi $uni;

    const MAX_PACKET_MAILS = 250;

    const SINGLE_GROUP_ID = 1;
    const MASS_GROUP_ID = 37;

    const RETRY_TIMES = 20;
    const RETRY_TIMEOUT = 5; //sec

    /**
     * UnisenderMail constructor.
     * @param array $data
     */
    public function __construct(array $data)
    {
        $this->_text = $data['text'] ?? '';
        $this->_html = $data['html'] ?? '';
        $this->_subject = $data['subject'] ?? '';
        $this->_to = $data['to'] ?? '';
        $this->_from = $data['from'] ?? 'Kraken Casino <'.MailConfig::getFrom().'>';
        $this->_sender = $data['sender'] ?? MailConfig::getFrom(); //$data['sender'] ?? 'info@krakenmail.info';
        $this->key = MailConfig::getUnisenderKey();
        $this->uni = new UnisenderApi($this->key);

//        $this->_sender = 'info@krakencassino.com';

    }

    public function send(bool $force = false, bool $now = false, $category = 'default')
    {
        if (empty($this->_to)) {
            Log::channel('mail')->error('empty to list. Subj:' . $this->_subject);
            return;
        }

        set_time_limit(ini_get('max_execution_time') + 6000);
        sleep(1);
        Log::channel('mail')->info('Subj:' . $this->_subject);
        Log::channel('mail')->info('Unisend Email: send list: ' . var_export($this->_to, true));

        //clear unsub link
        if (preg_match('#<td.*\*UNSUB\*.*</td>#', $this->_html, $matches)) {
            $this->_html = str_replace($matches[0], "", $this->_html);
        }

//        Log::channel('mail')->info('mail body: ' . var_export($this->_html, true));

        if (is_array($this->_to)) {
            if (!$force) {
                $mail_list = MailSubscribe::filterEmails($this->_to);
                Log::channel('mail')->info('Unisend Email: filtered send list: ' . var_export($this->_to, true));
            } else {
                $mail_list = $this->_to;
            }

        } else {
            if (!$force && !MailSubscribe::isEmailSubscribed($this->_to)) {
                Log::channel('mail')->info('Unisend Email: email not subscribed and not forced, abort email: ' . var_export($this->_to, true));
                return false;
            }
            if ($now) {
                $t = 0;
                do {
                    Log::channel('mail')->info('sending direct email');
                    $resp = json_decode($this->uni->sendEmail([
                        'email' => $this->_to,
                        'sender_name' => '🐙 Kraken',
                        'sender_email' => $this->_sender,
                        'subject' => $this->_subject,
                        'body' => $this->_html,
                        'list_id' => self::SINGLE_GROUP_ID,
                    ]), true);
                    $t++;
                    Log::channel('mail')->info($resp);
                    if (!empty($resp['error']) && strpos($resp['error'], 'Api call limit') !== false) sleep(self::RETRY_TIMEOUT);
                } while (!empty($resp['error']) && strpos($resp['error'], 'Api call limit') !== false && $t < self::RETRY_TIMES);
                return;
            }
            $mail_list = [$this->_to];
        }

        $mails = [];

        $limit = MailConfig::getMaxMails();
        $c = 1;
        foreach ($mail_list as $mail_to) {

            if (!preg_match('/^([a-zA-Z0-9_\-.]+)@[a-zA-Z0-9\-]+\.[a-zA-Z0-9\-.]+$/', $mail_to)) continue;
            $mails[] = $mail_to;
            $c++;

            if ($c > $limit) {
                try {
                    $this->sendBlock($mails);
                    $c = 1;
                    $mails = [];
                } catch (\Exception $e) {
                    Log::channel('mail')->error($e->getMessage());
                }
            }
        }

        if (!empty($mails)) $this->sendBlock($mails);
    }

    public function sendBlock(&$mail_list)
    {
        Log::channel('mail')->info('sendblock:' . json_encode($mail_list));
        //$uni->importContacts(['field_names' => ['email', 'email_list_ids'], 'data' => [['destgtk@gmail.com', '1,2'], ['payboxultra@gmail.com', '1,2']], 'overwrite_tags' => 1, 'overwrite_lists' => 1])
        $import_users = [];
        foreach ($mail_list as $mail) {
            $import_users[] = [$mail, '' . self::SINGLE_GROUP_ID . ',' . self::MASS_GROUP_ID];
        }

        $t = 0;
        do {
            $resp = json_decode($this->uni->importContacts([
                'field_names' => ['email', 'email_list_ids'],
                'data' => $import_users,
            ]), true);
            $t++;
            Log::channel('mail')->info('Unisender import:' . json_encode($resp));
            if (!empty($resp['error']) && strpos($resp['error'], 'Api call limit') !== false) sleep(self::RETRY_TIMEOUT);
        } while (!empty($resp['error']) && strpos($resp['error'], 'Api call limit') !== false && $t < self::RETRY_TIMES);
        sleep(1);

        $bad_emails = self::invalidEmailFromImport(json_encode($resp));
        Log::channel('mail')->info('bad emails:' . json_encode($bad_emails));
        $mail_list = array_diff($mail_list, $bad_emails);
        Log::channel('mail')->info('new mail list:' . json_encode($mail_list));
        if (empty($mail_list)) {
            Log::channel('mail')->info('empty send list skip');
            return;
        }

        $t = 0;
        do {
            $email = json_decode($this->uni->createEmailMessage([
                'sender_name' => '🐙 Kraken',
                'sender_email' => $this->_sender,
                'subject' => $this->_subject,
                'body' => $this->_html,
                'list_id' => self::MASS_GROUP_ID,
            ]), true);
            $t++;
            Log::channel('mail')->info('Unisender create email:' . json_encode($email));
            if (!empty($email['error']) && strpos($email['error'], 'Api call limit') !== false) sleep(self::RETRY_TIMEOUT);
        } while (!empty($email['error']) && strpos($email['error'], 'Api call limit') !== false && $t < self::RETRY_TIMES);

        if (isset($email['error'])) {
            Log::channel('mail')->error('Unisender mass send error:' . $email['error']);
            return;
        }

        $message_id = $email['result']['message_id'];

        $m_list = (implode(',', $mail_list));

        $t = 0;
        do {
            Log::channel('mail')->info('Unisender create campaign params:' . json_encode([
                    'message_id' => $message_id,
                    'contacts' => $m_list,
                    'track_read' => 1,
                    'track_links' => 1,
                ]));
            Log::channel('mail')->info('size of data:' . mb_strlen($m_list));
            $campaign = json_decode($this->uni->createCampaign([
                'message_id' => $message_id,
                'contacts' => $m_list,
                'track_read' => 1,
                'track_links' => 1,
            ]), true);
            $t++;
            Log::channel('mail')->info('Unisender create campaign:' . json_encode($campaign));
            if (!empty($campaign['error']) && strpos($campaign['error'], 'Api call limit') !== false) sleep(self::RETRY_TIMEOUT);
        } while (!empty($campaign['error']) && strpos($campaign['error'], 'Api call limit') !== false && $t < self::RETRY_TIMES);
        sleep(2);
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
                            <a style="font-size: large; color: white; margin-right: 15px; margin-top: 50px;" href="https://t.me/KrakenCasinoSup_bot">Мы в телеграмм</a>
                            </div>
                            </body>
                             </html>';
    }

    public static function testUnisend($to)
    {
        $email = new UnisenderMail([
            'subject' => 'test send',
            'to' => $to,
            'html' => "Unisender mail send test <strong> with PHP</strong>",
        ]);
        try {
            $email->send();
        } catch (\Exception $e) {
            echo 'Caught exception: ' . $e->getMessage() . "\n";
        }
    }

    public static function invalidEmailFromImport(string $resp)
    {
        preg_match_all('#\\\"(.*?@.*?)\\\"#', $resp, $matches);
        return $matches[1] ?? [];
    }
}

/*
 $resp = '{"result":{"total":200,"inserted":0,"updated":183,"deleted":0,"new_emails":9,"invalid":17,"log":[{"index":"125","code":"w_address__w_possibletypo","message":"Warning (but email was added). Possible typo in the domain name for the email address: stepanggg02@maiil.ru"},{"index":"137","code":"e_address__e_syntax","message":"Error (email was ignored). Invalid format for the email address: \"eghrth5@ede.yh\""},{"index":"140","code":"e_address__e_syntax","message":"Error (email was ignored). Invalid format for the email address: \"efefefr@werg.rhr\""},{"index":"141","code":"e_address__e_syntax","message":"Error (email was ignored). Invalid format for the email address: \"evrevvre@werf.rh\""},{"index":"142","code":"e_address__e_syntax","message":"Error (email was ignored). Invalid format for the email address: \"wecwcw@wef.rth\""},{"index":"143","code":"e_address__e_syntax","message":"Error (email was ignored). Invalid format for the email address: \"svds@ef.reh\""},{"index":"144","code":"e_address__e_syntax","message":"Error (email was ignored). Invalid format for the email address: \"evrever@erg.rth\""},{"index":"145","code":"e_address__e_syntax","message":"Error (email was ignored). Invalid format for the email address: \"vdsvsvsvsv@ggrt.eghreg\""},{"index":"146","code":"e_address__e_syntax","message":"Error (email was ignored). Invalid format for the email address: \"egegeegr@erg.erg\""},{"index":"147","code":"e_address__e_syntax","message":"Error (email was ignored). Invalid format for the email address: \"qdqdqdqw@reg.hrt\""},{"index":"148","code":"e_address__e_syntax","message":"Error (email was ignored). Invalid format for the email address: \"qdq222dqdqw@reg.hrt\""},{"index":"149","code":"e_address__e_syntax","message":"Error (email was ignored).
Invalid format for the email address: \"qdq444dqdqw@reg.hrt\""},{"index":"150","code":"e_address__e_syntax","message":"Error (email was ignored). Invalid format for the email address: \"gtjtyyj@efw.ujj\""},{"index":"151","code":"e_address__e_syntax","message":"Error (email was ignored). Invalid format for the email address: \"g4g4g4g4@rgg.hh\""},{"index":"152","code":"e_address__e_syntax","message":"Error (email was ignored). Invalid format for the email address: \"w4fwfwf@hrh.rthr\""},{"index":"153","code":"e_address__e_syntax","message":"Error (email was ignored). Invalid format for the email address: \"f3f3f@erg.rth\""},{"index":"154","code":"e_address__e_syntax","message":"Error (email was ignored). Invalid format for the email address: \"egegegee@rge.rth\""},{"index":"155","code":"e_address__e_syntax","message":"Error (email was ignored). Invalid format for the email address: \"rtrbrb@erg.rth\""}]}}';

 */
