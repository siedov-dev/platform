<?php

namespace App\SMS\Services;

use App\SMS\SmsInfo;
use App\SMS\SmsService;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ACSolutions implements SmsService
{
    private string $url = "https://back.crm.acsolutions.ai";

    private string $_key_ru = "hGcPK6KUguZZUH8SivFMG1ZijG1F8kkkYnxcOXj0GW.GxRDIHv.VGGaJELOtOI7p";
    private string $_key_kz = "Dha7Woe7wXgyP7nC5cU6fBLDroZ63akKnS3hjZVE03y4GP1d04VEC.BCSiD3cZeX";
    private string $_key_ua = "llHUsp.kuasaEd.FTAer9MQaRlrq1Kk6bh0k.t8gDOwfRcyVIgF.HsRET5SQttni";

    public function getId(): string
    {
        return "acsolutions";
    }

    public function getName(): string
    {
        return "ACSolutions";
    }

    public function getBalance(): float
    {
        return 999;
    }

    public function sendSMS(string $phone, string $message): SmsInfo
    {
        $key = $this->getKey($phone);
        Log::channel('mail')->info(__CLASS__." key:".var_export($key, true));

        if (!preg_match('#^(\+)?(\d{8,})$#', $phone, $match)) {
            $info = new SmsInfo(false, 0, 0);
            $info->error = "wrong phone number";
            return $info;
        }
        $phone_n = $match[2];
        $req_data =  [
            [
                'phone' => intval($phone_n),
//                'import_id' => $phone,
                'code' => $message,
            ]
        ];

        Log::channel('mail')->info(__CLASS__." add_orders req:".var_export(json_encode($req_data), true));

        $response = Http::acceptJson()->post($this->url."/api/v2/bpm/public/bp/{$key}/add_orders", $req_data);
        $resp_data = $response->json();
        Log::channel('mail')->info(__CLASS__." add_orders resp:".var_export($resp_data, true));
        if (!empty($resp_data['message'])) {
            $info = new SmsInfo(false, 0, 0);
            $info->error = $resp_data['message'];
            return $info;
        }

        if (empty($resp_data['data']?? [])) {
//            $response = Http::acceptJson()->post($this->url."/api/v2/orders/public/{$key}/trash_order", [
//                'import_ids' => [
//                    $phone,
//                ],
//            ]);
//            $resp_data = $response->json();
//            Log::channel('mail')->info(__CLASS__." trash_order resp:".var_export($resp_data, true));
//
//            if (!empty($resp_data['message'])) {
//                $info = new SmsInfo(false, 0, 0);
//                $info->error = $resp_data['message'];
//                return $info;
//            }

            $info = new SmsInfo(false, 0, 0);
            $info->error = __('sms.limit_try_again', ['time' => 4]);
            return $info;
        }

        if (!empty($resp_data['message'])) {
            $info = new SmsInfo(false, 0, 0);
            $info->error = $resp_data['message'];
            return $info;
        }
        $info = new SmsInfo(true, 1, 1);
        return $info;
    }

    private function getKey(string $phone): string
    {
        if (preg_match('#^(\+)?380\d{7,}$#', $phone)) return $this->_key_ua;
        if (preg_match('#^(\+)?77\d{7,}$#', $phone)) return $this->_key_kz;
        return $this->_key_ru;
    }
}
