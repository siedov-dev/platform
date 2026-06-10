<?php


namespace App\Telegram\Commands;


use App\Telegram\Models\TelegramUser;
use App\Telegram\TelegramBot;
use Telegram\Bot\Objects\Message;
use Telegram\Bot\Objects\User;

interface CommandInterface
{
    public function __construct(TelegramBot &$bot);

    public function process(string $params, User $user, TelegramUser $t_user, Message $message): ?Message;
}
