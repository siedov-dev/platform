<?php

namespace App\Http\Controllers\Payments;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PayCallbackController
{
    public function guruDeposit(Request $request)
    {
        Log::channel('pay')->info('PayGuru raw ' . var_export($request->getContent(), true));
        Log::channel('pay')->info('PayGuru data ' . var_export($request->all(), true));

        $targetUrl = "http://127.0.0.1:23106/pay/guru";
        $response = Http::withHeaders($request->headers->all())
            ->withBody($request->getContent(), $request->header('Content-Type'))
            ->send($request->method(), $targetUrl);
        Log::channel('pay')->info('PayGuru callback response ' . var_export($response->body(), true));
        return response($response->body(), $response->status())->withHeaders($response->headers());
    }

    public function feniDeposit(Request $request)
    {
        Log::channel('pay')->info('Feni raw ' . var_export($request->getContent(), true));
        Log::channel('pay')->info('Feni data ' . var_export($request->all(), true));

        $targetUrl = "http://127.0.0.1:23106/pay/feni";
        $response = Http::withHeaders($request->headers->all())
            ->withBody($request->getContent(), $request->header('Content-Type'))
            ->send($request->method(), $targetUrl);
        Log::channel('pay')->info('Feni callback response ' . var_export($response->body(), true));
        return response($response->body(), $response->status())->withHeaders($response->headers());
    }

    public function norosDeposit(Request $request)
    {
        Log::channel('pay')->info('Noros raw ' . var_export($request->getContent(), true));
        Log::channel('pay')->info('Noros data ' . var_export($request->all(), true));

        $targetUrl = "http://127.0.0.1:23106/pay/noros";
        $response = Http::withHeaders($request->headers->all())
            ->withBody($request->getContent(), $request->header('Content-Type'))
            ->send($request->method(), $targetUrl);
        Log::channel('pay')->info('Noros callback response ' . var_export($response->body(), true));
        return response($response->body(), $response->status())->withHeaders($response->headers());
    }
}
