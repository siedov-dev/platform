<?php

namespace App\PaymentsAPI\Changelly;

use App\MUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * @docs https://fiat-api.changelly.com/docs/
 */
final class Changelly
{
    private string $api_key = "c5b74922e0bfb7e4b6d2e29e3a3fd8d0158d39bb2356324f6aad7f7a53dacd96";
    private string $private_key = "LS0tLS1CRUdJTiBQUklWQVRFIEtFWS0tLS0tCk1JSUV1d0lCQURBTkJna3Foa2lHOXcwQkFRRUZBQVNDQktVd2dnU2hBZ0VBQW9JQkFRQ0gzbThzUmYrKzBxdFMKZ0JhMEhmcWhWU3FwMWtTN2I5d244NU5BQ1p3dWc5cGc2bUFzWjV3OERYbEFYbVlOajUrZitMTjlkQ0RuVExXRApQMEp5R01OdnJia0hhOFlpbnZOM20yT01qUzZ3c3g3bWgvNzZwSHEvZzVkTmVzc0FnRTJlN1dIM2Nnb2V0NDVIClo1QVVDbktlQXpqVk1kTllWOHVKKzc2bDg0OVlrTEFVVnFQNGk1a3E1K3lIbHEvU2cxV1pXc2hKUGlWTXBhSnMKQVZvcHh3a1dVZEdiYVV2dFExb29zVnJmeElnZjZ3WjRLVlFUY3pyVElwdG1MUDU5VU5VN1hhNHI1RVcrcWU0cApiYmIxVTVEQnQ3YklrZjZac2RXWHdBbGZIelVkMENaOEh2NUtUTWRMWjNVcTJ0Z1ZtWnNBeWUxenU1U3llNi92CmczT3U1RC9oQWdNQkFBRUNnZ0VBRmpYNUU1SS8zQmlCVkVQSE1sdWlRS1IxREgyUUQ5b1o4ZGdDTHRwRFUyRlAKbmo5Q1NKQUpmTkd4RENKQ2FaNGNlS0RGNnRERW5qc3VndzJnMVprUnZkeDIzbTB5WHk5LzJvSW05RytSL2VYUwpPOXBvaHQzRGlUSkFWalNvSWVWWGl3OEFVRnl1cGRYWTV2aFpsOFViZHBSTlFxRGFEVnVRMVBZMzlOWUxqVzBnCnQzbnZLSFFEWGpTUWlFdExrNzFOdGdvUDBRdGV4SlZPcmdnU2YzNkUrb21hdEovdmorOWZCV1NybTVFa1NkVi8KM0E0WkNoUnowR1A1dGdSWTlWMlZJUmpYV2txanFndURYbGN1a3Y3SHBVem9xd2VHUEoySWo3Y25pQU9zUElCbQpLUThXVWdXdFFqK3JGMDJuZkxLMzd0TytHdFhobG5DT1Y4TWU1cW1jL3dLQmdRQzNVbTVVbGJ1Z2xRQkpqMlA1ClE3MmV1L282V2JtRXMwUVVTNnhiSEVzcmNLREJrV21TZHkwVlpuMytuL2Z1WnhCa1BOVDdlLzE0M25qam1tMDAKdEF1UXR2V211RE1RU0FUZE1PQmUrVkp6U2Nwa1VMT2pFSG5rbG95VjBlcHlXZGk2Vm91My9JWExVSGdYWWRFcQpDYWsyN1h2dW9mSmpTQmpQcUdTeTlmanRHd0tCZ1FDOXUrNForS0ZuWHJKa2hlbDZUaS9QUnYrMkNuMjg4NmVvCk5pMlVwWEc1RmgyMU1raGNiY2o0RXF5NU5SYVlWalZQbWV0UUhkaWRPSVNoWHR1ZFdTUlVFWkU1dGttN2MyUU0Kd2NWOHV4VzdzeVpUR2xtKzFDV2xRRmF0eEpjTUdHaVM0YmdpdTc0V3Iwdi8zY2pxMEx2cUI3a3FuZ2VNa1ZIaApRd2p4MVBqQ3N3S0JnQXVNdlBqRlNrSHV3VXJoSnRwUWcwVFo4UVQySERNWkhFUHZGSmZVNjROOElCU3g5YVhOCnJtQjN5WjVlTTcrb2JLYjBFak1aOTdsQ3d2a01CSzlhWEpRc2dzT1RQVnhjUFRvMlZ4eUV0c1llc3dxbzd2aHEKRkZQcmpFWHdLTVkxbE9zQWd1TC9uT3BYVW9tZWx3Ly85ZEpKR2hMSnp6eWtFVFFjRnVRVGpUQmJBb0dCQUpYbwoxNTdUa0l1QkNjaGMrZlYxczFhOWpTc0RMUWZQS0NmQzdzNmt6YTdrcDRKVUFWSnVON3l4M0tEVkxrNGN4ekJtCnQ4THNZZ3NPN2RRbWc3VFdDdHNWcWo0RUprdWlSMm5BN0UyNTc5a0hHbi9Nc1I0U3RzL1RmdWo3RERFMVUrTEIKejJBNklGNENFVFZQd2dDdC9kdFNVTjQ0VUZTak9TT1hlSWpDc2UzN0FuOXdTRTlBaWpXSVN4MDVSV1JtT2x3MAovc1FMZlZ1ZDRVWlZxZ3NRYmlrMkx1cmJKRHd3SE1pcDB0MC8weVZJZkFnZXFVaW1kUkR1ZXhFOHF1QTcwY3VGClM1cmZURll3TmZyWGQ2SDNKK3FCdWR2SXozR3V3OGpscWZNaUlvTTA0VTdsOWhnVEwrU2NabGpIeDlIMHJWdjkKWnJwMUsySExwMGNyZS82WitySFYKLS0tLS1FTkQgUFJJVkFURSBLRVktLS0tLQo=";
    private string $endpoint = "https://fiat-api.changelly.com";

