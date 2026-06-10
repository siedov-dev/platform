<?php

namespace App\Listeners;

use App\CustomerIo\CioEventType;
use App\CustomerIo\CustomerIo;
use App\Events\EmailVerifiedEvent;
use App\Jobs\SyncCustomerIoJob;
use Illuminate\Support\Facades\Log;

class EmailVerifiedListener
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
     * @param  EmailVerifiedEvent  $event
     * @return void
     */
    public function handle(EmailVerifiedEvent $event)
    {
        Log::info('email verified');
        SyncCustomerIoJob::dispatch($event->user->id);
//        SlcCompletedTask::setTaskStatus($event->user->getId(), SlcTaskType::EmailConfirm, SlcTaskStatus::Completed);
    }
}
