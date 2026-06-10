<?php

namespace App\SMS\Services;


use App\Models\SmsServiceConfig;
use App\SMS\SmsInfo;
use App\SMS\SmsService;
use Illuminate\Support\Facades\Log;

class SMSC implements SmsService
{
    private $_login;
    private $_password;
    private $_sender;

    /** @var resource */
    private $_streamContext;

    public function __construct()
    {
        $conf = SmsServiceConfig::getConfig($this->getId());

        $this->_login = $conf['login']?? config('sms.SMSC.login');
        $this->_password = $conf['password']?? config('sms.SMSC.password');
        $this->_sender = $conf['sender']?? config('sms.SMSC.sender');
    }

    public function getId(): string
    {
        return 'smsc';
    }

    public function getName(): string
    {
        return 'SMSC';
    }

    /**
     * @return float
     */
    public function getBalance(): float
    {
        try {
            $balance=0;
            $requestStr='https://smsc.ua/sys/balance.php'
                .'?login='.$this->_login
                .'&psw='.$this->_password
                .'&fmt=3';
            $resStr = file_get_contents($requestStr, false, $this->getStreamContext());
            $jsn=json_decode($resStr);

            if(isset($jsn->balance)){
                $balance=floatval($jsn->balance);
            } else {
                Log::error('SMSManager get balance error:'.$resStr);
            }

            return $balance;
        } catch (\Exception $e) {
            Log::error('SMSC service: '.$e->getMessage());
        }
        return 0;
    }


    /**
     * Sends SMS to the given phone.
     * @param $phone
     * @param $message
     * @return bool
     */
    public function sendSMS(string $phone, string $message): SmsInfo
    {
        $info = new SmsInfo();
        try {
            $requestStr = 'https://smsc.ua/sys/send.php'
                .'?login='.$this->_login
                .'&psw='.$this->_password
                .'&phones='.$phone
                .'&mes='.urlencode($message)
                .'&sender='.$this->_sender
                .'&cost=2'
                .'&fmt=0';
//            Log::info('SMSC send:'.$requestStr);

            $resStr = file_get_contents($requestStr, false, $this->getStreamContext());
            $res = explode(' ', $resStr);
            $success = $res[0] === 'OK';
            if ($success) {
                $info->success = true;

                $regex = '/OK - ([\d.]+) SMS, ID - [\d.]+, COST - ([\d.]+)/i';
                preg_match_all($regex, $resStr, $matches);

                if (count($matches) === 3) {
                    $info->count = count($matches[1]) > 0 ? intval($matches[1][0]) : 1;
                    $info->cost = count($matches[2]) > 0 ? intval($matches[2][0]) : 0;
                }

            } else {
                Log::error($resStr);
                Log::error('Message: '.$message);
                Log::error('Request: '.$requestStr);
                $info->success = false;
                $info->error = $resStr;
            }
        } catch (\Exception $e) {
            Log::error('SMSC service: '.$e->getMessage());
            $info->success = false;
            $info->error = $e->getMessage();
        }
        return $info;
    }

    private function getStreamContext()
    {
        if ($this->_streamContext === null) {
            $this->_streamContext = stream_context_create([
                "ssl" => [
                    "verify_peer" => false,
                    "verify_peer_name" => false,
                ],
            ]);
        }
        return $this->_streamContext;
    }
}
