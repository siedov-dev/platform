<?php

namespace App\PaymentsAPI;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ApiBase
{

    protected string $api_url = '';
    private string $api_token = '';
    protected string $api_local_host = 'https://krakenfn.com';
    protected array $headers = [];
    public int $last_http_code;

    protected $http;

    public function __construct()
    {
        $this->http = Http::acceptJson();
    }

    protected function request(string $uri, array $params = [], bool $post = true): object|bool|array
    {
        $url = $this->api_url . $uri;

        $this->http->withHeaders($this->headers);

        if ($post) {
            $response = $this->http->post($url, $params);
        } else {
            $response = $this->http->get($url, $params);
        }

        $this->last_http_code = $response->status();

        if (isset($hds['api-key'])) {
            $this->headers['api-key'] = substr($this->headers['api-key'], 0, 5) . str_repeat('*', strlen($this->headers['api-key']) - 5);
        }

        if (isset($this->headers['signature'])) {
            $this->headers['signature'] = substr($this->headers['signature'], 0, 5) . str_repeat('*', strlen($this->headers['signature']) - 5);
        }

        if (isset($this->headers['x-signature'])) {
            $this->headers['x-signature'] = substr($this->headers['x-signature'], 0, 5) . str_repeat('*', strlen($this->headers['x-signature']) - 5);
        }

        if (isset($this->headers['x-key'])) {
            $this->headers['x-key'] = substr($this->headers['x-key'], 0, 5) . str_repeat('*', strlen($this->headers['x-key']) - 5);
        }


        Log::channel('pay')->info(get_class($this) . ' API:', [
            'url' => $url,
            'method' => $post ? 'POST' : 'GET',
            'headers' => $this->headers,//$response->headers(),
            'params' => $params,
            'response' => $response->object() ?? $response->body(),
        ]);

        return $response?->object() ?? false;
    }

}
