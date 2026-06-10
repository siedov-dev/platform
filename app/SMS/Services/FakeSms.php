<?php

namespace App\SMS\Services;

use App\SMS\SmsInfo;
use App\SMS\SmsService;
use Illuminate\Support\Facades\Log;
use function Psy\debug;

class FakeSms implements SmsService
{

    public function getId(): string
    {
        return 'FakeSms';
    }

    public function getName(): string
    {
        return 'FakeSms';
    }

    public function getBalance(): float
    {
        return 1000;
    }

    public function sendSMS(string $phone, string $message): SmsInfo
    {
        Log::debug('Fake sms sending');
        Log::debug(__CLASS__. '@'. __METHOD__. 'phone:'. $phone);
        Log::debug(__CLASS__. '@'. __METHOD__. 'message:'. $message);

        $info = new SmsInfo();
        $info->count = 1;
        $info->cost = 1;
        $info->success = true;

        return $info;
    }
}
