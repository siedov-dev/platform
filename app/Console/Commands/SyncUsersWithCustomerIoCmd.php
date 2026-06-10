<?php

namespace App\Console\Commands;

use App\CustomerIo\CustomerIo;
use App\CustomerIo\CustomerIoUser;
use App\Helpers\Hlog;
use App\MUser;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SyncUsersWithCustomerIoCmd extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'customerio:sync';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync all users with Customer Io service (can take long time)';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        Log::channel('retention')->info("Sync users stats started");
        $cio = new CustomerIo();
        $c = 0;
        MUser::select('id')->chunk(400, function ($u) use (&$c, $cio) {
            Log::channel('retention')->info("Sync users processing chunk " . $c * 400);
            $ids = $u->pluck('id')->toArray();
            $data = CustomerIoUser::selectUsersData($ids);
            $batch = [];
            foreach ($data as $uid => $d) {
                $cu = CustomerIoUser::updateData($uid, $d);
                if (!empty($cu->getEmail())) {
                    $batch_data = $cio->identityRequestData($cu->getUserId(), $cu->toCioAttributes());
                    $batch[] = $batch_data;
                } else {
                    Log::channel('retention')->info("Sync users no email " . $cu->getUserId());
                }
            }
            try {
                if (!empty($batch)) {
                    $resp = $cio->batch($batch);
                    Log::channel('retention')->info('batch result', $resp);
                } else {
                    Log::channel('retention')->info("batch empty");
                }
            } catch (\Throwable $e) {
                Hlog::logException($e);
            }
            $c++;
        });
        return Command::SUCCESS;
    }
}
