<?php

namespace App\Helpers;

use App\GamesProviders\DeviceType;
use Illuminate\Support\Facades\Log;


/**
 * get client browser info based on user agent in browscap
 * @docs https://browscap.org
 * @docs https://www.php.net/manual/en/function.get-browser.php
 */
class Device
{
    const DESKTOP = 'desktop';
    const TABLET = 'tablet';
    const PHONE = 'phone';

    private static $_device;

    private static ?Device $_instance = null;

    private array $browser_cap = [];

    public function __construct(?string $user_agent = null)
    {
        try {
            $this->browser_cap = get_browser($user_agent, true);
        } catch (\Throwable $e) {
            Hlog::logException($e);
        }
    }

    public static function current(): Device
    {
        if (self::$_instance === null) {
            self::$_instance = new Device();
        }
        return self::$_instance;
    }

    public static function detect(): string
    {
        if (self::$_device !== null) return self::$_device;

        if (!isset($_SERVER['HTTP_USER_AGENT']) || !isset($_SERVER['HTTP_ACCEPT'])) return self::DESKTOP;

        $tablet_browser = 0;
        $mobile_browser = 0;

        if (preg_match('/(tablet|ipad|playbook)|(android(?!.*(mobi|opera mini)))/i', strtolower($_SERVER['HTTP_USER_AGENT']))) {
            $tablet_browser++;
        }

        if (preg_match('/(up.browser|up.link|mmp|symbian|smartphone|midp|wap|phone|android|iemobile)/i', strtolower($_SERVER['HTTP_USER_AGENT']))) {
            $mobile_browser++;
        }

        if ((strpos(strtolower($_SERVER['HTTP_ACCEPT']), 'application/vnd.wap.xhtml+xml') > 0) or ((isset($_SERVER['HTTP_X_WAP_PROFILE']) or isset($_SERVER['HTTP_PROFILE'])))) {
            $mobile_browser++;
        }

        $mobile_ua = strtolower(substr($_SERVER['HTTP_USER_AGENT'], 0, 4));
        $mobile_agents = array(
            'w3c ', 'acs-', 'alav', 'alca', 'amoi', 'audi', 'avan', 'benq', 'bird', 'blac',
            'blaz', 'brew', 'cell', 'cldc', 'cmd-', 'dang', 'doco', 'eric', 'hipt', 'inno',
            'ipaq', 'java', 'jigs', 'kddi', 'keji', 'leno', 'lg-c', 'lg-d', 'lg-g', 'lge-',
            'maui', 'maxo', 'midp', 'mits', 'mmef', 'mobi', 'mot-', 'moto', 'mwbp', 'nec-',
            'newt', 'noki', 'palm', 'pana', 'pant', 'phil', 'play', 'port', 'prox',
            'qwap', 'sage', 'sams', 'sany', 'sch-', 'sec-', 'send', 'seri', 'sgh-', 'shar',
            'sie-', 'siem', 'smal', 'smar', 'sony', 'sph-', 'symb', 't-mo', 'teli', 'tim-',
            'tosh', 'tsm-', 'upg1', 'upsi', 'vk-v', 'voda', 'wap-', 'wapa', 'wapi', 'wapp',
            'wapr', 'webc', 'winw', 'winw', 'xda ', 'xda-');

        if (in_array($mobile_ua, $mobile_agents)) {
            $mobile_browser++;
        }

        if (strpos(strtolower($_SERVER['HTTP_USER_AGENT']), 'opera mini') > 0) {
            $mobile_browser++;
            //Check for tablets on opera mini alternative headers
            $stock_ua = strtolower(isset($_SERVER['HTTP_X_OPERAMINI_PHONE_UA']) ? $_SERVER['HTTP_X_OPERAMINI_PHONE_UA'] : (isset($_SERVER['HTTP_DEVICE_STOCK_UA']) ? $_SERVER['HTTP_DEVICE_STOCK_UA'] : ''));
            if (preg_match('/(tablet|ipad|playbook)|(android(?!.*mobile))/i', $stock_ua)) {
                $tablet_browser++;
            }
        }

        if ($tablet_browser > 0) {
            self::$_device = self::DESKTOP;
            return self::DESKTOP;
        } else if ($mobile_browser > 0) {
            self::$_device = self::PHONE;
            return self::PHONE;
        } else {
            self::$_device = self::DESKTOP;
            return self::DESKTOP;
        }
    }

    public static function setDevice(string $device): void
    {
        self::$_device = $device;
    }

    public static function reset(): void
    {
        self::$_device = null;
    }

    public static function getDeviceType(): DeviceType
    {
        return self::detect() === self::PHONE ? DeviceType::Phone : DeviceType::Desktop;
    }

    public function getParent(): string
    {
        return $this->browser_cap['parent']?? '';
    }

    public function browserBits(): int
    {
        return intval($this->browser_cap['browser_bits']?? '32');
    }

    public function browser(): string
    {
        return $this->browser_cap['browser']?? '';
    }

    public function browserVersion(): string
    {
        return $this->browser_cap['version']?? '';
    }

    public function platform(): string
    {
        return $this->browser_cap['platform']?? '';
    }

    public function platformDescription(): string
    {
        return $this->browser_cap['platform_description']?? '';
    }
    public function platformBits(): int
    {
        return intval($this->browser_cap['platform_bits']?? '32');
    }

    public function deviceType(): string
    {
        return $this->browser_cap['device_type']?? 'Desktop';
    }

    public function deviceName(): string
    {
        return $this->browser_cap['device_name']?? 'Desktop';
    }

    public function rawBrowscap(): array
    {
        return $this->browser_cap;
    }

    public function isTablet(): bool
    {
        return ($this->browser_cap['istablet']?? '') === '1';
    }

    public function isMobile(): bool
    {
        return ($this->browser_cap['ismobiledevice']?? '') === '1';
    }

}
