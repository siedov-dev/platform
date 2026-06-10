<?php

namespace App\Services\KCms;

use App\Helpers\Hlog;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class KCms
{

    private static ?KCms $_instance = null;
    private string $token = "";
    private string $host = "";

    private function __construct()
    {
        $this->token = config('services.ServicesToken');
        $this->host = config('services.kCmsHost');
    }

    public static function getInstance(): KCms
    {
        if (self::$_instance === null) {
            self::$_instance = new KCms();
        }
        return self::$_instance;
    }

    /**
     * @param int $user_id
     * @return array{cd: int, err: string, state: UserCallRequestState}
     */
    public function userCallRequestGetStatus(int $user_id): array
    {
        try {
            $payload = [
                'user_id' => $user_id,
            ];
            $resp = $this->request("UserCallRequest.getState", $payload);
            $result = json_decode($resp, true);
            if (is_array($result)) {
                if ($result['cd'] !== 200) {
                    Log::error('Error userCallRequestGetStatus', $result);
                    return ['cd' => $result['cd'], 'err' => $result['err'],];
                }
                return [
                    'cd' => $result['cd'],
                    'state' => UserCallRequestState::from($result['state']),
                ];
            }
        } catch (\Exception $e) {
            Hlog::logException($e);
        }
        return ['cd' => 500, 'err' => "System error",];
    }

    /**
     * @param int $user_id
     * @return array{cd: int, err: string}
     */
    public function userCallRequestMake(int $user_id): array
    {
        try {
            $payload = [
                'user_id' => $user_id,
            ];
            $resp = $this->request("UserCallRequest.make", $payload);
            $result = json_decode($resp, true);
            if (is_array($result)) {
                return $result;
            }
        } catch (\Exception $e) {
            Hlog::logException($e);
        }
        return ['cd' => 500, 'err' => "System error",];
    }

    /**
     * @param int $user_id
     * @return array{cd: int, err: string}
     */
    public function userCallRequestAccept(int $user_id, int $manager_id): array
    {
        try {
            $payload = [
                'user_id' => $user_id,
                'manager_id' => $manager_id,
            ];
            $resp = $this->request("UserCallRequest.accept", $payload);
            $result = json_decode($resp, true);
            if (is_array($result)) {
                return $result;
            }
        } catch (\Exception $e) {
            Hlog::logException($e);
        }
        return ['cd' => 500, 'err' => "System error",];
    }

    public function userCallRequestPendingCount(): int
    {
        try {
            $payload = [
            ];
            $resp = $this->request("UserCallRequest.pendingCount", $payload);
            $result = json_decode($resp, true);
            if (is_array($result)) {
                return $result['count']?? 0;
            }
        } catch (\Exception $e) {
            Hlog::logException($e);
        }
        return 0;
    }

    public function serviceEnabled(): bool
    {
        try {
            return $this->ping() === "ok";
        } catch (\Exception $e) {
            Hlog::logException($e);
            return false;
        }
    }

    public function ping(): string
    {
        try {
            $endpoint = $this->host;
            return Http::withToken($this->token)->get($endpoint)->body();
        } catch (\Exception $e) {
            Hlog::logException($e);
            return false;
        }
    }

    private function request(string $method, array $payload)
    {
        $endpoint = $this->host."/api/$method";
        $resp = Http::withToken($this->token)
            ->timeout(3) //  максимум 3 секунды ждём
            ->acceptJson()
            ->post($endpoint, $payload)
            ->body();
        return $resp;
    }

}
