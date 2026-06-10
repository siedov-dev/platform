<?php

namespace App\Listeners;

use App\Events\UserFirstDayActivityEvent;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class UserFirstDayActivityListener
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
     * @param  UserFirstDayActivityEvent  $event
     * @return void
     */
    public function handle(UserFirstDayActivityEvent $event)
    {
//        SlcDailyRewardJob::dispatch($event->user->getId(), $event->prev_active);
    }
}
