<?php

namespace App\Http\Middleware;

use App\Helpers\Popup;
use Closure;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class CheckToken
{
    /**
     * except routes
     * @var array|string[]
     */
    private array $except = [
        'pay.success',
        'pay.fail',
    ];
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
//        Log::info($request->route()->getName());
        if (Auth::check() && !in_array($request->route()->getName(), $this->except) ) {
            if (session('user_token') !== Auth::user()->token) {
                Log::error('wrong token user token ' . Auth::user()->token . ' session token:' . session('user_token')
                    . ' user#'.Auth::id()
                    . ' request:' . $request->getUri());
                Auth::logout();
                return redirect()->action('AuthController@login');
            }
        }
        return $next($request);
    }
}
