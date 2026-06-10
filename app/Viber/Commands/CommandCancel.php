<?php


namespace App\Viber\Commands;


use App\MUser;
use App\Viber\Models\ViberUser;
use App\Viber\ViberBot;

class CommandCancel implements CommandInterface
{
    private ViberBot $bot;

    public function __construct(ViberBot &$bot)
    {
        $this->bot = $bot;
    }

    public function process($params, ViberUser $v_user, $update)
    {
        $v_user->setReqStatus(ViberUser::REQ_STATUS_NONE);

        return $v_user->sendText('Отменено');
    }
}
