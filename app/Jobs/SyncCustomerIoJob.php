<?php

namespace App\Jobs;

use App\CustomerIo\CustomerIoUser;
use App\Helpers\Hlog;
use App\MUser;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SyncCustomerIoJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private int $user_id;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(int $user_id)
    {
        $this->user_id = $user_id;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        //
        Log::info('sync customer io job user id#'.$this->user_id);
        $user = MUser::find($this->user_id);
        if ($user === null) {
            Log::error(__CLASS__." no user found id #".$this->user_id);
            return;
        }
        try {
            CustomerIoUser::syncUser($user);
        } catch (\Throwable $e) {
            Hlog::logException($e);
        }
    }
}
