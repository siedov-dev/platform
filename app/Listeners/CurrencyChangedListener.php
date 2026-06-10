<?php

namespace App\Listeners;

use App\CustomerIo\CustomerIo;
use App\Events\CurrencyChangedEvent;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class CurrencyChangedListener implements ShouldQueue
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
     * @param  object  $event
     * @return void
     */
    public function handle(CurrencyChangedEvent $event)
    {
        CustomerIo::changeAttribute($event->user_id, 'currency', $event->currency);
    }
}
