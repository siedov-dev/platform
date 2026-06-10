<?php

namespace App\Http\Middleware;

use App\Services\IpInfo\IpInfo;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class CheckIpBlock
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
        $ip = $request->ip();
        $blocked = IpInfo::isBlocked($ip);
        if ($blocked['blocked']?? false) {
                Log::info('blocking ip '. $ip);
                if (($blocked['reason']??'') == 'Country') {
                    return response('
<html lang="en"><head></head><body>
<div style="display: flex; justify-content: center; align-items: center; height: 100vh;">
<div style="display: flex; flex-flow: column; align-items: center; justify-content: center">
 <svg xmlns="http://www.w3.org/2000/svg" viewBox="420 250 176 137" style="width: 180px; height: 180px; margin-top: -100px;">
  <g>
    <g>
      <path d="M527.006,385.157c-1.812,0-3.281-1.469-3.281-3.281v-55.807c0-1.812,1.469-3.281,3.281-3.281s3.281,1.469,3.281,3.281v55.807c0,1.812-1.469,3.281-3.281,3.281Z" fill="#ff4900"/>
      <path d="M517.558,373.941c-1.812,0-3.281-1.469-3.281-3.281v-55.807c0-1.812,1.469-3.281,3.281-3.281s3.281,1.469,3.281,3.281v55.807c0,1.812-1.469,3.281-3.281,3.281Z" fill="#ff4900"/>
      <path d="M508.111,366.706c-1.812,0-3.281-1.469-3.281-3.281v-55.806c0-1.812,1.469-3.281,3.281-3.281s3.281,1.469,3.281,3.281v55.806c0,1.812-1.469,3.281-3.281,3.281Z" fill="#ff4900"/>
      <path d="M498.664,376.313c-1.812,0-3.281-1.469-3.281-3.281v-55.807c0-1.812,1.469-3.281,3.281-3.281s3.281,1.469,3.281,3.281v55.807c0,1.812-1.469,3.281-3.281,3.281Z" fill="#ff4900"/>
    </g>
    <path d="M502.497,271.647c4.594-7.958,16.08-7.958,20.675,0l15.428,26.723,15.428,26.723c4.594,7.958-1.149,17.905-10.337,17.905h-61.714c-9.189,0-14.932-9.947-10.337-17.905l15.428-26.723,15.428-26.723Z" fill="#ff4900"/>
    <path d="M508.44,334.343c-1.168-1.119-1.751-2.512-1.751-4.181s.583-3.061,1.751-4.181c1.167-1.119,2.655-1.68,4.467-1.68,1.762,0,3.216.561,4.359,1.68,1.144,1.12,1.715,2.513,1.715,4.181s-.572,3.062-1.715,4.181c-1.144,1.12-2.597,1.679-4.359,1.679-1.811,0-3.3-.559-4.467-1.679ZM518.052,284.782l-1.143,34.518h-8.433l-1.215-34.518h10.791Z" fill="#fff"/>
  </g>
</svg>
<style>*{margin: 0; padding: 0;}h1{font-size: 4rem}h5{color: #444; font-size: 1.5rem; font-weight: 500;}b{color: #000; font-weight: 700;}</style>
<h1>SORRY</h1><h5>but players from your country are not allowed to visit '.$request->getHost().'.</h5><h5>Check the operation of your <b>VPN</b>.</h5><h5>If you have any questions, please contact info@krakenmail.info</h5>
</div></div></body></html>
', 403);
                }

                return response('ip temporarily blocked, contact <a href="https://t.me/KrakenCasinoSup_bot">https://t.me/KrakenCasinoSup_bot</a>', 403);
        }

        return $next($request);
    }
}
