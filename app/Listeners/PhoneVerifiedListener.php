<?php

namespace App\Listeners;

use App\Events\PhoneVerifiedEvent;
use App\Jobs\SyncCustomerIoJob;
use App\Models\Slc\SlcCompletedTask;
use App\Models\Slc\SlcTaskStatus;
use App\Models\Slc\SlcTaskType;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class PhoneVerifiedListener
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
     * @param  PhoneVerifiedEvent  $event
     * @return void
     */
    public function handle(PhoneVerifiedEvent $event)
    {
//        SlcCompletedTask::setTaskStatus($event->user->getId(), SlcTaskType::PhoneConfirm, SlcTaskStatus::Completed);
        SyncCustomerIoJob::dispatch($event->user->id);
    }
}
