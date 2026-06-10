<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Log;

class Hlog
{

    public static function logException(\Throwable $e, $channel = 'daily'): void
    {
        Log::channel($channel)->error($e->getMessage());
        Log::channel($channel)->error($e->getFile());
        Log::channel($channel)->error($e->getLine());
        Log::channel($channel)->error($e->getTraceAsString());
    }
}
