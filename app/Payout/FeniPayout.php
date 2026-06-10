<?php


namespace App\Payout;


use App\ApplicationForPayment;
use App\Models\PaymentSystemGroup;
use App\Models\PaymentSystems;
use App\Models\UserNotifications;
use App\MUser;
use App\Payin\PayCraftPayin;
use App\PaymentsAPI\PayOnHost;
use App\PayoutTransaction;
use App\Services\KPay\KPay;
use App\UserWallet;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class FeniPayout extends Payout implements AutoPayoutSystem
{
    public function processAutoPayment(ApplicationForPayment $applicationForPayment)
    {
        KPay::getInstance()->payoutProcessApplication($applicationForPayment->id);
    }

    public function getFormRequisites(string $method, ?PaymentSystems $pay_method): string
    {
        return view('pay.requisites.payout.card-holder', [
            'method' => $method,
        ]);
    }

}
