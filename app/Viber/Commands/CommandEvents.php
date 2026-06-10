<?php


namespace App\Viber\Commands;


use App\Http\Controllers\GetBonusController;
use App\Models\Bonuses\UserBonus;
use App\Models\BonusEvents\BonusEvent;
use App\Models\Tournaments\Tournament;
use App\MUser;
use App\Viber\Models\ViberUser;
use App\Viber\ViberBot;

class CommandEvents implements CommandInterface
{
    private ViberBot $bot;

    public function __construct(ViberBot &$bot)
    {
        $this->bot = $bot;
    }

    public function process($params, ViberUser $v_user, $update)
    {
        $msg = '';

        /** @var MUser $m_user */
        $m_user = MUser::find($v_user->user_id);


        if(empty($msg)){
            $msg = 'На данный момент акции или турниры отсутствуют.';
        }

        return $v_user->sendText($msg);
    }
}
