<?php

namespace App\Http\Middleware;

use App\Helpers\GoldPartnersSdk;
use Closure;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Session;

class CheckPartnerLink
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
        $clickId = $request->query('clickId');
        if (Auth::check() && !Session::has('visitOnline:'.Auth()->id()) && $clickId && Auth::user()->click_id == $clickId) {
            Session::put('visitOnline:'.Auth()->id(), 1);
            // Create message and send it to GoldPartners.
            $casinoId = intval(config('goldPartners.casinoId'));
            $secretKey = config('goldPartners.secretKey');

            $sdk = new GoldPartnersSdk($casinoId, $secretKey);
            $sdk->clickId  = $clickId;
            $sdk->clientName = Auth::user()->login;
            $sdk->clientId = Auth::id();
            $msg = $sdk->createMessage(GoldPartnersSdk::EVENT_VISIT_ONLINE);
            $sdk->send($msg);
        }

        if (!$request->hasCookie('clickId') && $clickId) {
            //redirect destroys post
//            return redirect($request->fullUrl())->withCookie(cookie()->forever('clickId', $clickId));
            Cookie::queue('clickId', $clickId, 31104000);
        }
        \session()->forget('uref');
        \session()->forget('uref_click');

        if (!empty($_GET['promo'])) {
            \session()->put('promocode', $_GET['promo']);
        }

        return $next($request);
    }
}
