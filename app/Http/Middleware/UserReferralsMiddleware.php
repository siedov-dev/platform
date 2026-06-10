<?php

namespace App\Http\Middleware;

use App\Helpers\Measure;
use App\Models\UserReferrals\ReferralEvents;
use App\Models\UserReferrals\UserReferral;
use App\Services\Referrals\ReferralsService;
use Closure;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Log;

class UserReferralsMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if (!empty($_GET['uref'])) {
            session()->put('uref', $_GET['uref']);
            session()->put('uref_click', $_GET['uref']);
            Log::info('uref click ' . $_GET['uref']);
            unset($_GET['uref']);
            if ($request->hasCookie('clickId')) {
                Cookie::queue(Cookie::forget('clickId'));
            }

            if (!$request->isMethod('post')) return redirect($request->url());
        }

//        if (!empty(session('uref_click')) && !empty(Cookie::get('fingerprint'))) {
        if (!empty(session('uref_click'))) {
            if (ReferralsService::getInstance()->serviceEnabled()) {
                ReferralsService::getInstance()->refClick(session('uref_click'), request()->headers->get('referer'), Cookie::get('fingerprint').":".$request->getClientIp());
                session()->forget('uref_click');
            }
        }
        return $next($request);
    }
}
