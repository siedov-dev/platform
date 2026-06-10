<?php

namespace App\Telegram\ReqState;

use App\Telegram\Models\TelegramStat;
use App\Telegram\Models\TelegramUser;
use Telegram\Bot\Objects\Message;
use Telegram\Bot\Objects\Update;
use Telegram\Bot\Objects\User;

class ConfirmPhoneState extends ReqestUserState
{

    public function getUserMenu(): ?array
    {
        $markup = [
            'keyboard' => [[
                ['text' => __('telegram.cmd_resend_sms')],
            ]],
            'resize_keyboard' => true,
            'one_time_keyboard' => false,
        ];
        return $markup;
    }

    public function processMessage(User $user, TelegramUser $t_user, Message $message, Update $update): bool
    {
        if (preg_match(__('telegram.ptn_resend_sms'), $message->getText(), $match)) {
            $this->telegram->sendMessage($t_user, $t_user->sendPhoneConfirmCode());
            return true;
        }

        if (preg_match('#^\d{4}$#', $message->getText())) {
            if ($t_user->phone_confirm_code === $message->getText()) {
                $t_user->status = TelegramUser::STATUS_CONFIRMED;
                $t_user->setReqStatus(TelegramUser::REQ_STATUS_NONE);
                $t_user->save();
                $m_user = $t_user->getMUser();
                $m_user->once_pass = null;
                if (empty($m_user->email) && !empty($t_user->email)) {
                    $m_user->email = $t_user->email;
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
