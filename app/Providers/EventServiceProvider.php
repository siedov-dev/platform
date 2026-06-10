<?php

namespace App\Providers;

use App\Events\CurrencyChangedEvent;
use App\Events\EmailVerifiedEvent;
use App\Events\LocaleChanged;
use App\Events\MoneyDeposit;
use App\Events\MoneyPayedOut;
use App\Events\MoneySpent;
use App\Events\PhoneVerifiedEvent;
use App\Events\UserFirstDayActivityEvent;
use App\Listeners\CurrencyChangedListener;
use App\Listeners\EmailVerifiedListener;
use App\Listeners\LocaleChangedListener;
use App\Listeners\LoginListener;
use App\Listeners\ModeyDepositListener;
use App\Listeners\MoneyDepositRankEvents;
use App\Listeners\MoneyPayoutListener;
use App\Listeners\MoneySpendReferral;
use App\Listeners\PhoneVerifiedListener;
use App\Listeners\SendLoginToPartners;
use App\Listeners\SendMoneyDepositToPartners;
use App\Listeners\SendMoneyPayoutToPartners;
use App\Listeners\SendMoneySpendToPartners;
use App\Listeners\SlcDepositListener;
use App\Listeners\UserFirstDayActivityListener;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Event;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event to listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],

        MoneySpent::class => [
            SendMoneySpendToPartners::class,
            MoneySpendReferral::class,
        ],

        MoneyDeposit::class => [
            SendMoneyDepositToPartners::class,
//            MoneyDepositRankEvents::class,
            SlcDepositListener::class,
            ModeyDepositListener::class,
        ],

        MoneyPayedOut::class => [
            SendMoneyPayoutToPartners::class,
            MoneyPayoutListener::class,
        ],

        Login::class => [
            LoginListener::class,
            SendLoginToPartners::class,
        ],

        EmailVerifiedEvent::class => [
            EmailVerifiedListener::class,
        ],

        PhoneVerifiedEvent::class => [
            PhoneVerifiedListener::class,
        ],

        UserFirstDayActivityEvent::class => [
            UserFirstDayActivityListener::class,
        ],

        LocaleChanged::class => [
            LocaleChangedListener::class,
        ],

        CurrencyChangedEvent::class => [
            CurrencyChangedListener::class,
        ],
    ];

    /**
     * The subscriber classes to register.
     *
     * @var array
     */
    protected $subscribe = [
//        \App\Models\Bonuses\WeeklyCashBackBonus::class,
    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        //
    }

    /**
     * Determine if events and listeners should be automatically discovered.
     *
     * @return bool
     */
    public function shouldDiscoverEvents()
    {
        return false;
    }
}
