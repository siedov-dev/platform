<?php

namespace App\SMS\Services;

use App\Models\SmsServiceConfig;
use App\SMS\Lib\Zadarma\ApiException;
use App\SMS\Lib\Zadarma\ZadarmaApi;
use App\SMS\SmsInfo;
use App\SMS\SmsService;
use Illuminate\Support\Facades\Log;

class Zadarma implements SmsService
{
    private string $key = '19f01cef4bd4a40bd910';
    private string $secret = 'b845a3e31dd5f57c7f0a';
    private string $sender = '';

    private ZadarmaApi $api;

    const RETRIES = 10;
    const SLEEP_SEC = 3;

    public function __construct()
    {
        $conf = SmsServiceConfig::getConfig($this->getId());
        $this->api = new ZadarmaApi($conf['key'], $conf['secret'], false);
        $this->sender = $conf['sender']??'kraken';
    }

    public function getId(): string
    {
        return 'zadarma';
    }

    public function getName(): string
    {
        return 'Zadarma';
    }

    public function getBalance(): float
    {
        $try = 1;
        do {
            try {
                $exceeded = false;
                sleep(1);
                return $this->api->getBalance()->balance;
            } catch (ApiException $e) {
                Log::error($e->getCode());
                Log::error($e->getMessage());
                $try++;
                if ($e->getCode() === 429) {
                    $exceeded = true;
                    sleep(self::SLEEP_SEC);
                }
            }
        } while ($exceeded && $try < self::RETRIES);
        return -1;
    }

    public function sendSMS(string $phone, string $message): SmsInfo
    {
        $try = 1;
        do {
            try {
                $exceeded = false;
                sleep(1);
                Log::info('zadarma sms to '.$phone.' '. $message);
                $result = $this->api->sendSms($phone, 'Код активации: '.$message);
                return new SmsInfo(true, $result->messages, $result->cost);
            } catch (ApiException $e) {
                Log::error($e->getCode());
                Log::error($e->getMessage());
                $try++;
                if ($e->getCode() === 429) {
                    $exceeded = true;
                    sleep(self::SLEEP_SEC);
                }
            }
        } while ($exceeded && $try < self::RETRIES);

        return new SmsInfo(false,0, 0);
    }
}
