<?php

namespace App\Console\Commands;

use App\Models\MessageBlank;
use Illuminate\Console\Command;

class MessageBlankTelegram extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'blank:telegram';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sends scheduled message-blank telegram manager chat messages';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        MessageBlank::processTelegramSchedule();
        return Command::SUCCESS;
    }
}
