<?php


namespace App\Telegram;

use App\Helpers\Localization;
use App\Models\TelegramSendSchedule;
use App\MUser;
use App\Telegram\Models\TelegramPromocodes;
use App\Telegram\Models\TelegramStat;
use App\Telegram\Models\TelegramUser;
use Illuminate\Support\Facades\Log;
use Telegram\Bot\Exceptions\TelegramSDKException;
use Telegram\Bot\FileUpload\InputFile;
use Telegram\Bot\Objects\CallbackQuery;
use Telegram\Bot\Objects\ChatMember;
use \Telegram\Bot\Objects\Update;
use \Telegram\Bot\Objects\Message;
use \Telegram\Bot\Objects\User;

class TelegramBot
{

    // comands
    /*
login - привяжите игровой аккаунт
menu - меню
     */
    const BTN_GET_PROMO = 2;
    const BTN_CANCEL = 3;

    public int $chat_id;
    public string $bot_name;
    public string $bot_link;
    public string $channel_link;
    private string $token;

    private static ?TelegramBot $_instance;

    private ExtApi $telegram;
    private MessageParser $parser;

    private int $bot_id = 1221933837;

    public function __construct(?string $token = null)
    {
        $this->chat_id = config('telegram_bot.chat_id');
        $this->bot_name = config('telegram_bot.bot_name');
        $this->bot_link = config('telegram_bot.bot_link');
        $this->channel_link = config('telegram_bot.channel_link');
        $this->token = $token?? config('telegram_bot.token');

        $this->telegram = new ExtApi($this->token);
        $this->parser = new MessageParser($this);

        self::$_instance = $this;
    }

    public static function getInstance(): TelegramBot
    {
        if (self::$_instance === null) {
            self::$_instance = new TelegramBot();
        }

        return self::$_instance;
    }

    public static function sendChannelMessage(string $text, array $buttons = [])
    {
        self::sendChatMessage($text, config('telegram_bot.chat_id'), $buttons);
    }

    public static function sendChannelPhotoData(string $file_data, string $caption = "", $chat_id = null, array $buttons = [])
    {
        self::sendChatPhotoData($file_data, $caption, config('telegram_bot.chat_id'), $buttons);
    }

    public static function sendChannelPhoto(string $file_url, string $caption = "", array $buttons = [])
    {
        self::sendChatPhoto($file_url, $caption, config('telegram_bot.chat_id'), $buttons);
    }

    /**
     * @return ExtApi
     */
    public function getApi(): ExtApi
    {
        return $this->telegram;
    }

    /**
     * send message to chat/channel
     * @param string $text
     * @param null $chat_id
     * @param array $buttons
     */
    public static function sendChatMessage(string $text, $chat_id = null, array $buttons = [], ?string $bot_token = null): string
    {
        try {
            $telegram = new TelegramBot($bot_token);
            if ($chat_id === null) $chat_id = $telegram->chat_id;

            $markup = [
                'inline_keyboard' => [],
            ];

            if (!empty($buttons)) {
                foreach ($buttons as $btn_text => $url) {
                    $markup['inline_keyboard'][] = [
                        ['text' => $btn_text, 'url' => $url,]
                    ];
                }
            }

            $telegram->getApi()->sendApiMessage([
                'chat_id' => $chat_id,
                'parse_mode' => 'HTML',
                'text' => $text,
                'reply_markup' => json_encode($markup),
            ]);

            return $telegram->getApi()->getResp();
        } catch (\Exception $e) {
            Log::channel('telegram')->error($e->getMessage());
            return $e->getMessage();
        }
    }

    /**
     * send photo to channel|chat
     * @param string $file_data base64 encoded img file data
     * @param string $caption photo caption
     * @param null $chat_id chat id
     * @param array $buttons
     */
    public static function sendChatPhotoData(string $file_data, string $caption = "", $chat_id = null, array $buttons = [], ?string $bot_token = null): string
    {
        try {
            $telegram = new TelegramBot($bot_token);

            if ($chat_id === null) {
                $chat_id = $telegram->chat_id;
            }

            $markup = [
                'inline_keyboard' => [],
            ];

            if (!empty($buttons)) {
                foreach ($buttons as $btn_text => $url) {
                    $markup['inline_keyboard'][] = [
                        ['text' => $btn_text, 'url' => $url,]
                    ];
                }
            }

            $img_data = base64_decode($file_data);

            $file = tmpfile();

            fwrite($file, $img_data);

            $telegram->getApi()->sendApiPhoto([
                'chat_id' => $chat_id,
                'photo' => new InputFile($file),
                'parse_mode' => 'HTML',
                'caption' => $caption,
                'reply_markup' => json_encode($markup),
            ]);
            TelegramStat::incImages();
            return $telegram->getApi()->getResp();
        } catch (\Exception $e) {
            Log::channel('telegram')->error($e->getMessage());
            TelegramStat::incErrors();
            return $e->getMessage();
        }
    }

