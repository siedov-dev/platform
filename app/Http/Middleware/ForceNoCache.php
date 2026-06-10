<?php

namespace App\Http\Middleware;

use Closure;

class ForceNoCache
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $response = $next($request);
        $response->header('Cache-Control', 'no-store, no-cache, must-revalidate');
        $response->header('Expires', date("r"));
        return $response;
    }
}
