<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class UserLoginLog
 * Модель логов авторизации пользователей
 *
 * @property int $id
 * @property int $user_id
 * @property string $ip
 * @property string $browser
 * @property string $browser_version
 * @property string $platform
 * @property string $device_name
 * @property string $device_type
 * @property string $continent
 * @property string $country
 * @property string $country_code
 * @property string $region
 * @property string $city
 * @property string $lat
 * @property string $long
 * @property string $currency
 * @property string $isp
 * @property string $org
 * @property int $cookie_uid
 * @property int $storage_uid
 * @property string $domain
 * @property ?string $fingerprint
 */
class UserLoginLog extends Model
{
    public function getId(): int
    {
        return $this->id;
    }

    public function getUserId(): int
    {
        return $this->user_id;
    }

    public function getIp(): string
    {
        return $this->ip;
    }

    public function getBrowser(): string
    {
        return $this->browser;
    }

    public function getBrowserVersion(): string
    {
        return $this->browser_version;
    }

    public function getPlatform(): string
    {
        return $this->platform;
    }

    public function getDeviceName(): string
    {
        return $this->device_name;
    }

    public function getDeviceType(): string
    {
        return $this->device_type;
    }

    public function getContinent(): string
    {
        return $this->continent;
    }

    public function getCountry(): string
    {
        return $this->country;
    }

    public function getCountryCode(): string
    {
        return $this->country_code;
    }

    public function getRegion(): string
    {
        return $this->region;
    }

    public function getCity(): string
    {
        return $this->city;
    }

    public function getLat(): string
    {
        return $this->lat;
    }

    public function getLong(): string
    {
        return $this->long;
    }

    public function getCurrency(): string
    {
        return $this->currency;
    }

    public function getIsp(): string
    {
        return $this->isp;
    }

    public function getOrg(): string
    {
        return $this->org;
    }

    public function getCookieUid(): int
    {
        return $this->cookie_uid;
    }

    public function getStorageUid(): int
    {
        return $this->storage_uid;
    }

    public function getDomain(): string
    {
        return $this->domain;
    }

    public function getFingerprint(): ?string
    {
        return $this->fingerprint;
    }

    public function setFingerprint(?string $fingerprint): void
    {
        $this->fingerprint = $fingerprint;
    }

}
