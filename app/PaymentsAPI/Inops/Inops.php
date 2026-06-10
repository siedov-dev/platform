<?php

namespace App\PaymentsAPI\Inops;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * @docs https://api.inops.net/docs/
 */
final class Inops
{
    private string $api_key = "";
    private string $url = "https://app-demo.inops.net/api";

    public function balance(string $terminal_id, string $currency = 'KZT'): array
    {
        $endpoint = $this->url . "/api/v1/terminals/getBalance/$terminal_id";
        $data = [
            'currency' => $currency,
        ];
        return $this->query($endpoint, $data, 'get');
    }

    public function checkStatus(string $id): array
    {
        $endpoint = $this->url . "/api/v1/payments/$id";
        $data = [
        ];
        return $this->query($endpoint, $data, 'get');
    }

    public function generateToken(): array
    {
        $endpoint = $this->url ."/merch-api/oauth2/token";
        $data = [
        ];
        return $this->query($endpoint, $data, 'get');
    }

    public function createPayment(string $tr_id, float $amount, string $paymentMethod, string $currency = 'KZT'): array
    {
        $endpoint = "/api/v1/payments";
        $data = [
            'referenceId' => $tr_id,
            'paymentType' => 'DEPOSIT',
            'paymentMethod' => $paymentMethod,
            'amount' => $amount,
            'currency' => $currency,
        ];
        return $this->query($endpoint, $data, 'post');
    }

    private function query(string $url, array $data = [], string $httpMethod = 'post')
    {
        Log::channel('pay')->info(__CLASS__ . " query $url method $httpMethod data:", $data);

        if (mb_strtolower($httpMethod) === 'get') {
            $result = Http::withToken($this->api_key)->acceptJson()->get($url, $data)->json();
        } else {
            $result = Http::withToken($this->api_key)->acceptJson()->post($url, $data)->json();
        }

        Log::channel('pay')->info(__CLASS__ . " query $url response:", $result);
        return $result;
    }
}
