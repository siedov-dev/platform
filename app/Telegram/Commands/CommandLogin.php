<?php


namespace App\Telegram\Commands;


use App\Models\Bonuses\UserBonus;
use App\MUser;
use App\Telegram\Models\TelegramUser;
use App\Telegram\TelegramBot;
use Telegram\Bot\Objects\Message;
use Telegram\Bot\Objects\User;

class CommandLogin implements CommandInterface
{
    private TelegramBot $bot;

    public function __construct(TelegramBot &$bot)
    {
        $this->bot = $bot;
    }

    public function process(string $params, User $user, TelegramUser $t_user, Message $message): ?Message
    {
        if ($t_user->status == TelegramUser::STATUS_CONFIRMED) {
            return $this->bot->getApi()->sendApiMessage([
                'chat_id' => $user->getId(),
                'text' => __('telegram.already_connected'),
                'reply_markup' => json_encode($t_user->getUserMenu()),
            ]);
        }

        $t_user->setReqStatus(TelegramUser::REQ_STATUS_GET_EMAIL);

        return $this->bot->getApi()->sendApiMessage([
            'chat_id' => $user->getId(),
            'text' => __('telegram.enter_email_or_phone'),
            'reply_markup' => json_encode($t_user->getUserMenu()),
        ]);
    }
}
