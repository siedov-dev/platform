<?php

namespace App\Console\Commands;

use App\Http\Controllers\ReferralController;
use App\Models\UserReferrals\ReferralEvents;
use App\Models\UserReferrals\UserReferral;
use App\MUser;
use App\UserWallet;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class ReferralsProcess extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'referrals:process';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Calculate user referrals profits';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        //
        set_time_limit( ini_get('max_execution_time') + 600);
        $st_date = now()->firstOfMonth()->subDay()->firstOfMonth();
        $end_date = now()->firstOfMonth();

        Log::info('Processing referal users profit. From date:'.$st_date.' to date:'.$end_date);

        /** @var UserReferral[] $referrals */
        $referrals = UserReferral::get();

        foreach ($referrals as $ref) {

            /** @var MUser $user */
            $user = MUser::find($ref->user_id);
            if ($user === null) continue;

            foreach ($user->getActiveCurrencies() as $curr_id) {
                $profit = ReferralEvents::where('referral_events.ref_id', $ref->id)
                    ->where('referral_events.created_at', '>=', $st_date->format('Y-m-d H:i:s'))
                    ->where('referral_events.created_at', '<', '2025-07-16 00:00:00')
                    ->where('type', ReferralEvents::TYPE_MONEY_SPENT)
                    ->where('currency', $curr_id)
                    ->whereIn('status', [ReferralEvents::STATUS_PAYED])
                    ->sum('profit');

                Log::info('User#'.$ref->user_id.' profit:'.$profit.' '.UserWallet::currencyName($curr_id));
                if ($profit > 0) {
                    $user->getWallet($curr_id)->addReferralMoney($profit);
                }

//                ReferralEvents::where('referral_events.ref_id', $ref->id)
//                    ->where('referral_events.created_at', '>=', $st_date->format('Y-m-d H:i:s'))
//                    ->where('referral_events.created_at', '<', $end_date->format('Y-m-d H:i:s'))
//                    ->where('type', ReferralEvents::TYPE_MONEY_SPENT)
//                    ->where('currency', $curr_id)
//                    ->whereIn('status', [ReferralEvents::STATUS_HOLD, ReferralEvents::STATUS_NONE])
//                    ->update(['status' => ReferralEvents::STATUS_PAYED]);

            }
        }

        return '';
    }
}
