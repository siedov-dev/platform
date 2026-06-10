<?php

namespace App\Helpers;

use GeoIp2\Database\Reader;
use Illuminate\Support\Facades\Log;

/**
 * @deprecated
 */
class GeoIP
{
    private static $_instance;

    private $_city;
    private $_country;

    private function __construct()
    {
        try {
//            Log::info(resource_path('geo-ip/GeoLite2-City.mmdb'));
//            $reader = new Reader(resource_path('geo-ip/GeoLite2-City.mmdb'));
//            $this->_city = $reader->city($_SERVER['REMOTE_ADDR'])->city;
//            $reader = new Reader(resource_path('geo-ip/GeoLite2-Country.mmdb'));
//            $this->_country = $reader->country($_SERVER['REMOTE_ADDR'])->country;
        } catch (\Exception $e) {
            Log::error($e->getMessage());
        }
    }


    public function getCityName() : string
    {
        // City can ben't be detected for some reasons (such as 127.0.0.1 or etc.) so use some default value.
        if ($this->_city === null) return 'Kiev';
        if ($this->_city->name === null) return 'Kiev';
        return $this->_city->name;
    }


    public function getCountryName() : string
    {
        if ($this->_country === null) return 'Ukraine';
        if ($this->_country->name === null) return 'Ukraine';
        return $this->_country->name;
    }


    public function getCountryCode() : string
    {
        if ($this->_country === null) return 'UA';
        if ($this->_country->name === null) return 'UA';
        return strtoupper($this->_country->isoCode);
    }


    public static function instance() : GeoIP
    {
        if (self::$_instance === null) {
            self::$_instance = new GeoIP();
        }
        return self::$_instance;
    }
}
