<?php

namespace App\Telegram\Commands;

use App\Telegram\Models\TelegramUser;
use App\Telegram\TelegramBot;
use Telegram\Bot\Objects\Message;
use Telegram\Bot\Objects\User;

class CommandManagerBonus implements CommandInterface
{
    private TelegramBot $bot;

    public function __construct(TelegramBot &$bot)
    {
        $this->bot = $bot;
    }

    public function process(string $params, User $user, TelegramUser $t_user, Message $message): ?Message
    {
        TelegramBot::sendChatMessage(__('telegram.link_manager_bonus'), $t_user->getTelegramId(), [
                __('telegram.cmd_personal_manager') => 'https://t.me/kraken_chatbot?start=bonus',
//                __('telegram.cmd_personal_manager') => 'https://t.me/dgtk_manager_bot?start=bonus',
            ]);

        return null;
    }
}
