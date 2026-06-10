<?php


namespace App\PaymentsAPI\FreeKassa;


use Illuminate\Support\Facades\Log;

/**
 * Class FreeKassaApi
 * @deprecated
 * @package App\PaymentsAPI\FreeKassa
 * @see https://www.free-kassa.ru/docs/api.php
 */
class FreeKassaApi
{

    protected $merchant_id = '257369';
    protected $secret = "sicmt6r8";
    protected $api_url = "https://www.free-kassa.ru/api.php";

    protected $wallet_id = "F102915768";
    protected $wallet_key = "A9147BE3FD601C12D41E5FF2C08AE8C4";

    const SERVICE_FKWALLET_RUB = 133;
    const SERVICE_QIWI_RUB = 63;
    const SERVICE_UMON_RUB = 45;
    const SERVICE_VISA_RUB = 160;
    const SERVICE_EXMO_RUB = 180;
    const SERVICE_PAYER_RUB = 114;

    public function getPayUrl(string $user_id, string $order_id, float $amount, string $service)
    {
        $url = 'https://www.free-kassa.ru/merchant/cash.php';
        $data = [
            'm' => $this->merchant_id,
            'o' => $order_id,
            'oa' => $amount,
            'lang' => 'ru',
//            'i' => $service,
            'us_user' => $user_id,
            's' => $this->sign($amount, $order_id),
        ];
        return $url.'?'.http_build_query($data);
    }

    public function getServiceCode($method)
    {
        switch ($method){
            case 'FK.fkwrub':
                return self::SERVICE_FKWALLET_RUB;
            case 'FK.uqiwirub':
                return self::SERVICE_QIWI_RUB;
            case 'FK.umonrub':
                return self::SERVICE_UMON_RUB;
            case 'FK.visarub':
                return self::SERVICE_VISA_RUB;
            case 'FK.exmorub':
                return self::SERVICE_EXMO_RUB;
            case 'FK.payerrub':
                return self::SERVICE_PAYER_RUB;
        }
        return 'FK.unknown';
    }

    protected function sign(string $amount, string $order_id)
    {
        return md5($this->merchant_id.':'.$amount.':'.$this->secret.':'.$order_id);
    }

    public function checkSign(string $amount, string $order_id, string $sign)
    {
        return $sign === md5($this->merchant_id.':'.$amount.':'.$this->secret.':'.$order_id);
    }
}
