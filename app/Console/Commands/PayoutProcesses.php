<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class PayoutProcesses extends Command{

    protected $signature = 'payout:processes';

    public function handle()
    {
        $this->info(PayoutProcesses::class . ' process started');

        \App\Http\Controllers\PayController::processAutoPayments();

        $this->info(PayoutProcesses::class . ' process end');
    }

}
