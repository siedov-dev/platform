<?php

namespace App\Models\Bonuses;

use App\MUser;

interface DepositBonusInterface
{
    public function getId(): ?int;
    public function getRequiredSum(): float;
    public function confirmPayment(MUser $user, float $amount, int $currency): float;
    public function isExpired(): bool;
    public function getCurrencyName();
}
