<?php

namespace App\SMS;

class SmsInfo
{
    public bool $success = false;
    public int $count = 1;
    public float $cost = 0;
    public string $error = '';

    public function __construct(bool $success = false, int $count = 0, float $cost = 0)
    {
        $this->success = $success;
        $this->count = $count;
        $this->cost = $cost;
    }
}
