<?php

namespace App\SMS\Services;

use App\Models\SmsServiceConfig;
use App\SMS\SmsInfo;
use App\SMS\SmsService;
use Illuminate\Support\Facades\Log;

class Atomic implements SmsService
{
    private $_sender;
    private $_publicKey;
    private $_privateKey;

    const GATEWAY = 'https://api.atompark.com/api/sms/3.0/';

    public function __construct()
    {
        $conf = SmsServiceConfig::getConfig($this->getId());
        $this->_sender = $conf['sender']?? config('sms.atomic.sender');
        $this->_publicKey = $conf['publicKey']?? config('sms.atomic.publicKey');
        $this->_privateKey = $conf['privateKey']?? config('sms.atomic.privateKey');
    }

    public function getId(): string
    {
        return 'atomic';
    }

    public function getName(): string
    {
        return 'Atomic';
    }

    public function getBalance(): float
    {
        try {
            $data = $this->sendRequest(['action' => 'getUserBalance', 'currency' => 'UAH']);
            if ($data !== null && is_array($data) && array_key_exists('balance_currency', $data)) {
                return floatval($data['balance_currency']);
            }
        } catch (\Exception $e) {
            Log::error($e->getMessage());
        }
        return -1;
    }

    public function sendSMS(string $phone, string $message): SmsInfo
    {
        $info = new SmsInfo();
        $message = [
            'action' => 'sendSMS',
            'sender' => $this->_sender,
            'text' => $message,
            'phone' => $phone,
            'datetime' => '',
            'sms_lifetime' => 0,
        ];
        Log::info('atomic send data: '.var_export($message, true));
        $data = $this->sendRequest($message);
        Log::info('atomic send response: '.var_export($data, true));
        if ($data !== null && is_array($data) && array_key_exists('price', $data)) {
            $info->cost = $data['price'];
            $info->success = true;
            $info->count = 1;
        } else {
            Log::error('Atomic error on sending SMS: '. var_export($data, true));
        }
        return $info;
    }

    private function sendRequest(array $params)
    {
        $params ['version'] ="3.0";
        $params ['key'] = $this->_publicKey;
        ksort ($params);
        $sum = '';
        foreach ($params as $k => $v) $sum .= $v;
        $sum .= $this->_privateKey;
        $params['sum'] = md5($sum);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_URL, self::GATEWAY.$params['action']);
        $result = curl_exec($ch);
        if (curl_errno($ch) > 0) {
            Log::error('Error on sending request to Atomic (CURL).');
            return null;
        }

        $data = json_decode($result, true);

        if (array_key_exists('result', $data)) {
            return $data['result'];
        } else {
            Log::error('Atomic error on sending request: ' . $result);
        }

        return null;
    }
}
