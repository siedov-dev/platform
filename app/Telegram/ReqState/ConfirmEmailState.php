<?php

namespace App\Telegram\ReqState;

use App\Telegram\Models\TelegramStat;
use App\Telegram\Models\TelegramUser;
use Telegram\Bot\Objects\Message;
use Telegram\Bot\Objects\Update;
use Telegram\Bot\Objects\User;

class ConfirmEmailState extends ReqestUserState
{

    public function getUserMenu(): ?array
    {
        $markup = [
            'keyboard' => [[
                ['text' => __('telegram.resend_email')],
            ]],
            'resize_keyboard' => true,
            'one_time_keyboard' => false,
        ];
        return $markup;
    }

    public function processMessage(User $user, TelegramUser $t_user, Message $message, Update $update): bool
    {
        if (__('telegram.resend_email') === $message->getText()) {
            $this->telegram->sendMessage($t_user, $t_user->sendEmailConfirmCode());
            return true;
        }

        if (preg_match('#^\d{8}$#', $message->getText())) {
            if ($t_user->email_confirm_code === $message->getText()) {
                $t_user->status = TelegramUser::STATUS_CONFIRMED;
                $t_user->setReqStatus(TelegramUser::REQ_STATUS_NONE);
                $t_user->save();
                $m_user = $t_user->getMUser();
                $m_user->email_verification_token = null;
                if (empty($m_user->phone) && !empty($t_user->phone)) {
                    $m_user->phone = $t_user->phone;
                }
                $m_user->save();
                $this->telegram->sendMessage($t_user, __('telegram.account_connected'));
                TelegramStat::incConnected();
                return true;
            }
        }

        $this->telegram->sendMessage($t_user, __('telegram.wrong_confirm_code'));
        return true;
    }
}
