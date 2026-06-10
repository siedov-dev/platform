<?php

namespace App\Services\KSocket;

use App\Helpers\Hlog;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class KSocket
{
    private static ?KSocket $_instance = null;
    private string $token = "";
    private string $host = "http://127.0.0.1:8082";

    private function __construct()
    {
        $this->token = config('services.ServicesToken');
        $this->host = config('services.kSocketHost');
    }

    public static function getInstance(): KSocket
    {
        if (self::$_instance === null) {
            self::$_instance = new KSocket();
        }
        return self::$_instance;
    }

    public function setAuthToken(int $user_id, string $token): bool
    {
        try {
            $payload = [
                'user_id' => $user_id,
                'token' => $token,
            ];
            $resp = $this->request("authTokenSet", $payload);
            $result = json_decode($resp, true);
            if (is_array($result)) {
                if ($result['status'] !== 200) {
                    Log::error('Error authTokenSet', $result);
                    return false;
                }
                return true;
            }

        } catch (\Exception $e) {
            Hlog::logException($e);
        }
        return false;
    }

    public function sendEvent(int $user_id, string $event, $data = []): bool
    {
        try {
            $payload = [
                'user_id' => $user_id,
                'event' => $event,
                'data' => json_encode($data),
            ];
            $resp = $this->request("sendEvent", $payload);
            $result = json_decode($resp, true);
            if (is_array($result)) {
                if ($result['status'] !== 200) {
                    Log::error('Error sendEvent', $result);
                    return false;
                }
                return true;
            }

        } catch (\Exception $e) {
            Hlog::logException($e);
        }
        return false;
    }

    private function request(string $method, array $payload)
    {
        $endpoint = $this->host."/$method";
        $resp = Http::withToken($this->token)->acceptJson()->post($endpoint, $payload)->body();
        return $resp;
    }
}
