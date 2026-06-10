<?php

namespace App\Models;

use App\Services\KPay\KPay;
use Illuminate\Database\Eloquent\Model;

/**
 * Class PaySystemsConfig
 * Параметры платежных систем.
 *
 * @property int $id
 * @property int $system_id
 * @property string $param_name
 * @property string $param_key
 * @property string $param_value
 */
class PaySystemsConfig extends Model
{
    //
    /**
     * returns system config values
     * @param int $system_id
     * @return array
     */
    public static function getSystemConfig(int $system_id): array
    {
        return KPay::getInstance()->getPaySystemParams($system_id);
//        $data = PaySystemsConfig::where('system_id', $system_id)->get();
//        if ($data !== null) return $data->pluck('param_value', 'param_key')->toArray();
//        return [];
    }

    /**
     * @param int $system_id
     * @param string $key
     * @param string $value
     * @return bool
     */
    public static function setSystemConfig(int $system_id, string $key, string $value): bool
    {
        return KPay::getInstance()->paySystemSetParam($system_id, $key, $value);
//        /** @var PaySystemsConfig $data */
//        $data = PaySystemsConfig::where('system_id', $system_id)->where('param_key', $key)->first();
//        $data->param_value = $value;
//        return $data->save();
    }

//    public static function getSystemParams(int $system_id)
//    {
//        $data = PaySystemsConfig::where('system_id', $system_id)->get();
//        return $data;
//    }
}
