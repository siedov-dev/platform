<?php

namespace App\SMS\Services;

use App\Models\SmsServiceConfig;
use App\SMS\Lib\BSG\BsgApi;
use App\SMS\SmsInfo;
use App\SMS\SmsService;

class BSG implements SmsService
{
    private $_key;
    private $_sender;

    public function __construct()
    {
        $conf = SmsServiceConfig::getConfig($this->getId());
        $this->_key = $conf['key']?? config('sms.BSG.key');
        $this->_sender = $conf['sender']?? config('sms.BSG.sender');
    }

    public function getId(): string
    {
        return 'bsg';
    }

    public function getName(): string
    {
        return 'BSG';
    }

    public function getBalance(): float
    {
        $api = new BsgApi($this->_key);
        $client = $api->getSmsClient();
        $res = $client->getBalance();
        return is_array($res) && isset($res['amount']) ? $res['amount'] : 0;
    }

    public function sendSMS(string $phone, string $message): SmsInfo
    {
        $api = new BsgApi($this->_key, $this->_sender);
        $client = $api->getSmsClient();

        $res = $client->sendSms($phone, $message, 'successSend'.time());

        $info = new SmsInfo();
        if (isset($res['result']['price'])) {
            $info->cost = floatval($res['result']['price']);
        } else {
            $info->cost = 0.5;
        }
        return $info;
    }
}
