<?php

namespace App\Console\Commands;

use App\Models\UserNotifications;
use Illuminate\Console\Command;

class userNotify extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'user:notify {user_id : The ID of the user} {title : message title} {message : text}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Notify user message';

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
        UserNotifications::notify($this->argument('user_id'), $this->argument('title'), $this->argument('message'));
    }
}
