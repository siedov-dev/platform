<?php

namespace App;

use App\Helpers\Hlog;
use App\Models\SmsCurrencySystem;
use App\Models\SmsLog;
use App\Models\SmsServiceConfig;
use App\SMS\Services\ACSolutions;
use App\SMS\Services\Atomic;
use App\SMS\Services\BSG;
use App\SMS\Services\FakeSms;
use App\SMS\Services\P1Sms;
use App\SMS\Services\SMSC;
use App\SMS\Services\UnisenderSms;
use App\SMS\Services\Zadarma;
use App\SMS\SmsInfo;
use App\SMS\SmsService;
use App\SMS\SmsServiceType;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SMSManager
{
    private static bool $_savingHandlerAdded = false;
    private static int $_smsSentQuantity = 0;
    private static int $_smsSentCost = 0;
    private static string $_message = '';

    private static ?SmsService $_service = null;

    const TIME_LIMIT  = 4;
    const SMS_LIMIT = 1;

    public static function getSmsService(?SmsServiceType $service = null) : SmsService
    {
        if ($service === null) $service = SmsServiceType::from(SmsServiceConfig::getDefaultId());

        if (self::$_service === null || self::$_service->getId() !== $service->value) {
            self::$_service = match ($service) {
                SmsServiceType::ACSolutions => new ACSolutions(),
                SmsServiceType::Atomic => new Atomic(),
                SmsServiceType::BSG => new BSG(),
                SmsServiceType::SMSC => new SMSC(),
                SmsServiceType::UnisenderSms => new UnisenderSms(),
                SmsServiceType::Zadarma => new Zadarma(),
                SmsServiceType::FakeSms => new FakeSms(),
                SmsServiceType::P1sms => new P1Sms(),
                default => throw new \Exception("Unknown SMS service"),
            };
        }
        return self::$_service;
    }

    /**
     * @return float
     */
    public static function getBalance(?SmsServiceType $service = null): float
    {
        try {
            return self::getSmsService($service)->getBalance();
        } catch (\Exception $e) {
            Hlog::logException($e);
        }
        return 0;
    }

    /**
     * Sends SMS to the given phone.
     * @param $phone
     * @param $msg
     * @param ?SmsServiceType $service sms sevice id (null - default)
     * @param bool $force don't check sms limit
     * @return SmsInfo
     */
    public static function sendSMS($phone, $msg, ?SmsServiceType $service = null, bool $force = false, ?string $currency = null): SmsInfo
    {
        //sms limit
        if (!$force && SmsLog::where('phone', $phone)
            ->where('created_at', '>=', now()->subMinutes(self::TIME_LIMIT))
            ->count() >= self::SMS_LIMIT) {
            $last_sent_at = SmsLog::where('phone', $phone)->max('created_at');
            $time_left = Carbon::createFromFormat('Y-m-d H:i:s', $last_sent_at)->addMinutes(self::TIME_LIMIT)->diffInMinutes(now());
            $info = new SmsInfo();
            $info->count = 1;
            $info->cost = 1;
            $info->success = false;
            $info->error = __('sms.limit_try_again', ['time' => $time_left]);

            Log::error('sms limit sending sms to '.$phone.' time left '.$time_left);
            return $info;
        }

        if ($currency !== null) {
            $service_id = SmsCurrencySystem::getForCurrency($currency);
            if ($service_id !== null) $service = $service_id;
        }

        $service = self::getSmsService($service);
        $info = $service->sendSMS($phone, $msg);
        if ($info->count > 0) {
            self::logSms($phone, $msg, $service->getId());
            self::logSending($msg, $phone, $info);
        } else {
            Log::error('Error on sending SMS to '. $phone . ' via ' . $service->getId());
        }
        return $info;
    }

    private static function logSms(string $phone, string $text = '', string $service = '')
    {
        try{
            $log = new SmsLog(compact('phone', 'text', 'service'));
            $log->save();
        } catch (\Exception $e) {
            Log::error($e->getMessage());
        }
    }

    private static function logSending($msg, $phone, SmsInfo $info)
    {
        if (!self::$_savingHandlerAdded) {
            register_shutdown_function(function() {
                DB::table('all_sms_history')->insert([
                    'how_many_sms' => self::$_smsSentQuantity,
                    'price_of_sms' => self::$_smsSentCost,
                    'hall_id' => 237,
                    'text_sms' => self::$_message,
                ]);
            });
            self::$_savingHandlerAdded = true;
        }

        self::$_message = $msg;
        self::$_smsSentCost += $info->cost;
        self::$_smsSentQuantity += $info->count;
    }
}
