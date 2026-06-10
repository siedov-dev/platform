<?php

namespace App\Console\Commands;

use App\Helpers\Currency;
use App\Models\BonusSchedule;
use App\Models\EmailSchedule;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class EveryMinuteTasks extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'minuteTasks';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'process tasks in background';

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
        $process = [];

        $pid = pcntl_fork();
        if ($pid > 0) {
            $process[] = $pid;
        } else {
            try{
                Log::info('processing background every-minute tasks');
                Log::info($pid);
            } catch (\Exception $e) {
                Log::error($e->getMessage());
            }
            exit();
        }

        $pid = pcntl_fork();
        if ($pid > 0) {
            $process[] = $pid;
        } else {
            try {
                DB::reconnect();
                BonusSchedule::processBonusSchedule();
            } catch (\Exception $e) {
                Log::error($e->getMessage());
            }
            exit();
        }

        $pid = pcntl_fork();
        if ($pid > 0) {
            $process[] = $pid;
        } else {
            try{
                DB::reconnect();
                \App\Models\BonusSchedule::sendNotifications();
            } catch (\Exception $e) {
                Log::error($e->getMessage());
            }
            exit();
        }

        $pid = pcntl_fork();
        if ($pid > 0) {
            $process[] = $pid;
        } else {
            try{
                DB::reconnect();
                EmailSchedule::process();
            } catch (\Exception $e) {
                Log::channel('mail')->error($e->getMessage());
            }

            exit();
        }

        //every 4 minutes
        if (date('i')%4 === 0) {
            $pid = pcntl_fork();
            if ($pid > 0) {
                $process[] = $pid;
            } else {
                try {
                    DB::reconnect();
                    Currency::cacheData();
                } catch (\Exception $e) {
                    Log::error($e->getMessage());
                }

                exit();
            }
        }

        $this->info('waiting to complete processes:');
        $this->info(var_export($process, true));
        foreach ($process as $proc) {
            $this->info("wait process $proc");
            pcntl_waitpid($proc, $status);
            $this->info("process $proc status $status");
        }

        return 0;
    }
}
