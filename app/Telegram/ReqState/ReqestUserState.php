<?php

namespace App\Telegram\ReqState;

use App\Telegram\Models\TelegramUser;
use App\Telegram\TelegramBot;
use Telegram\Bot\Objects\Message;
use Telegram\Bot\Objects\Update;
use Telegram\Bot\Objects\User;

abstract class ReqestUserState
{
    const REQ_STATUS_NONE = 0;
    const REQ_STATUS_GET_EMAIL = 1; //wait for users email or phone number
    const REQ_STATUS_CONFIRM_EMAIL = 2; //inline confirm provided email|phone
    const REQ_STATUS_CONFIRM_PHONE_CODE = 3; //confirm sent phone number
    const REQ_STATUS_CONFIRM_EMAIL_CODE = 4; //confirm sent email

    const BTN_GET_PROMO = 2;
    const BTN_CANCEL = 3;

    protected TelegramUser $t_user;
    protected TelegramBot $telegram;

    public function __construct(TelegramUser $t_user)
    {
        $this->t_user = $t_user;
        $this->telegram = TelegramBot::getInstance();
    }

    /**
     * return actual user menu
     * @return array|null
     */
    public abstract function getUserMenu(): ?array;

    /**
     * process users message
     * @param User $user
     * @param TelegramUser $t_user
     * @param Message $message
     * @param Update $update
     * @return bool is message processed
     */
    public abstract function processMessage(User $user, TelegramUser $t_user, Message $message, Update $update): bool;

    public final static function getState(TelegramUser $t_user): ReqestUserState
    {
        switch ($t_user->req_status) {
            case TelegramUser::REQ_STATUS_GET_EMAIL:
                return new GetEmailState($t_user);
            case TelegramUser::REQ_STATUS_CONFIRM_EMAIL_CODE:
                return new ConfirmEmailState($t_user);
            case TelegramUser::REQ_STATUS_CONFIRM_PHONE_CODE:
                return new ConfirmPhoneState($t_user);
            case TelegramUser::REQ_STATUS_CONFIG:
                return new SettingsState($t_user);
            case TelegramUser::REQ_STATUS_LANG_CHOOSE:
                return new ChooseLangState($t_user);
        }

        return new DefaultState($t_user);
    }
}
