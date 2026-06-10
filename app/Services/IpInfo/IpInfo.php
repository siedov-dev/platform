<?php

namespace App\Services\IpInfo;

use App\Helpers\Hlog;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Class IpInfo
 * Предоставляет информацию об ip адресах из сервиса kipinfo.
 */
class IpInfo
{
    public string $ip = "";
    public ?string $continent = null;
    public ?string $country = null;
    public ?string $country_code = null;
    public ?string $region = null;
    public ?string $city = null;
    public ?string $lat = null;
    public ?string $long = null;
    public ?string $currency = null;
    public ?string $isp = null;
    public ?string $org = null;
    public ?Carbon $block_to = null;
    public bool $whitelist = false;

    public function __construct(array $data = [])
    {
        $this->ip = $data['ip'] ?? '';
        $this->continent = $data['continent'] ?? null;
        $this->country = $data['country'] ?? null;
        $this->country_code = $data['country_code'] ?? null;
        $this->region = $data['region'] ?? null;
        $this->city = $data['city'] ?? null;
        $this->lat = $data['lat'] ?? null;
        $this->long = $data['long'] ?? null;
        $this->currency = $data['currency'] ?? null;
        $this->isp = $data['isp'] ?? null;
        $this->org = $data['org'] ?? null;
        $this->whitelist = $data['whitelist'] ?? false;
        if (!empty($data['block_to'])) {
            $this->block_to = Carbon::parse($data['block_to']);
        }
    }

    /**
     * Возвращает информацию об шз адресе
     * @param string $ip
     * @param bool $no_cache
     * @param bool $save
     * @return IpInfo|null
     */
    public static function getInfo(string $ip, bool $no_cache = false, bool $save = true): ?IpInfo
    {
        $host = config('services.kIpInfoHost');
        if ($ip === '127.0.0.1') {
            $ip = '5.45.72.167';
        }
        $url = $host.'/ip/'.$ip;
        if ($no_cache) {
            $url = $host.'/ipnocache/'.$ip;
        }

        try {
            $resp = Http::withToken(config('services.ServicesToken'))->get($url, []);
            if ($resp->status() !== 200) {
                Log::error('error get ip info '.$resp->status());
                return null;
            }
            $data = $resp->json();
            if ($data['status'] === 200) {
                return new IpInfo($data['data']);
            }
        } catch (\Exception $e) {
            Hlog::logException($e);
        }

        return null;
    }

    /**
     * Заблокирован ли ip
     * @param string $ip
     * @return array{
     *     status: int,
     *     blocked: bool,
     *     reason?: string,
     * } block status array
     */
    public static function isBlocked(string $ip): array
    {
        $host = config('services.kIpInfoHost');
        if ($ip === '127.0.0.1') {
            $ip = '5.45.72.167';
        }
        $url = $host.'/isblocked/'.$ip;

        try {
            $resp = Http::withToken(config('services.ServicesToken'))->get($url, []);
            if ($resp->status() !== 200) {
                Log::error('error get ip info '.$resp->status());
                return ['status' => 500, 'blocked' => false];
            }
            $data = $resp->json();
            if ($data['status'] !== 200) {
                Log::error('error get ip info ', $data);
                return $data;
            }
            return $data;
        } catch (\Exception $e) {
            Hlog::logException($e);
        }

        return ['status' => 500, 'blocked' => false];
    }

    /**
     * заблокировать ip
     * @param string $ip
     * @param string $date_to
     * @return string[]
     */
    public static function Block(string $ip, string $date_to): array
    {
        $host = config('services.kIpInfoHost');
        $url = $host.'/block/'.$ip.'/'.$date_to;
        try {
            $resp = Http::withToken(config('services.ServicesToken'))->get($url, []);
            if ($resp->status() !== 200) {
                Log::error('error block '.$resp->status());
                return ['error' => 'error block '.$resp->status()];
            }
            $data = $resp->json();
            return $data;
        } catch (\Exception $e) {
            Hlog::logException($e);
        }
        return ['error' => 'error block'];
    }

    /**
     * Разблокировать ip
     * @param string $ip
     * @return string[]
     */
    public static function UnBlock(string $ip): array
    {
        $host = config('services.kIpInfoHost');
        $url = $host.'/unblock/'.$ip;
        try {
            $resp = Http::withToken(config('services.ServicesToken'))->get($url, []);
            if ($resp->status() !== 200) {
                Log::error('error unblock '.$resp->status());
                return ['error' => 'error unblock '.$resp->status()];
            }
            $data = $resp->json();
            return $data;
        } catch (\Exception $e) {
            Hlog::logException($e);
        }
        return ['error' => 'error unblock'];
    }

    /**
     * Перезагрузить заблокированные страны
     * @return string[]
     */
    public static function reloadBlockedCountries(): array
    {
        $host = config('services.kIpInfoHost');
        $url = $host.'/reload/BlockedCountries';
        try {
            $resp = Http::withToken(config('services.ServicesToken'))->get($url, []);
            if ($resp->status() !== 200) {
                Log::error('error reloading blocked countries '.$resp->status());
                return ['error' => 'error reloading blocked countries '.$resp->status()];
            }
            $data = $resp->json();
            return $data;
        } catch (\Exception $e) {
            Hlog::logException($e);
        }
        return ['error' => 'error reloading blocked countries'];
    }

    /**
     * Устанавливает флаг whitelist для ip
     * @param string $ip
     * @param bool $set
     * @return array
     */
    public static function whitelistSet(string $ip, bool $set = true): array
    {
        $host = config('services.kIpInfoHost');
        $url = $host.'/whitelist/'.$ip.'/'.($set? 1 : 0);
        try {
            $resp = Http::withToken(config('services.ServicesToken'))->get($url, []);
            if ($resp->status() !== 200) {
                Log::error('error whiteListSet '.$resp->status());
                return ['error' => 'error whiteListSet '.$resp->status()];
            }
            $data = $resp->json();
            return $data;
        } catch (\Exception $e) {
            Hlog::logException($e);
        }
        return ['error' => 'error whiteListSet'];
    }

    /**
     * Возвращает информацию в виде массива
     * @return array
     */
    public function toArray(): array
    {
        return [
            'ip' => $this->ip,
            'continent' => $this->continent,
            'country' => $this->country,
            'country_code' => $this->country_code,
            'region' => $this->region,
            'city' => $this->city,
            'lat' => $this->lat,
            'long' => $this->long,
            'currency' => $this->currency,
            'isp' => $this->isp,
            'org' => $this->org,
            'block_to' => $this->block_to?->format('Y-m-d H:i:s'),
            'whitelist' => $this->whitelist,
        ];
    }
}
