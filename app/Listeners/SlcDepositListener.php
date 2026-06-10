<?php

namespace App\Listeners;

use App\Events\MoneyDeposit;
use App\Models\Slc\Achievements\SlcAchievement;
use App\Models\Slc\SlcCompletedTask;
use App\Models\Slc\SlcTask;
use App\Models\Slc\SlcTaskStatus;
use App\Models\Slc\SlcTaskType;
use App\UserWallet;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SlcDepositListener
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
     * @param  MoneyDeposit  $event
     * @return void
     */
    public function handle(MoneyDeposit $event)
    {
        SlcAchievement::updateAchievements($event->user);
    }
}
