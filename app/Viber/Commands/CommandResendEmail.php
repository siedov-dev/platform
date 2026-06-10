<?php


namespace App\Viber\Commands;


use App\Viber\Models\ViberUser;
use App\Viber\ViberBot;

class CommandResendEmail implements CommandInterface
{
    private ViberBot $bot;

    public function __construct(ViberBot &$bot)
    {
        $this->bot = $bot;
    }

    public function process($params, ViberUser $v_user, $update)
    {
        if ($v_user->status == ViberUser::STATUS_NOT_CONFIRMED && $v_user->getReqStatus() == ViberUser::REQ_STATUS_CONFIRM_EMAIL_CODE) {
            $v_user->sendText($v_user->sendEmailConfirmCode());
        }
    }
}
