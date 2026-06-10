<?php

namespace App\Listeners;

use App\CustomerIo\CustomerIo;
use App\Events\LocaleChanged;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class LocaleChangedListener implements ShouldQueue
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
    public function handle(LocaleChanged $event)
    {
        //
        CustomerIo::changeAttribute($event->user_id, 'lang', $event->locale);
    }
}
