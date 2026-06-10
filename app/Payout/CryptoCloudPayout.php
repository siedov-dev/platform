<?php

namespace App\Payout;

use App\ApplicationForPayment;
use App\PaymentsAPI\CryptoCloud\CryptoCloud;

class CryptoCloudPayout extends Payout implements AutoPayoutSystem
{
    private CryptoCloud $api;

    public function __construct()
    {
        $this->api = new CryptoCloud();
    }

    public function checkParams(array $params): bool
    {
        return true;
    }

    public function processAutoPayment(ApplicationForPayment $applicationForPayment)
    {
        $applicationForPayment->confirmed = ApplicationForPayment::STATUS_PENDING;
        $applicationForPayment->save();
    }
}
