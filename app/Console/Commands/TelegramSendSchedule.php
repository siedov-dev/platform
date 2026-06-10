<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class TelegramSendSchedule extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'telegram:send-schedule';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send telegram scheduled message';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        \App\Models\TelegramSendSchedule::sendMessages();
        return Command::SUCCESS;
    }
}
