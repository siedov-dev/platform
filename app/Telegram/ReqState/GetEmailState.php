<?php

namespace App\Telegram\ReqState;

use App\Telegram\Models\TelegramUser;
use Illuminate\Support\Facades\Log;
use Telegram\Bot\Objects\Message;
use Telegram\Bot\Objects\Update;
use Telegram\Bot\Objects\User;

class GetEmailState extends ReqestUserState
{

    public function getUserMenu(): ?array
    {
        $markup = [
            'keyboard' => [[
                ['text' => __('telegram.cmd_share_phone'), 'request_contact' => true],
            ]],
            'resize_keyboard' => true,
            'one_time_keyboard' => false,
        ];
        return $markup;
    }

    public function processMessage(User $user, TelegramUser $t_user, Message $message, Update $update): bool
    {
        $email = null;
        $phone = null;
        $reqs = null;

        if (preg_match('/^([a-zA-Z0-9_\-.]+)@[a-zA-Z0-9\-]+\.[a-zA-Z0-9\-.]+$/', $message->getText())) {
            $email = $message->getText();
            $reqs = $email;
        }

        if (preg_match('/^(\+?)\d{10,}$/', $message->getText())) {
            $phone = $message->getText();
            $reqs = $phone;
        }

        if ($message->getContact() !== null) {
            Log::channel('telegram')->info('private user contact#' . $message->getContact()->getPhoneNumber());
            $phone = $message->getContact()->getPhoneNumber();
            $reqs = $phone;
        }

        if ($email === null && $phone === null) {
            $this->telegram->sendMessage($t_user, __('telegram.wrong_email_or_phone'));
//                    $t_user->setReqStatus(TelegramUser::REQ_STATUS_NONE);
            return true;
        }

        $markup = [
            'inline_keyboard' => [[
                ['text' => __('telegram.cmd_yes'), 'callback_data' => $reqs,],
                ['text' => __('telegram.cmd_no'), 'callback_data' => self::BTN_CANCEL,],
            ]],
        ];

        $sent_message = $this->telegram->sendMessage($t_user, __('telegram.connect_acc') . ($phone !== null ? " \u{1F4F1}:" : "\u{1F4E8}:.") . $reqs . "?", json_encode($markup));
        $t_user->setReqStatus(TelegramUser::REQ_STATUS_CONFIRM_EMAIL);
        $t_user->req_message_id = $sent_message->getMessageId();
        $t_user->save();
        return true;
    }
}