    /**
     * send photo to channel|chat
     * @param string $file_url
     * @param string $caption
     * @param null $chat_id
     */
    public static function sendChatPhoto(string $file_url, string $caption = "", $chat_id = null, array $buttons = [], ?string $bot_token = null): string
    {
        try {
            $telegram = new TelegramBot($bot_token);

            if ($chat_id === null) {
                $chat_id = $telegram->chat_id;
            }

            $markup = [
                'inline_keyboard' => [],
            ];

            if (!empty($buttons)) {
                foreach ($buttons as $btn_text => $url) {
                    $markup['inline_keyboard'][] = [
                        ['text' => $btn_text, 'url' => $url,]
                    ];
                }
            }

            $telegram->getApi()->sendApiPhoto([
                'chat_id' => $chat_id,
                'photo' => new InputFile($file_url),
                'parse_mode' => 'HTML',
                'caption' => $caption,
                'reply_markup' => json_encode($markup),
            ]);
            TelegramStat::incImages();
            return $telegram->getApi()->getResp();

        } catch (\Exception $e) {
            Log::channel('telegram')->error($e->getMessage());
            TelegramStat::incErrors();
            return $e->getMessage();
        }
    }

    /**
     * send message to users in telegram
     * @param TelegramUser $t_user
     * @param $message
     * @param string|null $markup json markup
     * @return Message|null
     */
    public function sendMessage(TelegramUser $t_user, $message, ?string $markup = null): ?Message
    {
        try {
            if ($markup === null) $markup = json_encode($this->userMenu($t_user));
            $message = $this->telegram->sendApiMessage([
                'chat_id' => $t_user->getTelegramId(),
                'text' => $message,
                'parse_mode' => 'HTML',
                'reply_markup' => $markup,
            ]);
            return $message;
        } catch (\Exception $e) {
            Log::channel('telegram')->error($e->getMessage());
        }
        return null;
    }

    /**
     * send message to users telegram
     * @param User $user
     * @param TelegramUser $t_user
     * @param string $message
     * @param null $markup json markup
     * @return Message|null
     */
    protected function sendMsg(User $user, TelegramUser $t_user, string $message, $markup = null): ?Message
    {
        try {
            if ($markup === null) $markup = json_encode($this->userMenu($t_user));
            $message = $this->telegram->sendApiMessage([
                'chat_id' => $user->getId(),
                'text' => $message,
                'parse_mode' => 'HTML',
                'reply_markup' => $markup,
            ]);
            return $message;
        } catch (\Exception $e) {
            Log::channel('telegram')->error($e->getMessage());
        }

        return null;
    }

