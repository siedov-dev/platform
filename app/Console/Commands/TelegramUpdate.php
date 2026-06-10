<?php

namespace App\Console\Commands;

use App\Telegram\TelegramBot;
use Illuminate\Console\Command;

class TelegramUpdate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'telegram:update';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Process telegram messages';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        TelegramBot::update();
        return Command::SUCCESS;
    }
}
