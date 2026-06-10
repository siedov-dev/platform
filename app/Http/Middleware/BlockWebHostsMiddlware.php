<?php

namespace App\Http\Middleware;

use App\Helpers\Hosts;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class BlockWebHostsMiddlware
{
    /**
     * Handle an incoming request.
     * Block requests on financial and game hosts
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        if (in_array('https://' . $request->getHttpHost(), [...Hosts::getFinHosts(), ...Hosts::getGamesHosts()])) {
            die(404);
        }
        return $next($request);
    }
}