    /**
     * process inline buttons callbacks
     * @param User $user
     * @param TelegramUser $t_user
     * @param CallbackQuery $callback
     */
    public function onInlineCallback(User $user, TelegramUser $t_user, CallbackQuery $callback, Update $update)
    {
        Log::channel('telegram')->info('callback user#' . $t_user->telegram_id . ' ' . $user->getFirstName() . ': ');
        try {

            if ($this->parser->parseMessage($user, $t_user, new Message(['text' => $callback->getData()])))
            {
                TelegramStat::incCommands();
                return;
            }

            //confirm email or phone number
            if ($t_user->status == TelegramUser::STATUS_NOT_CONFIRMED && $t_user->getReqStatus() == TelegramUser::REQ_STATUS_CONFIRM_EMAIL) {

                if ($callback->getMessage()->getMessageId() == $t_user->req_message_id) {

                    if ($callback->getData() == self::BTN_CANCEL) {
                        $t_user->setReqStatus(TelegramUser::REQ_STATUS_GET_EMAIL);

                        $markup = [
                            'inline_keyboard' => [[
                            ]],
                        ];
//
                        $this->telegram->editMessageText([
                            'chat_id' => $callback->getMessage()->getChat()->getId(),
                            'message_id' => $callback->getMessage()->getMessageId(),
                            'text' => 'Отменено',
                            'reply_markup' => json_encode($markup),
                        ]);
                        return;
                    }

                    if (preg_match('/^([a-zA-Z0-9_\-.]+)@[a-zA-Z0-9\-]+\.[a-zA-Z0-9\-.]+$/', $callback->getData())) {

                        /** @var MUser $m_user */
                        $m_user = MUser::where('email', $callback->getData())->first();
                        if (!$m_user) {

                            $t_user->email = $callback->getData();
                            $t_user->save();

//                            $this->sendMsg($user, $t_user, 'Введенный email не найден');
                            $this->sendMsg($user, $t_user, __('telegram.email_not_found'));
                            $markup['inline_keyboard'] = [[]];
                            $this->telegram->editMessageText([
                                'chat_id' => $callback->getMessage()->getChat()->getId(),
                                'message_id' => $callback->getMessage()->getMessageId(),
                                'text' => __('telegram.email_not_found2'),
                                'reply_markup' => json_encode($markup),
                            ]);
                            return;
                        }

                        $t_user_ex = TelegramUser::where('user_id', $m_user->id)->where('telegram_id', '!=', $t_user->getTelegramId())->first();
                        if ($t_user_ex !== null) {
                            $this->sendMsg($user, $t_user, __('telegram.email_already_used'));
                            return;
                        }

//                        if (!$m_user->isEmailVerified()) {
//                            $this->sendMsg($user, $t_user, 'email не верифицирован. Пройдите верификацию перед подключением пользователя.');
//                            return;
//                        }

                        $t_user->user_id = $m_user->id;
                        $t_user->setReqStatus(TelegramUser::REQ_STATUS_CONFIRM_EMAIL_CODE);
                        $t_user->email = $callback->getData();
                        $t_user->save();

                        $t_user->sendEmailConfirmCode();

                        $markup['inline_keyboard'] = [[]];

                        $this->telegram->editMessageText([
                            'chat_id' => $callback->getMessage()->getChat()->getId(),
                            'message_id' => $callback->getMessage()->getMessageId(),
                            'text' => __('telegram.waiting_verification'),
                            'reply_markup' => json_encode($markup),
                        ]);

                        $this->sendMsg($user, $t_user, __('telegram.enter_email_code'));
                        return;
                    }

                    if (preg_match('/^(\+?)(\d{10,})$/', $callback->getData(), $match)) {
                        $phone = $match[2];

                        /** @var MUser $m_user */
                        $m_user = MUser::where('phone', $phone)->first();
                        if (!$m_user) $m_user = MUser::where('phone', '+' . $phone)->first();

                        if (!$m_user) {
                            $t_user->phone = $phone;
                            $t_user->save();

                            $markup['inline_keyboard'] = [[]];
                            $this->telegram->editMessageText([
                                'chat_id' => $callback->getMessage()->getChat()->getId(),
                                'message_id' => $callback->getMessage()->getMessageId(),
                                'text' => __('telegram.phone_not_found'),
                                'reply_markup' => json_encode($markup),
                            ]);

                            $this->sendMsg($user, $t_user, __('telegram.phone_not_found'));
                            return;
                        }

                        $t_user_ex = TelegramUser::where('user_id', $m_user->id)->where('telegram_id', '!=', $t_user->getTelegramId())->first();
                        if ($t_user_ex !== null) {
                            $this->sendMsg($user, $t_user, __('telegram.phone_already_used'));
                            return;
                        }

//                        if (!$m_user->isPhoneVerified()) {
//                            $this->sendMsg($user, $t_user, 'Номер телефона  не верифицирован. Пройдите верификацию перед подключением пользователя.');
//                            return;
//                        }

                        $t_user->user_id = $m_user->id;
                        $t_user->setReqStatus(TelegramUser::REQ_STATUS_CONFIRM_PHONE_CODE);
                        $t_user->phone = $phone;
                        $t_user->save();

                        $t_user->sendPhoneConfirmCode();

                        $markup['inline_keyboard'] = [[]];

                        $this->telegram->editMessageText([
                            'chat_id' => $callback->getMessage()->getChat()->getId(),
                            'message_id' => $callback->getMessage()->getMessageId(),
                            'text' => __('telegram.waiting_verification'),
                            'reply_markup' => json_encode($markup),
                        ]);

                        $this->sendMsg($user, $t_user, __('telegram.enter_phone_code'));
                        return;

                    }

                }

            }

            if ($callback->getData() == self::BTN_GET_PROMO) {
                $this->parser->commandPromocode('', $user, $t_user, $callback->getMessage());
                return;
            }

        } catch (\Exception $e) {
            Log::channel('telegram')->error($e->getMessage());
        }

    }

