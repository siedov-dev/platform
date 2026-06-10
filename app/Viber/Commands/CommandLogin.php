<?php


namespace App\Viber\Commands;


use App\Viber\Models\ViberUser;
use App\Viber\ViberBot;

class CommandLogin implements CommandInterface
{
    private ViberBot $bot;

    public function __construct(ViberBot &$bot)
    {
        $this->bot = $bot;
    }

    public function process($params, ViberUser $v_user, $update)
    {
        if($v_user->status == ViberUser::STATUS_CONFIRMED){
            return $v_user->sendText('Ваш телеграм уже привязан к аккауну Krakencassino.');
        }

        $v_user->setReqStatus(ViberUser::REQ_STATUS_GET_EMAIL);

        return $v_user->sendText('Введите email или номер телефона аккаунта Krakencassino');
    }
}
