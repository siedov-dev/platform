<?php

namespace App\Models;

use App\Helpers\BladeString;
use App\Helpers\Localization;
use App\Mail\MailConfig;
use App\Models\Crm\TelegramClient;
use App\Models\Popups\UserPopup;
use App\MUser;
use App\Telegram\Models\TelegramUser;
use App\Telegram\TelegramBot;
use App\Viber\Models\ViberUser;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;

/**
 * Class MessageBlank
 * Заготовки сообщений для рассылок.
 *
 * @property int $id
 * @property int $type
 * @property string $name
 * @property string $sms_text
 * @property string $emails
 * @property string $tg_message
 * @property string $lang_messages
 * @property string $tg_media_type
 * @property string $tg_image
 * @property string $lang_img_urls
 * @property string $viber_message
 * @property string $viber_media_type
 * @property string $viber_image
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
class MessageBlank extends Model
{
    const TYPE_DEFAULT = 0;
    const TYPE_RETENTION = 1;
    const TYPE_DAILY = 2;
    const TYPE_REGISTER = 3;
    const TYPE_PASSWORD = 4;

    const STATUS_PENDING = 0;
    const STATUS_IN_PROGRESS = 1;
    const STATUS_DONE = 2;
    const STATUS_ERROR = 3;

    const METHOD_EMAIL = "email";
    const METHOD_VIBER = "viber";
    const METHOD_TELEGRAM = "telegram";
    const METHOD_TG_CHAT = "tg_chat";

    //
    protected $dates = [
        'created_at',
        'updated_at',
    ];

    public static function getDefault()
    {
        return MessageBlank::where('type', self::TYPE_DEFAULT)->get();
    }

    public function getTypeName()
    {
        return self::typeName($this->type);
    }

    public static function allTypes(): array
    {
        return [
            self::TYPE_DEFAULT,
            self::TYPE_RETENTION,
            self::TYPE_DAILY,
            self::TYPE_REGISTER,
            self::TYPE_PASSWORD,
        ];
    }

    public static function typeName($type): string
    {
        switch ($type) {
            case self::TYPE_DEFAULT:
                return 'default';
            case self::TYPE_RETENTION:
                return 'Ретейшн';
            case self::TYPE_DAILY:
                return 'Бонусные дни';
            case self::TYPE_REGISTER:
                return 'Регистрация';
            case self::TYPE_PASSWORD:
                return 'Пароли';
        }
        return 'unknown';
    }

    public function sendToTelegramChannel(array $blade_data = [], $locale = 'en')
    {
        try {
            $curr_locale = App::getLocale();
            log::channel('telegram')->info('telegram sending messages to channel from blank, blank#'.$this->id);
            Localization::setAppLocale($locale);

            $locale_msgs = json_decode($this->lang_messages, true)?? [];
            $locale_imgs = json_decode($this->lang_img_urls, true)?? [];

            if (empty($this->tg_message)) {
                log::channel('telegram')->info('telegram messages is empty, blank#'.$this->id);
//            return;
            }

            Log::channel('telegram')->info("pattern message:");
            if (!empty($locale_msgs[$locale])) {
                Log::channel('telegram')->info("got from localized");
                $blade_tmp = $locale_msgs[$locale];
            } else {
                Log::channel('telegram')->info("got from tg_message");
                $blade_tmp = $this->tg_message;
            }
            Log::channel('telegram')->info($blade_tmp);
            $message = BladeString::bladeCompile($blade_tmp, $blade_data);
            Log::channel('telegram')->info("compiled message:");
            Log::channel('telegram')->info($message);

            if (!empty($locale_imgs[$locale])) {
                TelegramBot::sendChannelPhoto($locale_imgs[$locale], $message);
            } else {
                if ($this->tg_media_type === 'image') {
                    TelegramBot::sendChannelPhotoData($this->tg_image, $message);
                } else {
                    TelegramBot::sendChannelMessage($message);
                }
            }
            Log::channel('telegram')->info("sended");
            Localization::setAppLocale($curr_locale);
        } catch (\Exception $e) {
            Log::channel('telegram')->error($e->getMessage());
        }
    }

    /**
     * send telegram message to users
     * @param array $user_ids
     * @param array $blade_data
     */
    public function sendTelegram(array $user_ids, array $blade_data = [])
    {
        $schedule = new MessageBlankSchedule();
        $schedule->setBlankId($this->id);
        $schedule->setIds(json_encode($user_ids));
        $schedule->setBladeData(json_encode($blade_data));
        $schedule->setTotal(count($user_ids));
        $schedule->setMethod(self::METHOD_TELEGRAM);
        $schedule->setStatus(self::STATUS_PENDING);
        if ($schedule->getTotal() === 1) $schedule->setStatus(self::STATUS_IN_PROGRESS);
        $schedule->save();
        if ($schedule->getTotal() === 1) $this->sendTelegramSchedule($schedule->getId());
    }

    public static function processTelegramSchedule()
    {
        /** @var MessageBlankSchedule $schedule */
        $schedule = MessageBlankSchedule::where('status', self::STATUS_PENDING)
            ->where('method', self::METHOD_TELEGRAM)
            ->first();

        if ($schedule) {
            $schedule->setStatus(self::STATUS_IN_PROGRESS);
            $schedule->save();

            /** @var MessageBlank $blank */
            $blank = MessageBlank::find($schedule->getBlankId());

            if ($blank === null) {
                $schedule->setStatus(self::STATUS_ERROR);
                $schedule->setLastError("no blank found");
                $schedule->save();
                return;
            }

            $blank->sendTelegramSchedule($schedule->getId());
        }
    }

    /**
     * @param int $schedule_id
     * @return void
     */
    public function sendTelegramSchedule(int $schedule_id)
    {
        /** @var MessageBlankSchedule $schedule */
        $schedule = MessageBlankSchedule::find($schedule_id);
        $user_ids = json_decode($schedule->getIds(), true);
        $blade_data = json_decode($schedule->getBladeData(), true);

        $t_users = TelegramUser::whereIn('user_id', $user_ids)->get();

        log::channel('telegram')->info('telegram sending messages from blank, blank#'.$this->id);
        $locale_msgs = [];;
        $locale_imgs = [];
        if (!empty($this->lang_messages)) $locale_msgs = json_decode($this->lang_messages, true);
        if (!empty($this->lang_img_urls)) $locale_imgs = json_decode($this->lang_img_urls, true);

        if (empty($this->tg_message)) {
            log::channel('telegram')->info('telegram messages is empty, blank#'.$this->id);
//            return;
        }
        $curr_locale = App::getLocale();

        /** @var TelegramUser $t_user */
        foreach ($t_users as $t_user) {

            $schedule_log = new MessageBlankLog();
            $schedule_log->setUserId($t_user->user_id);
            $schedule_log->setMethod(self::METHOD_TELEGRAM);
            $schedule_log->setScheduleId($schedule_id);

            try {
                /** @var TelegramUser $t_user */
                Log::channel('telegram')->info($t_user->telegram_id);
//                $m_user = $t_user->getMUser();
//                Localization::setAppLocale($m_user->getLocale());
                Localization::setAppLocale($t_user->getLocale());
                Log::channel('telegram')->info("users locale ".$t_user->getLocale());
                Log::channel('telegram')->info("pattern message:");

                if (!empty($locale_msgs[$t_user->getLocale()])) {
                    Log::channel('telegram')->info("got from localized");
                    $blade_tmp = $locale_msgs[$t_user->getLocale()];
                } else {
                    Log::channel('telegram')->info("got from tg_message");
                    $blade_tmp = $this->tg_message;
                }

                Log::channel('telegram')->info($blade_tmp);

                $message = BladeString::bladeCompile($blade_tmp, $blade_data);

                Log::channel('telegram')->info("compiled message:");
                Log::channel('telegram')->info($message);

                $resp = "";

                if (!empty($locale_imgs[$t_user->getLocale()])) {
                    $resp = TelegramBot::sendChatPhoto($locale_imgs[$t_user->getLocale()], $message, $t_user->telegram_id);
                } else {
                    if ($this->tg_media_type === 'image') {
                        $resp = TelegramBot::sendChatPhotoData($this->tg_image, $message, $t_user->telegram_id);
                    } else {
                        if (empty($message)) {
                            Log::channel('telegram')->info("pattern message is empty");
                            $resp = "pattern message is empty";
                            $schedule_log->setResponse(mb_strimwidth($resp, 0, 200, "..."));
                            $schedule_log->save();
                            $schedule->setErrors($schedule->getErrors() + 1);
                            $schedule->save();
                            continue;
                        }
                        $resp = TelegramBot::sendChatMessage($message, $t_user->telegram_id);
                    }
                }

                Log::channel('telegram')->info("sent");
                $schedule_log->setResponse(mb_strimwidth($resp, 0, 200, "..."));

                $schedule->setSent($schedule->getSent() + 1);
                $schedule->save();

            } catch (\Exception $e) {
                Log::channel('telegram')->error($e->getMessage());
                $schedule_log->setResponse(mb_strimwidth($e->getMessage(), 0, 200, '...'));
                $schedule->setErrors($schedule->getErrors() + 1);
                $schedule->save();
            }

            $schedule_log->save();
        }

        $schedule->setStatus(self::STATUS_DONE);
        $schedule->save();

        Localization::setAppLocale($curr_locale);
    }

    public function popupMessages(array $user_ids, array $blade_data = [], $category = 'retention')
    {
        $users = MUser::whereIn('id', $user_ids)->get();

        log::channel('retention')->info('popupMessage sending messages from blank, blank#'.$this->id);
        $locale_msgs = [];;
        $locale_imgs = [];
        if (!empty($this->lang_messages)) $locale_msgs = json_decode($this->lang_messages, true);
        if (!empty($this->lang_img_urls)) $locale_imgs = json_decode($this->lang_img_urls, true);
        $schedule_letters = json_decode($this->emails, true);

        if (empty($this->tg_message)) {
            log::channel('retention')->info('popupMessage messages is empty, blank#'.$this->id);
//            return;
        }
        $curr_locale = App::getLocale();

        foreach ($users as $user) {
            try {
                /** @var MUser $user */
                Localization::setAppLocale($user->getLocale());
                Log::channel('retention')->info("users locale ".$user->getLocale());
                Log::channel('retention')->info("pattern message:");

                $subject = BladeString::bladeCompile($schedule_letters['subject_' . $user->getLocale()]??
                    $schedule_letters['subject_en']?? '', $blade_data);

                if (!empty($locale_msgs[$user->getLocale()])) {
                    Log::channel('retention')->info("got from localized");
                    $blade_tmp = $locale_msgs[$user->getLocale()];
                } else {
                    Log::channel('retention')->info("got from tg_message");
                    $blade_tmp = $this->tg_message;
                }
                Log::channel('retention')->info($blade_tmp);
                $message = BladeString::bladeCompile($blade_tmp, $blade_data);
                Log::channel('retention')->info("compiled message:");
                Log::channel('retention')->info($message);

                if (!empty($locale_imgs[$user->getLocale()])) {
                    UserPopup::create($user->getId(), view('common.popups.blank', [
                        'title' => $subject,
                        'msg' => $message,
                        'img' => $locale_imgs[$user->getLocale()],
                        'category' => $category,
                        ...$blade_data
                    ])->render(), $category, 3, now(), now()->endOfDay());
                } elseif(!empty($locale_imgs['en'])) {
                    UserPopup::create($user->getId(), view('common.popups.blank', [
                        'title' => $subject,
                        'msg' => $message,
                        'img' => $locale_imgs['en'],
                        'category' => $category,
                        ...$blade_data
                    ])->render(), $category, 3, now(), now()->endOfDay());
                } else {
                    UserPopup::create($user->getId(), view('common.popups.blank', [
                        'title' => $subject,
                        'msg' => $message,
                        'category' => $category,
                        ...$blade_data
                    ])->render(), $category, 3, now(), now()->endOfDay());
                }
                Log::channel('retention')->info("sent");
            } catch (\Exception $e) {
                Log::channel('retention')->error($e->getMessage());
            }
        }

        Localization::setAppLocale($curr_locale);
    }

    public function sendManagersTelegram(array $user_ids, array $blade_data = [])
    {
        $schedule = new MessageBlankSchedule();
        $schedule->setBlankId($this->id);
        $schedule->setIds(json_encode($user_ids));
        $schedule->setBladeData(json_encode($blade_data));
        $schedule->setTotal(count($user_ids));
        $schedule->setMethod(self::METHOD_TG_CHAT);
        $schedule->setStatus(self::STATUS_PENDING);
        if ($schedule->getTotal() === 1) $schedule->setStatus(self::STATUS_IN_PROGRESS);
        $schedule->save();
        if ($schedule->getTotal() === 1) $this->sendManagersTelegramSchedule($schedule->getId());
    }

    public static function processTelegramChatSchedule()
    {
        /** @var MessageBlankSchedule $schedule */
        $schedule = MessageBlankSchedule::where('status', self::STATUS_PENDING)
            ->where('method', self::METHOD_TG_CHAT)
            ->first();

        if ($schedule) {
            $schedule->setStatus(self::STATUS_IN_PROGRESS);
            $schedule->save();

            /** @var MessageBlank $blank */
            $blank = MessageBlank::find($schedule->getBlankId());

            if ($blank === null) {
                $schedule->setStatus(self::STATUS_ERROR);
                $schedule->setLastError("no blank found");
                $schedule->save();
                return;
            }

            $blank->sendManagersTelegramSchedule($schedule->getId());
        }
    }

    public function sendManagersTelegramSchedule(int $schedule_id)
    {
        /** @var MessageBlankSchedule $schedule */
        $schedule = MessageBlankSchedule::find($schedule_id);
        $user_ids = json_decode($schedule->getIds(), true);
        $blade_data = json_decode($schedule->getBladeData(), true);

        $t_users = TelegramClient::whereIn('m_user_id', $user_ids)->get();

        log::channel('telegram')->info('telegram crm sending messages from blank, blank#'.$this->id);

        log::channel('telegram')->info('found users:'.$t_users);
        $locale_msgs = [];;
        $locale_imgs = [];
        if (!empty($this->lang_messages)) $locale_msgs = json_decode($this->lang_messages, true);
        if (!empty($this->lang_img_urls)) $locale_imgs = json_decode($this->lang_img_urls, true);

        if (empty($this->tg_message)) {
            log::channel('telegram')->info('telegram messages is empty, blank#'.$this->id);
//            return;
        }
        $curr_locale = App::getLocale();

        /** @var TelegramClient $t_user */
        foreach ($t_users as $t_user) {

            $schedule_log = new MessageBlankLog();
            $schedule_log->setUserId($t_user->m_user_id);
            $schedule_log->setMethod(self::METHOD_TG_CHAT);
            $schedule_log->setScheduleId($schedule_id);

            try {
                Log::channel('telegram')->info($t_user->getTelegramId());
                $locale = $t_user->getLocale()??'en';
                Localization::setAppLocale($t_user->getLocale()??'en');
                Log::channel('telegram')->info("users locale ".$locale);
                Log::channel('telegram')->info("pattern message:");

                if (!empty($locale_msgs[$locale])) {
                    Log::channel('telegram')->info("got from localized");
                    $blade_tmp = $locale_msgs[$t_user->getLocale()];
                } else {
                    Log::channel('telegram')->info("got from tg_message");
                    $blade_tmp = $this->tg_message;
                }

                Log::channel('telegram')->info($blade_tmp);
                $message = BladeString::bladeCompile($blade_tmp, $blade_data);

                Log::channel('telegram')->info("compiled message:");
                Log::channel('telegram')->info($message);

                $resp = "";

                if (!empty($locale_imgs[$locale])) {
                    $resp = TelegramBot::sendChatPhoto($locale_imgs[$locale], $message, $t_user->getTelegramId(), bot_token: config('manager_bot.token'));
                } else {
                    if ($this->tg_media_type === 'image') {
                        $resp = TelegramBot::sendChatPhotoData($this->tg_image, $message, $t_user->getTelegramId(), bot_token: config('manager_bot.token'));
                    } else {
                        if (empty($message)) {
                            Log::channel('telegram')->info("pattern message is empty");
                            $schedule_log->setResponse("pattern message is empty");
                            $schedule_log->save();
                            $schedule->setErrors($schedule->getErrors() + 1);
                            continue;
                        }
                        $resp = TelegramBot::sendChatMessage($message, $t_user->getTelegramId(), bot_token: config('manager_bot.token'));
                    }
                }
                Log::channel('telegram')->info("sent");
                $schedule_log->setResponse(mb_strimwidth($resp, 0, 200, "..."));

                $schedule->setSent($schedule->getSent() + 1);
                $schedule->save();
            } catch (\Exception $e) {
                Log::channel('telegram')->error($e->getMessage());
                $schedule_log->setResponse(mb_strimwidth($e->getMessage(), 0, 200, '...'));
                $schedule->setErrors($schedule->getErrors() + 1);
                $schedule->save();
            }

            $schedule_log->save();
        }

        $schedule->setStatus(self::STATUS_DONE);
        $schedule->save();

        Localization::setAppLocale($curr_locale);
    }

    /**
     * send telegram message to all users
     * @param array $blade_data
     */
    public function sendToAllTelegram(array $blade_data = [])
    {
        $users = TelegramUser::all()->pluck('user_id')->toArray();
        $this->sendTelegram($users, $blade_data);
    }

    public function sendViber(array $user_ids, array $blade_data = [])
    {
        log::channel('viber')->info('Sending viber messages blank#'.$this->id);
        log::channel('viber')->info('to users '.var_export($user_ids, true));

        if (empty($this->viber_message)) {
            log::channel('viber')->info('viber messages is empty, blank#'.$this->id);
            return;
        }
        $curr_locale = App::getLocale();

        $v_users = ViberUser::whereIn('user_id', $user_ids)->get();
        log::channel('viber')->info('found users '.var_export($v_users, true));

        foreach ($v_users as $v_user) {
            /** @var ViberUser $v_user */
            Log::channel('viber')->info("sending to user");
            Log::channel('viber')->info($v_user->viber_id);
            $m_user = $v_user->getMUser();
            if (!$m_user) {
                Log::channel('viber')->info("no user found");
                Localization::setAppLocale('ru');
            } else {
                Localization::setAppLocale($m_user->getLocale());
            }

            $message = BladeString::bladeCompile($this->viber_message, $blade_data);

            try {
                if ($this->viber_media_type === 'image') {
                    $v_user->sendImage($this->viber_image, $message);
                } else {
                    if (empty($message)) {
                        Log::channel('viber')->info("message is empty");
                        continue;
                    }
                    $v_user->sendText($message);
                }
            } catch (\Exception $e) {
                Log::channel('viber')->error($e->getMessage());
            }
            Log::channel('viber')->info("sent");
        }

        Localization::setAppLocale($curr_locale);
    }

    public function sendToAllViber(array $blade_data = [])
    {
        $users = ViberUser::pluck('user_id')->toArray();
        $this->sendViber($users, $blade_data);
    }

    public function sendMail(array $user_ids, array $blade_data = [], bool $auto_locale = true, $mail_system = null, bool $force = false, bool $now = false)
    {
        Log::channel('mail')->info("message blank send mail #{$this->id} name {$this->name}");
        Log::channel('mail')->info('message blanks send mail to ids:'.var_export($user_ids, true));
        set_time_limit( ini_get('max_execution_time') + 60*60*4);

        $emails = [];
        $schedule_letters = json_decode($this->emails, true);

        $curr_locale = App::getLocale();

        foreach (Localization::getAvailableLocales() as $locale) {
            $emails[$locale] = MUser::whereIn('id', $user_ids)
                ->whereNotNull('email')
                ->where('email', '!=', '')
                ->where('locale', $locale)
                ->pluck('email')->toArray();
        }

        $emails['empty'] = MUser::whereIn('id', $user_ids)
            ->whereNotNull('email')
            ->where('email', '!=', '')
            ->whereNull('locale')
            ->pluck('email')->toArray();

        Log::channel('mail')->info('message blanks send mail by locals:'.var_export($emails, true));

            foreach ($emails as $locale => $addresses) {
                if (empty($addresses)) {
                    Log::channel('mail')->info("locale $locale email list is empty");
                    continue;
                }
                if ($locale === 'empty') $locale = 'en';
                Log::channel('mail')->info("blank {$this->id} {$this->name} sending locale $locale");

                if (empty($schedule_letters[$locale])) {
                    Log::channel('mail')->info("blank {$this->id} {$this->name} sending locale $locale is empty, trying en");
                    $locale = 'en';
                }

                if ($auto_locale) Localization::setAppLocale($locale);

                if (!empty($schedule_letters[$locale])) {
                    if (empty($schedule_letters[$locale]??[])) {
                        log::channel('mail')->error('mail messages is empty, blank#'.$this->id.' locale '.$locale);
                        continue;
                    }
                    $message = BladeString::bladeCompile($schedule_letters[$locale], $blade_data);
                    $subject = BladeString::bladeCompile($schedule_letters['subject_' . $locale]??'no-subject', $blade_data);

                    $message = str_replace('kraken.game.domain', MailConfig::getGameDomain(), $message);
                    $message = str_replace('krakencassino.com', MailConfig::getGameDomain(), $message);
                    $letter = new \App\Email([
                        'to' => $addresses,
                        'subject' => $subject,
                        'html' => $message,
                    ], $mail_system);

                    try {
                        $letter->send($force, $now);
                    } catch (\Exception $e) {
                        Log::channel('mail')->error($e->getMessage());
                    }

                } else {
                    log::channel('mail')->error('mail messages is empty, blank#'.$this->id.' locale '.$locale);
                }

            }

        Localization::setAppLocale($curr_locale);
    }

    public function sendToAllMail(array $blade_data = [], bool $auto_locale = true, $mail_system = null)
    {
        set_time_limit( ini_get('max_execution_time') + 60*60*4);

        MUser::whereNotNull('email')->where('email', '<>', '')->chunk(2000, function($users) use ($blade_data, $auto_locale, $mail_system) {
            $ids = $users->pluck('id')->toArray();
            Log::channel('mail')->info('sending chunk mail size '.count($ids));
            $this->sendMail($ids, $blade_data, $auto_locale, $mail_system);
        });
    }


    /**
     * @param array $blade_data
     * @param bool $auto_locale
     * @param null $mail_system
     */
    public function sendToAllUsers(array $blade_data = [], bool $auto_locale = true, $mail_system = null)
    {
        set_time_limit(3600);
        Log::channel('mail')->info(__CLASS__ . __METHOD__. ' telegram');
        $this->sendToAllTelegram($blade_data);
        Log::channel('mail')->info(__CLASS__ . __METHOD__. ' viber');
        $this->sendToAllViber($blade_data);
        Log::channel('mail')->info(__CLASS__ . __METHOD__. ' email');
        $this->sendToAllMail($blade_data, $auto_locale, $mail_system);
        Log::channel('mail')->info(__CLASS__ . __METHOD__. ' done');
    }

    public function sendAll(array $user_ids, array $blade_data = [], bool $auto_locale = true)
    {
        set_time_limit(3600);
        Log::channel('mail')->info(__CLASS__ . __METHOD__. ' telegram');
        $this->sendTelegram($user_ids, $blade_data);
        Log::channel('mail')->info(__CLASS__ . __METHOD__. ' viber');
        $this->sendViber($user_ids, $blade_data);
        Log::channel('mail')->info(__CLASS__ . __METHOD__. ' email');
        $this->sendMail($user_ids, $blade_data, $auto_locale);
        Log::channel('mail')->info(__CLASS__ . __METHOD__. ' done');
    }

    public static function testRetention($user_id)
    {
        /** @var MessageBlank $blank */
        foreach (MessageBlank::where('type', self::TYPE_RETENTION)->get() as $blank) {
            $blank->sendAll([$user_id], ['promocode' => 'testpromocode']);
        }
    }

    /**
     * convert blanks from currencies to locales
     */
    public static function convertBlanks() {
        /** @var MessageBlank $blank */
        foreach (MessageBlank::all() as $blank) {
            $mails = json_decode($blank->emails, true)??[];
            $new_mails = [];
            foreach ($mails as $mail_param => $value) {
                if (in_array($mail_param, ['RUB', 'UAH', 'INR'])) {
                    if ($mail_param === 'RUB') {
                        $new_mails['ru'] = $value;
                    }
                    if ($mail_param === 'UAH') {
                        $new_mails['ua'] = $value;
                    }
                    if ($mail_param === 'INR') {
                        $new_mails['hi'] = $value;
                    }
                }

                if (preg_match('#^subject_(.{2,4})$#', $mail_param, $match)) {
                    if (in_array($match[1], ['RUB', 'UAH', 'INR'])) {
                        if ($match[1] === 'RUB') {
                            $new_mails['subject_ru'] = $value;
                        }
                        if ($match[1] === 'UAH') {
                            $new_mails['subject_ua'] = $value;
                        }
                        if ($match[1] === 'INR') {
                            $new_mails['subject_hi'] = $value;
                        }
                    }
                }

                if (!empty($new_mails)) {
                    $blank->emails = json_encode($new_mails);
                    $blank->save();
                }
            }
        }
    }
}