    public function onPrivateMessage(User $user, TelegramUser $t_user, Message $message, Update $update)
    {
        Log::channel('telegram')->info('private user#' . $t_user->telegram_id . ' ' . $user->getFirstName() . ': ' . $message->getText());
        try {

            if (empty($t_user->lang) && $t_user->getReqStatus() !== TelegramUser::REQ_STATUS_LANG_CHOOSE) {
                $t_user->setReqStatus(TelegramUser::REQ_STATUS_LANG_CHOOSE);
                $this->sendMessage($t_user, "Please select your language");
                return true;
            }

            //check for commands
            if (!empty($t_user->lang) && $this->parser->parseMessage($user, $t_user, $message)) {
                TelegramStat::incCommands();
                return true;
            }

            //process state requests
            if ($t_user->getState()->processMessage($user, $t_user, $message, $update)) {
                TelegramStat::incCommands();
                return true;
            }

            if (empty($t_user->lang)) {
                $t_user->setReqStatus(TelegramUser::REQ_STATUS_LANG_CHOOSE);
                $this->sendMessage($t_user, "Please select your language");
                return true;
            }

            $this->sendMessage($t_user, __('telegram.use_context_menu'));

        } catch (\Exception $e) {
            Log::channel('telegram')->error($e->getMessage());
            Log::channel('telegram')->error($e->getFile());
            Log::channel('telegram')->error($e->getLine());
            Log::channel('telegram')->error($e->getTraceAsString());
        }
    }

    public function onJoinChat(User $user, TelegramUser $t_user)
    {
        Log::channel('telegram')->info('join user#' . $t_user->telegram_id . ' ' . $user->getFirstName());

//        if($t_user->status = TelegramUser::STATUS_NOT_CONFIRMED) {
//            $this->telegram->sendMessage([
//                'chat_id' => $user->getId(),
//                'text' => 'Укажите свой логин на сайте, командой /login (пользователь) в приват чате бота @KrakenCassinoBot, и получите бонус!',
//            ]);
//        }
    }

    public function onReplyMessage(User $user, TelegramUser $t_user, Message $reply_message, Message $message)
    {
        Log::channel('telegram')->info('reply user#' . $t_user->telegram_id . ' ' . $user->getFirstName() . ': ' . $message->getText());
        $this->parser->parseMessage($user, $t_user, $message);
    }

    public function onChatMessage(User $user, TelegramUser $t_user, Message $message, Update $update)
    {
        Log::channel('telegram')->info('chat user #' . $t_user->telegram_id . ' :' . $message->getText());
    }

    /**
     * get or create telegram user
     * @param User $user
     * @return TelegramUser
     */
    private function getTelegramUser(User $user): TelegramUser
    {
        $t_user = TelegramUser::where('telegram_id', $user->getId())->first();
        if (!$t_user) {
            $t_user = new TelegramUser();
            $t_user->telegram_id = $user->getId();
            $t_user->username = $user->getUsername();
            $t_user->first_name = $user->getFirstName();
            $t_user->last_name = $user->getLastName();
            $t_user->save();
            TelegramStat::incJoin();

            //        TelegramBot::sendChatPhoto('public/img/tg/subs1.jpg', __('telegram.subs_1'), $t_user->getTelegramId());
            $schedule = new TelegramSendSchedule();
            $schedule->file_data = 'public/img/tg/subs1.jpg';
            $schedule->media_type = "image_url";
            $schedule->send_at = now()->addMinute()->format('Y-m-d H:i:s');
            $schedule->text = __('telegram.subs_1');
            $schedule->to_users = json_encode([$t_user->getTelegramId()]);
            $schedule->to_channels = '';
            $schedule->save();

            $schedule = new TelegramSendSchedule();
            $schedule->file_data = 'public/img/tg/subs2.jpg';
            $schedule->media_type = "image_url";
            $schedule->send_at = now()->addMinutes(2)->format('Y-m-d H:i:s');
            $schedule->text = __('telegram.subs_2');
            $schedule->to_users = json_encode([$t_user->getTelegramId()]);
            $schedule->to_channels = '';
            $schedule->save();
        }

        return $t_user;
    }

