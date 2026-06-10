<?php


namespace App\Telegram\Commands;


use App\Models\Bonuses\UserBonus;
use App\MUser;
use App\Telegram\Models\TelegramUser;
use App\Telegram\TelegramBot;
use Telegram\Bot\Objects\Message;
use Telegram\Bot\Objects\User;

class CommandGtktest implements CommandInterface
{
    private TelegramBot $bot;

    public function __construct(TelegramBot &$bot)
    {
        $this->bot = $bot;
    }

    public function process(string $params, User $user, TelegramUser $t_user, Message $message): ?Message
    {
        $this->bot::sendChatMessage('test chat message');
        $this->bot::sendChatPhoto('/home/dest/Downloads/visa.png', 'test <b>chat</b> photo');
        $this->bot::sendChatPhoto('/home/dest/Downloads/visa.png', 'test <b>chat</b> photo', $user->getId());

        return $this->bot->getApi()->sendApiMessage([
            'chat_id' => $user->getId(),
            'parse_mode' => 'HTML',
            'text' => $t_user->first_name . ' <b>test chat_id:</b> ' . $this->bot->chat_id,
        ]);
        //check for command
        if (preg_match('#^/([0-9a-zA-Z]+) ?(.*)$#', $message->getText(), $match)) {
            $this->bot->getApi()->sendApiMessage([
                'chat_id' => $user->getId(),
                'text' => $t_user->first_name . ' пишите пожалуйста мне в приват. (Кликни сюда @KrakenCassinoBot)',
            ]);
        }
    }
}
