<?php

namespace App\Http\Controllers;

use GuzzleHttp\Client;
use Illuminate\Http\Request;

class GmProxyController extends Controller
{
    public function proxy(Request $request, string $path = '')
    {
        $host = config('services.gameManagerHost');
        $port = config('services.gameManagerPort');
        $base = rtrim("$host:$port", "/");
        $url = $base . '/' . ltrim($path, '/');

        $client = new Client([
            'http_errors' => false,
            'timeout' => 15,
        ]);

        $method = $request->getMethod();

        $options = [
            'headers' => $this->forwardRequestHeaders($request),
            'query'   => $request->query(),
            'body'    => $request->getContent(),
        ];

        try {
            $response = $client->request($method, $url, $options);

            $content = (string) $response->getBody();
            $status  = $response->getStatusCode();
            $headers = $this->filterResponseHeaders($response->getHeaders());

            return response($content, $status)->withHeaders($headers);
        } catch (\Throwable $e) {
            return response()->json([
                'error' => 'Upstream request failed',
                'message' => $e->getMessage(),
            ], 502);
        }
    }

    private function forwardRequestHeaders(Request $request): array
    {
        // Копируем входящие заголовки, исключая hop-by-hop и те, что задаются автоматически
        $skip = [
            'host',
            'content-length',
            'transfer-encoding',
            'connection',
            'accept-encoding',
        ];

        $headers = [];
        foreach ($request->headers->all() as $name => $values) {
            if (in_array(strtolower($name), $skip, true)) {
                continue;
            }
            $headers[$name] = implode(', ', $values);
        }

        // Добавим X-Forwarded-* заголовки
        $headers['X-Forwarded-For'] = trim(($headers['X-Forwarded-For'] ?? '') . ', ' . $request->ip(), ', ');
        $headers['X-Forwarded-Host'] = $request->getHost();
        $headers['X-Forwarded-Proto'] = $request->getScheme();

        return $headers;
    }

    private function filterResponseHeaders(array $upstreamHeaders): array
    {
        $skip = [
            'transfer-encoding',
            'connection',
            'content-encoding',
        ];

        $headers = [];
        foreach ($upstreamHeaders as $name => $values) {
            if (in_array(strtolower($name), $skip, true)) {
                continue;
            }
            // Берем первое значение (чаще всего достаточно). Можно объединять при необходимости.
            $headers[$name] = is_array($values) ? $values[0] : $values;
        }

        return $headers;
    }

}
