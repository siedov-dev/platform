<?php

namespace App\Payin;

use App\MUser;
use App\PayoutTransaction;

class UnknownPayin extends Payin
{
    public function __construct(MUser $user)
    {
        parent::__construct($user);
        $this->name = "Unknown";
        $this->type = 0;
    }

    public function process($amount, $method, array $data, ?PayoutTransaction $transaction, int $method_id)
    {
            $this->_error = 'Unknown paysystem';
            return false;
    }

}
