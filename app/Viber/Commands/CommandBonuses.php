<?php


namespace App\Viber\Commands;

use App\Models\Bonuses\UserBonus;
use App\MUser;
use App\Viber\Models\ViberUser;
use App\Viber\ViberBot;

class CommandBonuses implements CommandInterface
{
    private ViberBot $bot;

    public function __construct(ViberBot &$bot)
    {
        $this->bot = $bot;
    }

    public function process($params, ViberUser $v_user, $update)
    {
        /** @var MUser $m_user */
        $m_user = MUser::find($v_user->user_id);

        $msg = '';
        if($m_user) {
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
                $msg .= 'Доступен бонус "' . $b->getName() . '"' . PHP_EOL;
            }

            if (empty($msg)) {
                $msg = 'На данный момент у вас нет доступных бонусов.';
            }
        } else {
            $msg = 'Не привязан пользователь, укажите свой email на сайте, командой /login';
        }

        return $v_user->sendText($msg);
    }
}