    public function availableCountries()
    {
        $method = "/v1/available-countries";
        $data = [];

        return $this->query($method, 'get', $data);
    }

    public function currencies($type = 'fiat', $provider_code = 'moonpay')
    {
        $method = "/v1/currencies";
        $data = [
            'type' => $type,
            'providerCode' => $provider_code,
        ];

        return $this->query($method, 'get', $data);
    }

    public function providers()
    {
        $method = "/v1/providers";
        $data = [];

        return $this->query($method, 'get', $data);
    }

    public function validateWallet(string $currency, string $wallet_address)
    {
        $method = "/v1/validate-address";
        $data = [
            'currency' => $currency,
            'walletAddress' => $wallet_address,
        ];

        return $this->query($method, 'post', $data);
    }

    public function createOrder(string $order_id, string $user_id, string $provider, string $currency, string $crypto_currency, float $amountFrom, string $wallet)
    {
        $method = "/v1/orders";
        $data = [
            'externalOrderId' => $order_id,
            'externalUserId' => $user_id,
            'providerCode' => $provider,
            'currencyFrom' => $currency,
            'currencyTo' => $crypto_currency,
            'amountFrom' => number_format($amountFrom, 2, '.', ''),
            'country' => MUser::getAuth()->getCountry()?? 'UA',
            'walletAddress' => $wallet,
        ];

        return $this->query($method, 'post', $data);
    }

    private function sign(string $url, array $data): string
    {
        $private_key_object = base64_decode($this->private_key);
        $private_key_object = openssl_pkey_get_private($private_key_object);

        $payload = $url . json_encode($data, JSON_FORCE_OBJECT);

        openssl_sign($payload, $signature, $private_key_object, OPENSSL_ALGO_SHA256);
        return base64_encode($signature);
    }

    private function query(string $method, string $httpMethod, array $data = [])
    {
        $url = $this->endpoint . $method;

        $ch = curl_init();

        $payload = json_encode($data, JSON_FORCE_OBJECT);

        if ( strtolower($httpMethod) === 'post') {

            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);

        } else {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
            if (!empty($data)) {
                $url .= '?' . http_build_query($data);
                $data = [];
            }
        }

        $sign = $this->sign($url, $data);

        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'X-Api-Key: ' . $this->api_key,
            'X-Api-Signature: ' . $sign,
            'Content-Type: ' . 'application/json',
        ]);

        Log::channel('pay')->info(__CLASS__.' request: '.$url);
        Log::channel('pay')->info(var_export($data, true));
        Log::channel('pay')->info(__CLASS__.' request body: '.$url);
        Log::channel('pay')->info($payload);

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);

        curl_setopt($ch, CURLOPT_USERAGENT, 'KrakenCassino');
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_MAXREDIRS, 10);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

        curl_setopt($ch, CURLOPT_VERBOSE, true);

        $verbose = fopen('php://temp', 'w+');
        curl_setopt($ch, CURLOPT_STDERR, $verbose);

        $json = curl_exec($ch);
        $err = curl_error($ch);
        $resp_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $resp = json_decode($json, true);

        rewind($verbose);
        $verboseLog = stream_get_contents($verbose);

        Log::channel('pay')->info('curl verbose');
        Log::channel('pay')->info(htmlspecialchars($verboseLog));

        if ($err) {
            Log::channel('pay')->error($err);
        }

        Log::channel('pay')->info(__CLASS__.' resp:');
        Log::channel('pay')->info('code:'.$resp_code);
        Log::channel('pay')->info($json);
        Log::channel('pay')->info(var_export($resp, true));
        return $resp;
    }

    public static function depositCallback(Request $request)
    {
        Log::channel('pay')->info(__CLASS__.' callback deposit request');
        Log::channel('pay')->info(var_export($request->getUri(), true));
        Log::channel('pay')->info(var_export($request->getContent(), true));
        Log::channel('pay')->info(var_export($request->all(), true));

        return response('OK', 200);
    }

}
