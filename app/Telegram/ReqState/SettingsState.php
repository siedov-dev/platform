<?php

namespace App\Telegram\ReqState;

use App\Helpers\Measure;
use App\Telegram\Models\TelegramUser;
use Telegram\Bot\Objects\Message;
use Telegram\Bot\Objects\Update;
use Telegram\Bot\Objects\User;

class SettingsState extends ReqestUserState
{

    public function getUserMenu(): ?array
    {
        $markup = [
            'keyboard' => [
                [
                    ['text' => __('telegram.cmd_language')],
                ],
                [
                    ['text' => __('telegram.cmd_cancel')],
                ]
            ],
            'resize_keyboard' => true,
            'one_time_keyboard' => true,
        ];
        return $markup;
    }

    public function processMessage(User $user, TelegramUser $t_user, Message $message, Update $update): bool
    {
        if (__('telegram.cmd_cancel') === $message->getText()) {
            $t_user->setReqStatus($t_user::REQ_STATUS_NONE);
            $this->telegram->sendMessage($t_user, "canceled");
            return true;
        }

        if (__('telegram.cmd_language') === $message->getText()) {
            $t_user->setReqStatus($t_user::REQ_STATUS_LANG_CHOOSE);
            $this->telegram->sendMessage($t_user, "Select your language");
            return true;
        }

        return false;
    }
}
