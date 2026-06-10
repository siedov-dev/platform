<?php

namespace App\Telegram\ReqState;

use App\Helpers\Localization;
use App\Telegram\Commands\CommandLogin;
use App\Telegram\Commands\CommandStart;
use App\Telegram\Models\TelegramUser;
use Telegram\Bot\Objects\Message;
use Telegram\Bot\Objects\Update;
use Telegram\Bot\Objects\User;

class ChooseLangState extends ReqestUserState
{

    public function getUserMenu(): ?array
    {
        $markup = [
            'keyboard' => [
                [
                    ['text' => "\u{1F1EE}\u{1F1F3} Hindi"],
                    ['text' => "\u{1F1FA}\u{1F1F8} English"],
                ],
                [
                    ['text' => "\u{1F1F7}\u{1F1FA} Russian"],
                    ['text' => "\u{1F1FA}\u{1F1E6} Ukrainian"],
                ],
            ],
            'resize_keyboard' => true,
            'one_time_keyboard' => true,
        ];

        if (in_array($this->t_user->lang, Localization::getAvailableLocales())) {
            $markup['keyboard'][] = [
                ['text' => __('telegram.cmd_cancel')],
            ];
        }
        return $markup;
    }

    public function processMessage(User $user, TelegramUser $t_user, Message $message, Update $update): bool
    {

        if ("\u{1F1EE}\u{1F1F3} Hindi" === $message->getText()) {
            $t_user->lang = "hi";
            $t_user->setReqStatus($t_user::REQ_STATUS_NONE);
            $t_user->save();
            Localization::setAppLocale($t_user->lang??'en');

            if ($t_user->status === TelegramUser::STATUS_NOT_CONFIRMED) {
                $cmd = new CommandStart($this->telegram);
                $cmd->process('', $user, $t_user, $message);
                return true;
            }
            $this->telegram->sendMessage($t_user, "Ok");
            return true;
        }
        if ("\u{1F1FA}\u{1F1F8} English" === $message->getText()) {
            $t_user->lang = "en";
            $t_user->setReqStatus($t_user::REQ_STATUS_NONE);
            $t_user->save();
            Localization::setAppLocale($t_user->lang??'en');

            if ($t_user->status === TelegramUser::STATUS_NOT_CONFIRMED) {
                $cmd = new CommandStart($this->telegram);
                $cmd->process('', $user, $t_user, $message);
                return true;
            }
            $this->telegram->sendMessage($t_user, "Ok");
            return true;
        }
        if ("\u{1F1F7}\u{1F1FA} Russian" === $message->getText()) {
            $t_user->lang = "ru";
            $t_user->setReqStatus($t_user::REQ_STATUS_NONE);
            $t_user->save();
            Localization::setAppLocale($t_user->lang??'en');

            if ($t_user->status === TelegramUser::STATUS_NOT_CONFIRMED) {
                $cmd = new CommandStart($this->telegram);
                $cmd->process('', $user, $t_user, $message);
                return true;
            }
            $this->telegram->sendMessage($t_user, "Ok");
            return true;
        }
        if ("\u{1F1FA}\u{1F1E6} Ukrainian" === $message->getText()) {
            $t_user->lang = "ua";
            $t_user->setReqStatus($t_user::REQ_STATUS_NONE);
            $t_user->save();
            Localization::setAppLocale($t_user->lang??'en');

            if ($t_user->status === TelegramUser::STATUS_NOT_CONFIRMED) {
                $cmd = new CommandStart($this->telegram);
                $cmd->process('', $user, $t_user, $message);
                return true;
            }
            $this->telegram->sendMessage($t_user, "Ok");
            return true;
        }

        if (__('telegram.cmd_cancel') === $message->getText()) {
            $t_user->setReqStatus($t_user::REQ_STATUS_NONE);
            $this->telegram->sendMessage($t_user, "canceled");
            return true;
        }

        return false;
    }
}
