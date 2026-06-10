<?php


namespace App\Telegram\Commands;


use App\Telegram\Models\TelegramUser;
use App\Telegram\TelegramBot;
use Telegram\Bot\Objects\Message;
use Telegram\Bot\Objects\User;

class CommandStart implements CommandInterface
{
    private TelegramBot $bot;
    private string $message = "Добро пожаловать в официальный бот Кракен казино🐙
Если ты еще не зарегистрирован, быстрее регистрируйся и забирай свой 💸БЕЗДЕПОЗИТНЫЙ💸 бонус после привязки аккаунта к боту🤑";

    public function __construct(TelegramBot &$bot)
    {
        $this->bot = $bot;
    }

    public function process(string $params, User $user, TelegramUser $t_user, Message $message): ?Message
    {
        $t_user->setReqStatus(TelegramUser::REQ_STATUS_NONE);

        if ($t_user->status == TelegramUser::STATUS_NOT_CONFIRMED) {
            $markup = [
                'inline_keyboard' => [[
                    ['text' => __('telegram.cmd_inline_login'), 'callback_data' => '/login',],
                ]],
            ];

            $this->bot->getApi()->sendApiMessage([
                'chat_id' => $user->getId(),
                'text' => __('telegram.welcome_text'),
                'reply_markup' => json_encode($t_user->getUserMenu()),
            ]);

            return $this->bot->getApi()->sendApiMessage([
                'chat_id' => $user->getId(),
                'text' => __('telegram.welcome_text2'),
                'reply_markup' => json_encode($markup),
            ]);
        }

        return $this->bot->getApi()->sendApiMessage([
            'chat_id' => $user->getId(),
            'text' => __('telegram.welcome_text2'),
            'reply_markup' => json_encode($t_user->getUserMenu()),
        ]);
    }
}
