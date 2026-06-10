<?php

namespace App\Jobs;

use App\Models\Slc\SlcDailyReward;
use App\Models\Slc\SlcDailyTask;
use App\Models\Slc\SlcUserInventory;
use App\MUser;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SlcDailyRewardJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private int $user_id;
    private ?string $prev_active;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(int $user_id, ?string $prev_active = null)
    {
        $this->user_id = $user_id;
        $this->prev_active = $prev_active;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try {
            /** @var MUser $user */
            $user = MUser::findOrFail($this->user_id);

            $last_reward_day = $user->getValue('last_daily_reward', null);
            //if already got the reward
            if ($last_reward_day === now()->format('Y-m-d')) return;

            $last_active_day = $user->getValue('active_day', 1);
            $yesterday = now()->subDay()->format('Y-m-d');

            // if user wasn't active on the day before reset active days count
            if ($last_reward_day === null || $last_reward_day !== $yesterday) {
                $last_active_day = 1;
            } else {
                // after 7th active day reset days count
                if ($last_active_day > 6) {
                    $last_active_day = 1;
                } else {
                    $last_active_day++;
                }
            }

            //set current active day
            $user->setValue('last_daily_reward', now()->format('Y-m-d'));
            $user->setValue('active_day', $last_active_day);

            // give the reward
//        $tasks = SlcDailyTask::all()->pluck('reward', 'day')->toArray();
//        $user->getSlcWallet()->add($tasks[$last_active_day]?? 0, "daily-".$last_active_day);

            $rewards = SlcDailyReward::where('day', $last_active_day)->get();
            $inventory = SlcUserInventory::forUser($user);
            /** @var SlcDailyReward $reward */
            foreach ($rewards as $reward) {
                $inventory->addItem($reward, "daily-".$last_active_day);
            }

        } catch (\Exception $e) {
            Log::error($e->getMessage());
            Log::error($e->getLine());
            Log::error($e->getFile());
            Log::error($e->getTraceAsString());
        }
    }
}
