<?php

namespace App\SMS\Services;

use App\Mail\Unisender\UnisenderApi;
use App\Models\SmsServiceConfig;
use App\SMS\SmsInfo;
use App\SMS\SmsService;

class UnisenderSms  implements SmsService
{
    private UnisenderApi $api;
    private string $sender;

    public function __construct()
    {
        $conf = SmsServiceConfig::getConfig($this->getId());
        $this->api = new UnisenderApi($conf['key']);
        $this->sender = $conf['sender']??'kraken';
    }

    public function getId(): string
    {
        return "unisender";
    }

    public function getName(): string
    {
        return "unisender";
    }

    public function getBalance(): float
    {
        return 999;
    }

    public function sendSMS(string $phone, string $message): SmsInfo
    {
        $result = $this->api->sendSMS(['phone' => $phone, 'text' => $message, 'sender' => $this->sender]);
        return new SmsInfo(true, 1, 0);
    }
}
