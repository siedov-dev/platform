<?php

namespace App\Services\KPay;

use JsonSerializable;

class PayInData implements JsonSerializable
{
    public float $amount = 0;
    public string $currency = "";
    public int $user_id = 0;
    public int $method_id = 0;
    public int $bonus_id = 0;
    public string $site_url = "";
    public string $success_url = "";
    public string $fail_url = "";
    public string $close_url = "";
    public ?UserStatsData $userStats = null;
    public ?array $extra_data = null;

    public function __construct()
    {
        $this->site_url = url('/');
        $this->success_url = route('pay.success', [], true);
        $this->fail_url = route('pay.fail', [], true);
        $this->close_url = route('pay.close-tab', [], true);
    }

    public function jsonSerialize(): array
    {
        return [
            'amount' => $this->amount,
            'currency' => $this->currency,
            'user_id' => $this->user_id,
            'pay_method_id' => $this->method_id,
            'bonus_id' => $this->bonus_id,
            'user_pay_data' => $this->userStats,
            'extra_data' => $this->extra_data,
            'site_url' => $this->site_url,
            'success_url' => $this->success_url,
            'fail_url' => $this->fail_url,
        ];
    }
}
