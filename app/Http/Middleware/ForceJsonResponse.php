<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Log;

class ForceJsonResponse
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
        $request->headers->set('Accept', 'application/json');
        $response = $next($request);
        //$response->header('Cache-Control', 'public, no-store, no-cache, must-revalidate, no-transform');
        $response->header('Content-Length', strlen( !is_array($response->getOriginalContent()) ? $response->getOriginalContent() : $response->getContent()));
        $response->header('Content-Type', 'application/json');
//        Log::info('content length '.var_export($response, true));
        return $response;
    }
}
