<?php

namespace App\Telegram\Commands;

use App\Models\SiteMirror;
use App\Telegram\Models\TelegramUser;
use App\Telegram\TelegramBot;
use Telegram\Bot\Objects\Message;
use Telegram\Bot\Objects\User;

class CommandMirrors implements CommandInterface
{
    private TelegramBot $bot;

    public function __construct(TelegramBot &$bot)
    {
        $this->bot = $bot;
    }

    public function process(string $params, User $user, TelegramUser $t_user, Message $message): ?Message
    {
        $mirrors = SiteMirror::all();

        if ($mirrors->count() === 0) {
            TelegramBot::sendChatMessage(__('telegram.no_mirrors'), $t_user->getTelegramId());
            return null;
        }

        $msg = '';
        foreach ($mirrors as $mirror) $msg .= $mirror->url . PHP_EOL . PHP_EOL;
        TelegramBot::sendChatMessage($msg, $t_user->getTelegramId());
        return null;
    }
}
