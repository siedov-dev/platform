<?php

namespace App\Listeners;

use App\CustomerIo\CioEventType;
use App\CustomerIo\CustomerIo;
use App\Events\MoneyDeposit;
use App\Helpers\Hlog;
use App\Jobs\SyncCustomerIoJob;
use App\Models\UserMoneyStat;
use App\UserWallet;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class ModeyDepositListener
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
     * @param MoneyDeposit $event
     * @return void
     */
    public function handle(MoneyDeposit $event)
    {
        try {
            if ($event->user === null || $event->amount == 0) return;

            //update money stats
//            UserMoneyStat::getStat($event->user, $event->currency, UserMoneyStat::TYPE_TOTAL, true);
            //update customer io
            SyncCustomerIoJob::dispatch($event->user->id);
            CustomerIo::getInstance()->sendEvent($event->user->id, CioEventType::Deposit, [
                'amount' => $event->amount,
                'currency' => UserWallet::currencyName($event->currency),
                ]);

        } catch (\Exception $e){
            Hlog::logException($e);
        }
    }
}
