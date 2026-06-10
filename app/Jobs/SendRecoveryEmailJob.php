<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;

class SendRecoveryEmailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private int $user_id;
    private string $domain;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(int $user_id, ?string $domain = null)
    {
        //
        $this->user_id = $user_id;
        $this->domain = $domain;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        Log::channel('mail')->info('sending recovery email for user#'.$this->user_id);
        Artisan::call('mail:sendrecovery', ['user_id' => $this->user_id, 'domain' => $this->domain]);
    }
}
