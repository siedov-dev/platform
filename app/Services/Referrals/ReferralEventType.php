<?php

namespace App\Services\Referrals;

enum ReferralEventType: int
{
    case Click = 0;
    case Register = 1;
    case MoneySpent = 2;
    case Deposit = 3;
    case FirstDeposit = 4;
    case AchievementCompleted = 5;
    case ReferralMoneyTransferred = 6;
    case AccumulationMoneyTransferred = 7;

    public function getName(): string
    {
        return __('referral.event.'.$this->name);
    }
}
