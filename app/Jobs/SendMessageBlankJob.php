<?php

namespace App\Jobs;

use App\Models\MessageBlank;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendMessageBlankJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    private int $user_id;
    private string $blank_name;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(int $user_id, string $blank_name)
    {
        $this->user_id = $user_id;
        $this->blank_name = $blank_name;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        Log::channel('retention')->info('sending blank job user#'.$this->user_id." blank".$this->blank_name);
        /** @var MessageBlank $blank */
        $blank = MessageBlank::where('name', $this->blank_name)->first();
        if ($blank === null) {
            Log::channel('retention')->error('sending blank job user#'.$this->user_id." blank".$this->blank_name." blank not found");
            return;
        }
        $blank->sendMail([$this->user_id], []);
    }
}
