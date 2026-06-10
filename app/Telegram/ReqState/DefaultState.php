<?php

namespace App\Telegram\ReqState;

use App\Helpers\Measure;
use App\Telegram\Models\TelegramUser;
use Telegram\Bot\Objects\Message;
use Telegram\Bot\Objects\Update;
use Telegram\Bot\Objects\User;

class DefaultState extends ReqestUserState
{

    public function getUserMenu(): ?array
    {
        if ($this->t_user->status == TelegramUser::STATUS_NOT_CONFIRMED) {

            $markup = [
                'keyboard' => [[
                    ['text' => __('telegram.cmd_connect_acc')],
                ]],
                'resize_keyboard' => true,
                'one_time_keyboard' => true,
            ];
            return $markup;
        }

        $markup = [
            'keyboard' => [
                [
                    ['text' => __('telegram.cmd_balance')],
                    ['text' => __('telegram.cmd_bonuses')],
                ],
                [
                    ['text' => __('telegram.cmd_activities')],
                    ['text' => __('telegram.cmd_bezdep')],
                ],
                [
                    ['text' => __('telegram.cmd_personal_manager')],
                    ['text' => __('telegram.cmd_manager_bonus')],
                ],
                [
                    ['text' => __('telegram.cmd_mirrors')],
                    ['text' => __('telegram.cmd_settings')],
                ],
            ],
            'resize_keyboard' => true,
            'one_time_keyboard' => true,
        ];

        return $markup;
    }

    public function processMessage(User $user, TelegramUser $t_user, Message $message, Update $update): bool
    {
        if (__('telegram.cmd_settings') === $message->getText()) {
            $t_user->setReqStatus($t_user::REQ_STATUS_CONFIG);
            $this->telegram->sendMessage($t_user, "Settings");
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
