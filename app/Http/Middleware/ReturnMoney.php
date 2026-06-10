<?php

namespace App\Http\Middleware;

use App\Helpers\Measure;
use App\Http\Controllers\EmulatorController;
use App\MUser;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ReturnMoney
{
    /**
     * @param Request $request
     * @param Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
//        Measure::logNow('ReturnMoney');
        Log::info('ReturnMoney '.Auth::id().' '.$request->path());
        /** @var MUser $user */
        $user = Auth::user();
        if ($user !== null) {
            EmulatorController::transferMoneyBack();
        }

//        Measure::logNow('ReturnMoney');
        return $next($request);
    }
}
