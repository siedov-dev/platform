<?php

namespace App\Listeners;

use App\CustomerIo\CioEventType;
use App\CustomerIo\CustomerIo;
use App\Jobs\BindManagerJob;
use App\Jobs\LogLoginIpInfoJob;
use App\Jobs\SyncCustomerIoJob;
use App\Models\Bonuses\KUserBonus;
use App\Models\Popups\UserPopup;
use App\Models\UserLoginLog;
use App\Services\Users\UserManager;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class LoginListener
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
    public function handle($event)
    {
        try {
            Log::info('login '.Auth::id());

            if (session('tg_client_hash') !== null) {
                BindManagerJob::dispatch(Auth::id(), session('tg_client_hash'));
                session()->remove('tg_client_hash');
            }

            $ip = request()->ip();
            Log::channel('login')->info('login ip:' . $ip);
            Log::channel('login')->info('login ips:' . var_export(request()->getClientIps(), true));
            Log::channel('login')->info('headers:' . var_export(request()->headers->all(), true));
            Log::channel('login')->info('cookies:' . var_export(request()->cookies->all(), true));
            Log::channel('login')->info('agent:' . var_export($_SERVER['HTTP_USER_AGENT'], true));

            try {
                // need to set browsercap.ini in php.ini to use
                $browser = get_browser(null, true);
                Log::channel('login')->info('browser:' . var_export($browser, true));
                $log = new UserLoginLog();
                $log->domain = request()->getUri();
                $log->user_id = $event->user->id;
                $log->ip = $ip;
                $log->browser = $browser['parent']??'';
                $log->browser_version = $browser['version']??'';
                $log->platform = $browser['platform']??'';
                $log->device_name = $browser['device_name']??'';
                $log->device_type = $browser['device_type']??'';

                $log->cookie_uid = request()->cookie('user_id');
                $log->storage_uid = request()->get('lsUserId');
                $log->setFingerprint($event->user?->getFingerprint());
                $log->save();

                LogLoginIpInfoJob::dispatch($log->id, $ip);
                SyncCustomerIoJob::dispatch($event->user->id);
                CustomerIo::getInstance()->sendEvent($event->user->id, CioEventType::Login);
            } catch (\Throwable $e) {
                Log::channel('login')->error($e->getMessage());
            }

            UserManager::addEvent($event->user->id, 'wallet:update_balances', []);

//            UserPopup::setTimeout(Auth::id(), 30);
            if (!UserPopup::hasUnreadCategory(Auth::id(), 'telegram')) {
                UserPopup::showTelegramPopup(Auth::id());
            }

            $bonus = KUserBonus::firstUnactivatedBonus(Auth::id());
            if ($bonus !== null) UserPopup::create(Auth::id(), view('common.popups.has-bonus', [
                'title' => $bonus->getTitle(),
            ]), 'has-bonus', 2);

        } catch (\Exception $e) {
            Log::channel('login')->error($e->getMessage());
        }
    }
}
