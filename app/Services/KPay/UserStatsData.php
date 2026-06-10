<?php

namespace App\Services\KPay;

use App\Helpers\Device;
use App\MUser;
use JsonSerializable;

class UserStatsData implements JsonSerializable
{
    public int $user_id = 0;
    public string $browser = "";
    public string $platform = "";
    public float $total_bet = 0;
    public float $total_win = 0;
    public int $partner_id = 0;
    public int $reg_days = 0;
    public string $ip = "";
    public string $locale = "";
    public bool $isVerified = false;
    public string $fingerprint  = "";
    public string $user_agent  = "";
    public string $login  = "";
    public string $email  = "";
    public string $phone  = "";


    public function jsonSerialize(): array
    {
        return [
            'user_id' => $this->user_id,
            'browser' => $this->browser,
            'platform' => $this->platform,
            'total_bet' => $this->total_bet,
            'total_win' => $this->total_win,
            'partner_id' => $this->partner_id,
            'reg_days' => $this->reg_days,
            'ip' => $this->ip,
            'locale' => $this->locale,
            'is_verified' => $this->isVerified,
            'fingerprint' => $this->fingerprint,
            'user_agent' => $this->user_agent,
            'login' => $this->login,
            'email' => $this->email,
            'phone' => $this->phone,
        ];
    }

    public static function getUserData(int $user_id): UserStatsData
    {
        /** @var MUser $user */
        $user = MUser::find($user_id);
        if ($user === null) throw new \Exception("No user found");
        $data = new UserStatsData();
        $data->user_id = $user->getId();
        $data->reg_days = $user->created_at->diffInDays(now());
        $data->locale = $user->getLocale();
        $data->ip = $user->last_ip?? '';
        $data->total_bet = $user->getWallet()->getTotalBet();
        $data->total_win = $user->getWallet()->getTotalWin();
        $data->partner_id = $user->click_id?? 0;
        $data->isVerified = $user->isVerified();
        $data->fingerprint = $user->getFingerprint();
        $data->user_agent = request()->userAgent();
        $data->login = $user->login?? '';
        $data->email = $user->email?? '';
        $data->phone = $user->phone?? '';
        $device = Device::current();
        if ($device !== null) {
            $data->browser = $device->browser();
            $data->platform = $device->platform();
        }
        return $data;
    }
}
