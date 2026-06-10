<?php


namespace App\Viber\Commands;

use App\MUser;
use App\Viber\Models\ViberUser;
use App\Viber\ViberBot;
use Illuminate\Support\Facades\Log;

class CommandStart implements CommandInterface
{
    private ViberBot $bot;
    private string $message = "Добро пожаловать в официальный бот Кракен казино🐙
    Если ты еще не зарегистрирован, быстрее регистрируйся и забирай свой 💸БЕЗДЕПОЗИТНЫЙ💸 бонус после привязки аккаунта к боту🤑";

    public function __construct(ViberBot &$bot)
    {
        $this->bot = $bot;
    }

    public function process($params, ViberUser $v_user, $update)
    {
        if ($v_user->status == ViberUser::STATUS_NOT_CONFIRMED) {
            $markup = [
                'inline_keyboard' => [[
                    ['text' => "\u{1F4E7} Регистрация", 'callback_data' => '/login',],
                ]],
            ];

            $v_user->sendText('Добро пожаловать в официальный бот Кракен казино🐙.');
            return $v_user->sendText('Если ты еще не зарегистрирован, быстрее регистрируйся и забирай свой 💸БЕЗДЕПОЗИТНЫЙ💸 бонус после привязки аккаунта к боту🤑 https://krakencasino.club');

        }

        return $v_user->sendText('Добро пожаловать в официальный бот Кракен казино🐙.');
    }
}
