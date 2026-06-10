<?php

namespace App\Payout;

use App\ApplicationForPayment;

class UnknownPayout extends Payout implements AutoPayoutSystem
{

    public function checkParams(array $params): bool
    {
        $this->_error = "Unknown paysystem";
        return false;
    }

    public function processAutoPayment(ApplicationForPayment $applicationForPayment)
    {
    }
}
