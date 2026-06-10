<?php

namespace App\SMS\Services;

use App\Models\SmsServiceConfig;
use App\SMS\SmsInfo;
use App\SMS\SmsService;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class P1Sms implements SmsService
{
    private string $_sender = "";
    private string $_key = "";

    const GATEWAY = 'https://admin.p1sms.ru';

    public function __construct()
    {
        $conf = SmsServiceConfig::getConfig($this->getId());
//        $this->_sender = $conf['sender']?? config('sms.p1sms.sender');
        $this->_key = $conf['key'] ?? config('sms.p1sms.key');
    }

    public function getId(): string
    {
        return 'p1sms';
    }

    public function getName(): string
    {
        return 'P1Sms';
    }

    public function getBalance(): float
    {
        try {
            $data = Http::get(self::GATEWAY . '/apiUsers/getUserBalanceInfo', ['apiKey' => $this->_key]);
            return $data->json()['data'];
        } catch (\Exception $e) {
            Log::error($e->getMessage());
        }
        return -1;
    }

    public function sendSMS(string $phone, string $message): SmsInfo
    {
        $message  = "Ваш код $message";
        $info = new SmsInfo();
        $m = '/apiSms/create';
        $sender = 'GM_KK';
        if (preg_match('/^(\+)?7.*/', $phone)) {
            $sender = 'VIRTA';
        }
        if (preg_match('/^(\+)?77.*/', $phone)) {
            $sender = 'GM_KK';
        }
        $message = [
            'sms' => [
                [
                    'channel' => 'char',
                    'sender' => $sender,
                    'text' => $message,
                    'phone' => $phone,
                    'plannedAt' => time(),
                    "cascade" => [
                        "schemeDetail" => [
                            [
                                "needStatus" => "agg_error",
                                "channel" => "char",
                                "sender" => "GM_MP_O",
                                "smstemplate" => [
                                    "texts" => [$message],
                                ],
                            ]
                        ],
                    ],
                ],
            ]
        ];
        Log::info('p1sms send data: ' . var_export($message, true));
        Log::info('p1sms send data: ',  $message);
        $data = $this->sendRequest($m, $message);
        Log::info('p1sms send response: ' . var_export($data, true));
        if ($data !== null && is_array($data) && $data['status'] !== 'error' && $data['data'][0]['status'] === 'sent') {
            $info->cost = 0;
            $info->success = true;
            $info->count = 1;
        } else {
            Log::error('p1sms error on sending SMS: ' . var_export($data, true));
        }
        return $info;
    }

    private function sendRequest(string $method, array $params)
    {
        $params['apiKey'] = $this->_key;

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($params));
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_URL, self::GATEWAY . $method);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
        $result = curl_exec($ch);
        if (curl_errno($ch) > 0) {
            Log::error('Error on sending request to p1sms (CURL).');
            return null;
        }

        $data = json_decode($result, true);
        return $data;
    }
}
