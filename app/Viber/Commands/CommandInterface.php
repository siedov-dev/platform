<?php


namespace App\Viber\Commands;


use App\Viber\Models\ViberUser;
use App\Viber\ViberBot;

interface CommandInterface
{
    public function __construct(ViberBot &$bot);

    public function process($params, ViberUser $v_user, $update);
}
