<?php

namespace App\Console\Commands;

use App\Helpers\Hlog;
use App\Helpers\Localization;
use App\Models\Popups\PopupStatus;
use App\Models\Popups\UserPopup;
use App\Models\Popups\UserPopupSchedule;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Redis;

class CheckPopups extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'popups:check';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Process popups schedule, check if global popups are completed';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        try{
            UserPopupSchedule::processSchedule();
        } catch (\Exception $e) {
            Hlog::logException($e);
        }

        try{
            foreach (Localization::getAvailableLocales() as $locale)
            if (!Redis::get('no-global-popups-'.$locale, false)) {
                //get all current global popups
                $popups = UserPopup::where('user_id', UserPopup::GLOBAL_POPUP)
                    ->where('status', PopupStatus::New)
                    ->where('locale', $locale)
                    ->orderBy('priority')
                    ->get();

                if ($popups->count() === 0) {
                    Redis::set('no-global-popups-'.$locale, true);
                }
            }
        } catch (\Exception $e) {
            Hlog::logException($e);
        }

        return Command::SUCCESS;
    }
}
