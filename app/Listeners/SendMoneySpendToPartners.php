<?php

namespace App\Listeners;

use App\Events\MoneySpent;
use App\Helpers\GoldPartnersSdk;
use Illuminate\Support\Facades\Log;

class SendMoneySpendToPartners
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
     * @param  MoneySpent  $event
     * @return void
     */
    public function handle(MoneySpent $event)
    {
        // Check that given data is OK.
        if ($event->user === null || empty($event->user->click_id) || $event->amount == 0) return;

        // Create message and send it to GoldPartners.
        $casinoId = intval(config('goldPartners.casinoId'));
        $secretKey = config('goldPartners.secretKey');

        $sdk = new GoldPartnersSdk($casinoId, $secretKey);
        $sdk->clickId  = $event->user->click_id;
        $sdk->amount   = $event->amount;
        $sdk->currency = $event->currency;
        $sdk->clientName = $event->user->login;
        $sdk->clientId = $event->user->id;
        $sdk->source = $event->source;
        $sdk->gameName = $event->game_name;
        $sdk->gameCid = $event->game_cid;
        $sdk->total_bet = $event->total_bet;
        $sdk->total_win = $event->total_win;
        $msg = $sdk->createMessage(GoldPartnersSdk::EVENT_MONEY_SPENT);
        $sdk->send($msg);

        // Log information about sent data.
        Log::info("Sent data to GoldPartners at ".time()." for click ".$event->user->click_id . " with amount = ".$event->amount.' '.$event->currency);
    }
}
