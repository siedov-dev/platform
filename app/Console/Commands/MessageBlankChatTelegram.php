<?php

namespace App\Console\Commands;

use App\Models\MessageBlank;
use Illuminate\Console\Command;

class MessageBlankChatTelegram extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'blank:telegram-chat';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sends scheduled message-blank telegram messages';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        MessageBlank::processTelegramChatSchedule();
        return Command::SUCCESS;
    }
}
