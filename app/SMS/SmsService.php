<?php

namespace App\SMS;

interface SmsService
{
    public function getId(): string;
    public function getName(): string;
    public function getBalance(): float;
    public function sendSMS(string $phone, string $message): SmsInfo;
}
