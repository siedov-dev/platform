<?php

namespace App\Listeners;

use App\Events\MoneyPayedOut;
use App\Helpers\Hlog;
use App\Models\UserMoneyStat;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class MoneyPayoutListener
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
     * Handle the event.
     *
     * @param  MoneyPayedOut  $event
     * @return void
     */
    public function handle($event)
    {
        try {
            if ($event->user === null || $event->amount == 0) return;

            //update money stats
            UserMoneyStat::getStat($event->user, $event->currency, UserMoneyStat::TYPE_TOTAL, true);

        } catch (\Exception $e){
            Hlog::logException($e);
        }
    }
}
