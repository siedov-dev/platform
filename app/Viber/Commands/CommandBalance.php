<?php


namespace App\Viber\Commands;


use App\Models\Bonuses\UserBonus;
use App\MUser;
use App\UserWallet;
use App\Viber\Models\ViberUser;
use App\Viber\ViberBot;

class CommandBalance implements CommandInterface
{
    private ViberBot $bot;

    public function __construct(ViberBot &$bot)
    {
        $this->bot = $bot;
    }

    public function process($params, ViberUser $v_user, $update)
    {
        if ($v_user->status != ViberUser::STATUS_NOT_CONFIRMED) {
            /** @var MUser $m_user */
            $m_user = MUser::find($v_user->user_id);
            if (!$m_user) {
                return $v_user->sendText('Пользователь не найден');
            }

            $text = "Баланс: \n";
            $text .= number_format($m_user->getBalance(), 2, '.', '') . ' ' . UserWallet::currencyName($m_user->currency) . "\n";
            return $v_user->sendText($text);
        }

        return $v_user->sendText('Пользователь не идентифицирован');
    }
}
