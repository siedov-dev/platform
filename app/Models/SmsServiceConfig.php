<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Настройки СМС сервисов
 *
 * @property int $id
 * @property string $service_id
 * @property string $name
 * @property string $config
 * @property bool $is_default
 */
class SmsServiceConfig extends Model
{
    //
    public static function getConfig(string $service_id): array
    {
        /** @var SmsServiceConfig $conf */
        $conf = SmsServiceConfig::where('service_id', $service_id)->first();
        if ($conf) {
            return json_decode($conf->config, true)?? [];
        }
        return [];
    }

    public static function getDefaultId(): string
    {
        /** @var SmsServiceConfig $default */
        $default = SmsServiceConfig::where('is_default', true)->first();
        return $default? $default->service_id : "";
    }
}
