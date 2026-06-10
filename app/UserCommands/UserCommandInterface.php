<?php


namespace App\UserCommands;


use App\MUser;
use App\UserWallet;

/**
 * @deprecated
 */
interface UserCommandInterface
{

    public function execute(?MUser $user = null, ?int $currency = null, $context = null);

}