    /**
     * returns if user is in channel(subscribed)/chat
     * @param int|string $user_id user id or @user_name
     * @param int $channel_id
     * @return bool
     */
    public function isUserSubscribed($user_id, int $channel_id): bool
    {
        try {
            /** @var ChatMember $member */
            $member = $this->telegram->getChatMember([
                'chat_id' => $channel_id,
                'user_id' => $user_id,
            ]);
            if (!empty($member) && $member->status !== "left") return true;
        } catch (TelegramSDKException $e) {
            //user not found
            Log::channel('telegram')->error($e->getMessage());
        }
        return false;
    }


    protected function userMenu(TelegramUser $user): array
    {
        return $user->getUserMenu();
    }

    public static function update()
    {
        $telegram = new TelegramBot();
        $lat_update_id = 0;

        for($f = 0; $f < 60; $f++) {
//        while (date('s') < 59) {

//            if(date('s') >= 59) break;
            try {
                /** @var Update[] $updates */
                $updates = $telegram->getApi()->getUpdates(['offset' => $lat_update_id, 'limit' => 5, 'timeout' => 10]);

                /** @var Update $update */
                foreach ($updates as $update) {
                    $lat_update_id = $update->getUpdateId();
                    Log::channel('telegram')->info(var_export($update, true));

                    /** @var Message $message */
                    $message = $update->getMessage();

                    $callback = $update->getCallbackQuery();

                    Log::channel('telegram')->info('channel post:'.var_export($update['channel_post']??[], true));

                    if ($callback) {
                        Log::channel('telegram')->info(var_export($callback, true));
                        $user = $callback->getFrom();
                        $t_user = $telegram->getTelegramUser($user);
                        Localization::setAppLocale($t_user->lang??'en');

                        $telegram->onInlineCallback($user, $t_user, $callback, $update);
                    }

                    if ($message) {
                        Log::channel('telegram')->info(var_export($message, true));
//                        Log::channel('telegram')->info(var_export($message->getContact(), true));

                        /** @var User $user */
                        $user = $message->getFrom();
                        if ($user && !$user['is_bot'] &&
                            !in_array($user->getId(), [$telegram->bot_id])) {

                            $t_user = $telegram->getTelegramUser($user);
                            Localization::setAppLocale($t_user->lang??'en');

                            if ($message->getNewChatParticipant()) {
                                $telegram->onJoinChat($user, $t_user);
                            }

//                            if ($message->getReplyToMessage() && $message->getReplyToMessage()->getFrom()->getId() == $telegram->bot_id) {
//                                $telegram->onReplyMessage($user, $t_user, $message->getReplyToMessage(), $message);
//                            }
//
//                            if (!$message->getReplyToMessage() && $message->getText()) {
//                                if ($message->getChat()->getType() == 'private') {
//                                    $telegram->onPrivateMessage($user, $t_user, $message, $update);
//                                } else {
//                                    $telegram->onChatMessage($user, $t_user, $message, $update);
//                                }
//                            }

                            $telegram->onPrivateMessage($user, $t_user, $message, $update);
                        }
                    }
                }

            } catch (\Exception $exception) {
                Log::channel('telegram')->error($exception->getMessage());
            }

            //mark messages as processed
            $telegram->getApi()->getUpdates(['offset' => $lat_update_id + 1, 'limit' => 1]);
//            usleep(400000);
        }

    }

    public static function process_timer()
    {
        try {

            /** @var TelegramPromocodes $promo */
            $promo = TelegramPromocodes::where('status', TelegramPromocodes::STATUS_PENDING)
                ->where('send_at', '<', now())
                ->first();
            $bot = new TelegramBot();

            if ($promo !== null) {
                Log::channel('telegram')->info("sending promocode $promo->id message to users");
                $promo->status = TelegramPromocodes::STATUS_PROCESSING;
                $promo->save();

                $markup = [
                    'inline_keyboard' => [[
                        ['text' => __('telegram.cmd_get_promo'), 'callback_data' => self::BTN_GET_PROMO,],
                    ]],
                ];


                if ($promo->user_list == null) {

                    $users = TelegramUser::all();
                    foreach ($users as $t_user) {
                        try {
                            Log::channel('telegram')->info('sending message to user id#' . $t_user->user_id . ' t_id#' . $t_user->getTelegramId());
                            $bot->sendMessage($t_user, $promo->message, json_encode($markup));
                        } catch (\Exception $e) {
                            Log::channel('telegram')->error($e->getMessage());
                        }
                    }

                    $promo->status = TelegramPromocodes::STATUS_SENT;
                    $promo->save();
                }
            }

        } catch (\Exception $e) {
            Log::channel('telegram')->error($e->getMessage());
        }

    }

}
