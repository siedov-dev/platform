<?php

namespace App\Listeners;

use App\Events\MoneySpent;
use App\Services\NatsService\NatsUserEvents;
use App\Wallet;

class MoneySpendReferral
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
     * @param MoneySpent $event
     * @return void
     */
    public function handle(MoneySpent $event)
    {
        if ($event->amount == 0 || $event->user->ref_id === null) return;
//        /** @var UserReferral $ref */
//        $ref = UserReferral::find($event->user->ref_id);
//        $ref?->onMoneySpent($event);
        NatsUserEvents::moneySpent(
            user_id: $event->user->getId(),
            amount: $event->amount,
            currency_id: Wallet::getCurrencyCode($event->currency),
            ref_id: $event->user->ref_id,
            click_id: $event->user->click_id,
            fingerprint: $event->user->getFingerprint(),
        );
    }
}
