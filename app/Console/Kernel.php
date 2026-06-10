<?php

namespace App\Console;

use App\Console\Commands\RecoveryMail;
use App\Console\Commands\BonusTest;
use App\Http\Controllers\GetBonusController;
use App\MailPromotion\MailPromotionManager;
use App\Models\Bonuses\CashbackMoneyBonus;
use App\Models\GameTransaction;
use App\Models\Popups\UserPopup;
use App\Models\Slc\SlcConfig;
use App\Models\UserNotifications;
use App\Redis\DeferredModelWriter;
use App\Services\Referrals\ReferralsService;
use Carbon\Carbon;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Illuminate\Support\Facades\Log;

class Kernel extends ConsoleKernel
{
    protected $commands = [
        //
        'App\Console\Commands\TestPay',
        'App\Console\Commands\EveryMinuteTasks',
        RecoveryMail::class,
    ];
    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {

        $schedule->command('minuteTasks')->everyMinute()->runInBackground();
        $schedule->command('renewGamesCache')->everySixHours()->runInBackground();
//        $schedule->command('referrals:process')->monthly()->runInBackground();
        $schedule->command('musers:check-blocked')->everyMinute()->runInBackground();
        $schedule->command('blank:telegram-chat')->everyMinute()->runInBackground();
        $schedule->command('blank:telegram')->everyMinute()->runInBackground();
        $schedule->command('telegram:send-schedule')->everyMinute()->runInBackground();
        $schedule->command('popups:check')->everyMinute()->runInBackground();
        $schedule->command('customerio:sync')->dailyAt('02:07')->runInBackground();
        $schedule->command('users:dump-online')->everyTenMinutes()->runInBackground();
        $schedule->command('tournament:notify')->dailyAt('08:19')->runInBackground();
        $schedule->command('tournament:awards')->everyMinute()->runInBackground();

        $schedule->call(function () {
            try{
                ReferralsService::getInstance()->transferAllAccumulatedMoney();
            } catch (\Exception $e) {
                Log::error($e->getMessage());
            }
        })->monthly();

//        $schedule->command('luckypay:updatelimits')->everyFiveMinutes()->runInBackground();

//        $schedule->call(function () {
//            try{
//                DeferredModelWriter::getForModel(GameTransaction::class)->writeToDb();
//            } catch (\Exception $e) {
//                Log::error($e->getMessage());
//            }
//        })->everyFiveMinutes();

//        $schedule->call(function () {
//            try{
//                UserNotifications::clearOld();
//            } catch (\Exception $e) {
//                Log::error($e->getMessage());
//            }
//        })->everyThirtyMinutes();

        $schedule->call(function () {
            try{
                \App\Http\Controllers\PayController::processAutoPayments();
            } catch (\Exception $e) {
                Log::error($e->getMessage());
            }
        })->everyMinute();

        $schedule->call(function () {
            try{
                SlcConfig::slcJackpotAutoInc();
            } catch (\Exception $e) {
                Log::error($e->getMessage());
            }
        })->everyMinute();


//        $schedule->call(function () {
//            try {
////                DailyBonus::createDailyBonuses();
//            } catch (\Exception $e) {
//                Log::error($e->getMessage());
//            }
//        })->dailyAt('00:04');

        $schedule->call(function () {
            try {
                GetBonusController::processBirthdayBonuses();
            } catch (\Exception $e) {
                Log::error($e->getMessage());
            }
        })->dailyAt('08:45');

        $schedule->call(function () {
            try {
                UserPopup::clearOld();
            } catch (\Exception $e) {
                Log::error($e->getMessage());
            }
        })->dailyAt('11:23');

        $schedule->call(function () {
            try {
                CashbackMoneyBonus::weeklyCashBack();
            } catch (\Exception $e) {
                Log::error($e->getMessage());
            }
        })->weeklyOn(Carbon::FRIDAY, '07:11');

        $schedule->call(function () {
            try {
                GetBonusController::clearBonuses();
            } catch (\Exception $e) {
                Log::error($e->getMessage());
            }
        })->hourly();

        $schedule->call(function () {
            try {
                \App\Models\UserDailyStat::clearAll();
            } catch (\Exception $e) {
                Log::error($e->getMessage());
            }
        })->dailyAt('00:00');

        $schedule->call(function () {
            try {
                \App\Models\UserWeeklyData::clearAll();
            } catch (\Exception $e) {
                Log::error($e->getMessage());
            }
        })->weeklyOn(Carbon::MONDAY);

    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
