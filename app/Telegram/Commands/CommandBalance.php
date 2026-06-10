<?php


namespace App\Telegram\Commands;


use App\Models\Bonuses\UserBonus;
use App\MUser;
use App\Telegram\Models\TelegramUser;
use App\Telegram\TelegramBot;
use Telegram\Bot\Objects\Message;
use Telegram\Bot\Objects\User;

class CommandBalance implements CommandInterface
{
    private TelegramBot $bot;

    public function __construct(TelegramBot &$bot)
    {
        $this->bot = $bot;
    }

    public function process(string $params, User $user, TelegramUser $t_user, Message $message): ?Message
    {
        if ($t_user->status != TelegramUser::STATUS_NOT_CONFIRMED) {
            /** @var MUser $m_user */
            $m_user = MUser::find($t_user->user_id);
            if (!$m_user) {
                return $this->bot->getApi()->sendApiMessage([
                    'chat_id' => $user->getId(),
                    'text' => __('telegram.user_not_found'),
                    'reply_markup' => json_encode($t_user->getUserMenu()),
                ]);
            }

            $text = __('telegram.balance').": \n";
            $text .= number_format($m_user->getBalance(), 2, '.', '') . ' ' . $m_user->getCurrencyName() . "\n";
            return $this->bot->getApi()->sendApiMessage([
                'chat_id' => $user->getId(),
                'text' => $text,
                'reply_markup' => json_encode($t_user->getUserMenu()),
            ]);
        }

        return $this->bot->getApi()->sendApiMessage([
            'chat_id' => $user->getId(),
            'text' => __('telegram.user_not_identified'),
            'reply_markup' => json_encode($t_user->getUserMenu()),
        ]);
    }
}
