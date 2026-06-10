<?php

namespace App\Jobs;

use App\Services\IpInfo\IpInfo;
use App\Models\UserLoginLog;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class LogLoginIpInfoJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private string $ip;
    private int $log_id;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(int $log_id, string $ip)
    {
        $this->log_id = $log_id;
        $this->ip = $ip;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        //
        $log = UserLoginLog::find($this->log_id);
        if ($log !== null) {
//            Log::info('token:'. config('services.kIpInfoToken'));
            $info = IpInfo::getInfo($this->ip);
            if (!empty($info->country)) {
                $log->country = $info->country ?? '';
                $log->country_code = $info->country_code ?? '';
                $log->region = $info->region ?? '';
                $log->city = $info->city ?? '';
                $log->lat = $info->lat ?? '';
                $log->long = $info->long ?? '';
                $log->isp = $info->isp ?? '';
                $log->org = $info->org ?? '';
                $log->continent = $info->continent ?? '';
                $log->currency = $info->currency ?? '';
            }

            $log->save();
        }
    }
}
