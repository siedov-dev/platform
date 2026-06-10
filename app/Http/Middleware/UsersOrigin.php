<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Log;

class UsersOrigin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        if (Auth::guest()) {
            $referer = $_SERVER['HTTP_REFERER'] ?? request()->headers->get('referer');

            if (!empty($referer) && !str_starts_with($referer, $request->getSchemeAndHttpHost())) {
                Cookie::queue('origRef', $referer, 31104000);
            }
        }
        return $next($request);
    }
}
