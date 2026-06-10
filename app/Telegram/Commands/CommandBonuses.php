<?php


namespace App\Telegram\Commands;


use App\Models\Bonuses\UserBonus;
use App\MUser;
use App\Telegram\Models\TelegramUser;
use App\Telegram\TelegramBot;
use Telegram\Bot\Objects\Message;
use Telegram\Bot\Objects\User;

class CommandBonuses implements CommandInterface
{
    private TelegramBot $bot;

    public function __construct(TelegramBot &$bot)
    {
        $this->bot = $bot;
    }

    public function process(string $params, User $user, TelegramUser $t_user, Message $message): ?Message
    {
        /** @var MUser $m_user */
        $m_user = MUser::find($t_user->user_id);

        $msg = '';
        if ($m_user) {
            $bonuses = UserBonus::where('user_id', $m_user->id)
                ->whereIn('status', [UserBonus::STATUS_NOT_ACTIVATED,
                    UserBonus::STATUS_ACTIVE,
                    UserBonus::STATUS_WAITING_FOR_PAYMENT,
                    UserBonus::STATUS_WAITING_FOR_AGREEMENT,
                ])
                ->get();
            /** @var UserBonus $bonus */
            foreach ($bonuses as $bonus) {
                $b = UserBonus::convertByType($bonus);
                $msg .= __('telegram.bonus_available').' <b>"' . $b->getName() . '"</b>' . PHP_EOL;
            }

            /** @var \App\Models\BonusAvailable[] $available_bonuses */
            $available_bonuses = \App\Models\BonusAvailable::getForUser($m_user);

            foreach ($available_bonuses as $b_av) {
                $b = $b_av->getBonus($m_user);
                $msg .= __('telegram.bonus_available').' <b>"' . $b->getName() . '"</b>' . PHP_EOL;
            }

            if (empty($msg)) {
                $msg = __('telegram.no_bonuses');
            }
        } else {
            $msg = __('telegram.user_not_connected');
        }

        return $this->bot->getApi()->sendApiMessage([
            'chat_id' => $user->getId(),
            'text' => $msg,
            'parse_mode' => 'HTML',
            'reply_markup' => json_encode($t_user->getUserMenu()),
        ]);
    }
}
