<?php

namespace App\Listeners;

use App\Events\MoneySpent;
use App\Helpers\GoldPartnersSdk;
use App\MUser;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SendRegistrationToPartners
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Sends data to the partners.
     *
     * @param  MoneySpent  $event
     * @return void
     */
    public function handle(MoneySpent $event)
    {

    }
}
