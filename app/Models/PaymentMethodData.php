<?php

namespace App\Models;

use App\Services\KPay\KPay;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Данные метода платежного метода (ключи, ид...)
 * @property int $id
 * @property int $payment_method_id
 * @property string $key
 * @property string $value
 */
class PaymentMethodData
{
//    use HasFactory;

//    protected $guarded = ['id'];

    public static function getMethodData(int $method_id): array
    {
        return KPay::getInstance()->getPayMethodData($method_id);
//        return PaymentMethodData::where('payment_method_id', $method_id)
//            ->get()->pluck('value', 'key')->toArray()?? [];
    }

    public static function getMethodValue(int $method_id, string $key): ?string
    {
        return KPay::getInstance()->getPayMethodDataByKey($method_id, $key);
//        return PaymentMethodData::where('payment_method_id', $method_id)
//            ->where('key', $key)
//            ->first()->value?? null;
    }
}
