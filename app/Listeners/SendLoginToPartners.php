<?php

namespace App\Listeners;

use App\Helpers\GoldPartnersSdk;
use Illuminate\Auth\Events\Login;
use Illuminate\Support\Facades\Log;

class SendLoginToPartners
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {

    }

    /**
     * Sends data to the partners.
     *
     * @param  Login  $event
     * @return void
     */
    public function handle(Login $event)
    {
        // Check that given data is OK.
        if ($event->user === null || empty($event->user->click_id)) return;

        // Create message and send it to GoldPartners.
        $casinoId = intval(config('goldPartners.casinoId'));
        $secretKey = config('goldPartners.secretKey');

        $sdk = new GoldPartnersSdk($casinoId, $secretKey);
        $sdk->clickId  = $event->user->click_id;
        $sdk->clientName = $event->user->login;
        $sdk->clientId = $event->user->id;
        $msg = $sdk->createMessage(GoldPartnersSdk::EVENT_LOGIN);
        $sdk->send($msg);

        // Log information about sent data.
        Log::info("Sent data to GoldPartners (" . GoldPartnersSdk::EVENT_LOGIN . ") at ".time()." for click ".$event->user->click_id);
    }
}
