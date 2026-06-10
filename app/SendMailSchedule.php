<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

/**
 * Планировщик отправки email
 * Class SendMailSchedule
 * @property int status
 * @property \DateTime send_time
 * @property string message
 * @property string emails
 */
class SendMailSchedule extends Model
{
    //
    const STATUS_NOT_ACTIVE=0;
    const STATUS_ACTIVE=1;
    const STATUS_SENT=2;
    const STATUS_ERROR=3;

    const SEND_FROM_KRAKEN = 0;
    const SEND_FROM_GAMEINVESTS = 1;

    protected $connection = "mail";
    public $table = "send_mail_schedules";

    public static function process()
    {
        //moved to mail server
        return;
        /**
         * @var SendMailSchedule $sc
         */
        $sc=static::where('status',static::STATUS_ACTIVE)
            ->where('send_time', '<', date('Y-m-d H:i:s'))
            ->first();

        if($sc) {
            Log::info('Sending scheduled email');

            $sc->status=self::STATUS_SENT;
            $sc->save();

            $emails=json_decode($sc->emails);

            if(!is_array($emails)){
                $sc->status=self::STATUS_ERROR;
                $sc->save();
            }

                    if($sc->from == 1){
                    $letter = new \App\Email([
                            'to' => $emails,
                            'subject' => 'Gameinvests',
                            'text' => $sc->message,
                            'html' => '
                        <html>
                            <head>
                            <title>Gameinvests</title>
                            </head>
                            <body style="background-color: #00000066; color: white; background-image: url(\'https://gameinvests.com/img/landing/main-bg.jpg\');
                            background-position: right top; background-size: 100% 100%; min-height: 400px;">
                                <h1 style="width: 100%; margin: 25px; text-align: center; color: #ecc138">Gameinvests</h1>
                                <p style="border: 2px #0d8ddb solid; border-radius: 1em; background: #0f0087b0; min-height: 200px; margin: 20px; padding: 15px; font-size: large; ">
                                    ' . $sc->message . '
                                     </p>
                                     <br>
                             <div style="width: 100%; text-align: right;"><a style="font-size: x-large; color: white; margin-right: 15px; margin-top: 50px;" href="https://gameinvests.com">Gameinvests.com</a></div>
                            </body>
                             </html>
                        ',
                        'sender' => 'support@gameinvests.com',
                        'from' => 'Gameinvests <support@gameinvests.com>',
                        ]);

                    } else {

                        $letter = new \App\Email([
                            'to' => $emails,
                            'subject' => 'Krakencassino',
                            'text' => $sc->message,
                            'html' => \App\Email::createKrakenMailHtml($sc->message),
                        ]);
                    }

                    if (!$letter->send()) {
                        $sc->status = self::STATUS_ERROR;
                    }

            $sc->save();

        }
    }
}
