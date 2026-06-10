<?php

namespace App\Console\Commands;

use App\Helpers\Hlog;
use App\Services\Users\UserManager;
use App\Models\UserOnlineLog;
use Illuminate\Console\Command;

class DumpOnlineUsers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'users:dump-online';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Save current online users log';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        try {
            $users = UserManager::users();
            if (is_array($users)) {
                UserOnlineLog::addLog($users, 'ok');
            } else {
                UserOnlineLog::addLog([], 'err');
            }
        } catch (\Exception $e) {
            Hlog::logException($e);
        }
        return Command::SUCCESS;
    }
}
