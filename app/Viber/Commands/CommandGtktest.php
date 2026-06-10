<?php


namespace App\Viber\Commands;


use App\Models\Bonuses\UserBonus;
use App\MUser;
use App\Viber\Models\ViberUser;
use App\Viber\ViberBot;

class CommandGtktest implements CommandInterface
{
    private ViberBot $bot;

    public function __construct(ViberBot &$bot)
    {
        $this->bot = $bot;
    }

    public function process( $params, ViberUser $v_user, $update)
    {
        $v_user->sendText('test');
    }
}
