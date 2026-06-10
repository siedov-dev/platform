<?php

namespace App\Listeners;

use App\Events\MoneyDeposit;
use App\Helpers\GoldPartnersSdk;
use App\UserWallet;
use Illuminate\Support\Facades\Log;

class SendMoneyDepositToPartners
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct() {}


    /**
     * Sends data to the partners.
     *
     * @param  MoneyDeposit  $event
     * @return void
     */
    public function handle(MoneyDeposit $event)
    {
        // Check that given data is OK.
        if ($event->user === null || empty($event->user->click_id) || $event->amount == 0) return;

        // Create message and send it to GoldPartners.
        $casinoId = intval(config('goldPartners.casinoId'));
        $secretKey = config('goldPartners.secretKey');

        $sdk = new GoldPartnersSdk($casinoId, $secretKey);
        $sdk->clickId  = $event->user->click_id;
        $sdk->amount   = $event->amount;
        $sdk->currency = UserWallet::currencyName($event->currency);
        $sdk->clientName = $event->user->login;
        $sdk->clientId = $event->user->id;
        $msg = $sdk->createMessage(GoldPartnersSdk::EVENT_DEPOSIT);
        $sdk->send($msg);

        // Log information about sent data.
        Log::info("Sent data to GoldPartners at ".time()." for click ".$event->user->click_id . " with amount = ".$event->amount.' '.$event->currency);
    }
}
